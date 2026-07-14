<?php
/**
 * GoogleAuthController — handles Google OAuth 2.0 login for customers
 */
class GoogleAuthController
{
    private PDO $pdo;

    private const GOOGLE_AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USER_URL  = 'https://www.googleapis.com/oauth2/v3/userinfo';
    private const PENDING_TTL      = 900; // 15 minutes to finish account setup

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ----------------------------------------------------------------
     * Step 1: Redirect user to Google's consent screen
     * -------------------------------------------------------------- */
    public function redirect(): void
    {
        if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
            $this->failAndReturn('Google login is not configured yet. Please contact the administrator.');
        }

        // Signed state is CSRF-safe without relying on PHP session surviving the Google round-trip.
        $state = $this->createOAuthState();
        unset($_SESSION['google_pending']);

        // Remember where to return after login, but only for same-origin URLs.
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $appBase = rtrim(APP_URL, '/');
        $back = ($referer && strpos($referer, $appBase) === 0)
            ? $referer
            : APP_URL . '/index.php?url=products';
        $_SESSION['google_redirect_back'] = $back;
        $this->setOAuthCookie('google_oauth_back', $back, 600);

        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
            'state'         => $state,
            'prompt'        => 'select_account',
        ]);

        // Flush session before leaving the site so Hostinger can persist it reliably.
        session_write_close();

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Location: ' . self::GOOGLE_AUTH_URL . '?' . $params);
        exit;
    }

    /* ----------------------------------------------------------------
     * Step 2: Handle callback from Google
     * -------------------------------------------------------------- */
    public function handleCallback(): void
    {
        // CSRF / state check (HMAC-signed — does not depend on session continuity)
        if (!$this->verifyOAuthState($_GET['state'] ?? '')) {
            $this->failAndReturn('Invalid OAuth state. Please try again.');
        }

        if (isset($_GET['error'])) {
            $this->failAndReturn('Google sign-in was cancelled. Try again when you\'re ready.');
        }

        if (empty($_GET['code'])) {
            $this->failAndReturn('No authorisation code received from Google.');
        }

        // Exchange code for access token
        $tokenData = $this->fetchToken($_GET['code']);
        if (empty($tokenData['access_token'])) {
            $this->failAndReturn('Failed to obtain access token from Google.');
        }

        // Fetch user info from Google
        $googleUser = $this->fetchUserInfo($tokenData['access_token']);
        if (empty($googleUser['email'])) {
            $this->failAndReturn('Could not retrieve your Google account information.');
        }

        try {
            $this->loginOrRegister($googleUser);
        } catch (Throwable $e) {
            error_log('Google OAuth login failed: ' . $e->getMessage());
            $this->failAndReturn('Google login failed due to a server error. Please try again or use email login.');
        }
    }

    /**
     * Brief branded bridge page after a successful Google login.
     */
    public function showSigningIn(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['google_signing_in'])) {
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        $signingIn = $_SESSION['google_signing_in'];
        unset($_SESSION['google_signing_in']);

        $pageTitle = 'Signing you in';
        require VIEWS_PATH . '/auth/google-signing-in.php';
    }

    /**
     * Step 3a (new users): show Complete your account form.
     */
    public function showCompleteAccount(): void
    {
        AuthMiddleware::guest();

        $pending = $this->getValidPending();
        if (!$pending) {
            $this->failAndReturn('Your Google sign-up session expired. Please continue with Google again.');
        }

        $pageTitle = 'Complete your account';
        $pendingGoogle = $pending;
        require VIEWS_PATH . '/auth/complete-google-account.php';
    }

    /**
     * Step 3b (new users): create account from completed profile.
     */
    public function completeAccount(): void
    {
        AuthMiddleware::guest();
        AuthMiddleware::csrf();

        $pending = $this->getValidPending();
        if (!$pending) {
            $this->failAndReturn('Your Google sign-up session expired. Please continue with Google again.');
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $phone     = preg_replace('/\s+/', '', trim($_POST['phone'] ?? ''));
        $birthdate = trim($_POST['birthdate'] ?? '');

        if ($firstName === '' || $lastName === '' || $username === '' || $birthdate === '') {
            flash('error', 'Please fill in all required fields.');
            header('Location: ' . APP_URL . '/index.php?url=complete-google-account');
            exit;
        }

        if (strlen($username) < 3 || strlen($username) > 30) {
            flash('error', 'Username must be between 3 and 30 characters.');
            header('Location: ' . APP_URL . '/index.php?url=complete-google-account');
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            flash('error', 'Username can only contain letters, numbers, underscores, dashes and dots.');
            header('Location: ' . APP_URL . '/index.php?url=complete-google-account');
            exit;
        }

        if ($this->usernameExists($username)) {
            flash('error', 'Username is already taken. Please choose another.');
            header('Location: ' . APP_URL . '/index.php?url=complete-google-account');
            exit;
        }

        if ($phone !== '' && !preg_match('/^\+639\d{9}$/', $phone)) {
            flash('error', 'Invalid phone number. Must be a valid Philippine mobile number (+639XXXXXXXXX).');
            header('Location: ' . APP_URL . '/index.php?url=complete-google-account');
            exit;
        }

        $normalizedBirth = $this->normalizeBirthdate($birthdate);
        if ($normalizedBirth === null) {
            flash('error', 'Please enter a valid birthdate.');
            header('Location: ' . APP_URL . '/index.php?url=complete-google-account');
            exit;
        }

        // Race: email may have been registered after Google callback
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$pending['email']]);
        if ($stmt->fetch()) {
            unset($_SESSION['google_pending']);
            $this->failAndReturn('An account with this email already exists. Please sign in instead.');
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO users
                (role_id, first_name, last_name, username, email, password, phone, birthdate,
                 google_id, profile_image, email_verified_at, is_active)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)
        ");
        $stmt->execute([
            ROLE_CUSTOMER,
            $firstName,
            $lastName,
            $username,
            $pending['email'],
            password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
            $phone !== '' ? $phone : null,
            $normalizedBirth,
            $pending['google_id'],
            $pending['avatar'],
        ]);

        $newId = (int) $this->pdo->lastInsertId();
        $stmt  = $this->pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$newId]);
        $user  = $stmt->fetch(PDO::FETCH_ASSOC);

        unset($_SESSION['google_pending']);

        if (!$user) {
            $this->failAndReturn('Account was created but sign-in failed. Please try signing in with Google.');
        }

        $this->establishSession($user, true);
    }

    /* ----------------------------------------------------------------
     * Find existing user, link email account, or start complete-profile
     * -------------------------------------------------------------- */
    private function loginOrRegister(array $g): void
    {
        $email     = strtolower(trim($g['email']));
        $googleId  = $g['sub']          ?? '';
        $firstName = $g['given_name']   ?? explode(' ', $g['name'] ?? '')[0] ?? 'User';
        $lastName  = $g['family_name']  ?? (explode(' ', $g['name'] ?? '', 2)[1] ?? '');
        $avatar    = $g['picture']      ?? null;

        // 1. Returning Google user
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE google_id = ? LIMIT 1');
        $stmt->execute([$googleId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Existing email account → link Google
        if (!$user) {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $upd = $this->pdo->prepare('UPDATE users SET google_id = ? WHERE id = ?');
                $upd->execute([$googleId, $user['id']]);

                if (empty($user['profile_image']) && $avatar) {
                    $upd2 = $this->pdo->prepare('UPDATE users SET profile_image = ? WHERE id = ?');
                    $upd2->execute([$avatar, $user['id']]);
                    $user['profile_image'] = $avatar;
                }

                // Mark email verified if Google verified it and ours isn't yet
                if (!empty($g['email_verified']) && empty($user['email_verified_at'])) {
                    $upd3 = $this->pdo->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = ?');
                    $upd3->execute([$user['id']]);
                }
            }
        }

        // 3. Brand-new → complete profile first (do not create yet)
        if (!$user) {
            if (empty($g['email_verified'])) {
                $this->failAndReturn('Your Google account email is not verified.');
            }

            $_SESSION['google_pending'] = [
                'google_id'           => $googleId,
                'email'               => $email,
                'first_name'          => $firstName,
                'last_name'           => $lastName,
                'avatar'              => $avatar,
                'suggested_username'  => $this->generateUsername($firstName, $lastName, $email),
                'expires'             => time() + self::PENDING_TTL,
            ];

            header('Location: ' . APP_URL . '/index.php?url=complete-google-account');
            exit;
        }

        // Block non-customer roles from using Google login
        if ((int) $user['role_id'] !== ROLE_CUSTOMER) {
            $this->failAndReturn('Admin accounts cannot sign in with Google.');
        }

        // Block inactive accounts
        if (isset($user['is_active']) && !(int) $user['is_active']) {
            $this->failAndReturn('Your account has been deactivated. Please contact support.');
        }

        $this->establishSession($user, false);
    }

    private function establishSession(array $user, bool $isNew): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['role_id']       = $user['role_id'];
        $_SESSION['email']         = $user['email'];
        $_SESSION['first_name']    = $user['first_name'];
        $_SESSION['last_name']     = $user['last_name'];
        $_SESSION['user_name']     = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['profile_image'] = $user['profile_image'] ?? null;

        $back = $_SESSION['google_redirect_back']
            ?? ($_COOKIE['google_oauth_back'] ?? null)
            ?? (APP_URL . '/index.php?url=products');
        unset($_SESSION['google_redirect_back']);
        $this->clearOAuthCookie('google_oauth_back');

        $appBase = rtrim(APP_URL, '/');
        if (!$back || strpos($back, $appBase) !== 0) {
            $back = APP_URL . '/index.php?url=products';
        }

        $welcome = $isNew
            ? ('Account created. Welcome to ' . APP_NAME . ', ' . $user['first_name'] . '!')
            : ('Welcome back, ' . $user['first_name'] . '!');

        $_SESSION['google_signing_in'] = [
            'first_name' => $user['first_name'],
            'avatar'     => $user['profile_image'] ?? null,
            'is_new'     => $isNew,
            'redirect'   => $back,
            'message'    => $welcome,
        ];

        header('Location: ' . APP_URL . '/index.php?url=google-signing-in');
        exit;
    }

    private function getValidPending(): ?array
    {
        $pending = $_SESSION['google_pending'] ?? null;
        if (!is_array($pending)) {
            return null;
        }

        if (empty($pending['email']) || empty($pending['google_id']) || empty($pending['expires'])) {
            unset($_SESSION['google_pending']);
            return null;
        }

        if ((int) $pending['expires'] < time()) {
            unset($_SESSION['google_pending']);
            return null;
        }

        return $pending;
    }

    private function normalizeBirthdate(string $birth): ?string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth)) {
            return null;
        }

        $d = DateTime::createFromFormat('Y-m-d', $birth);
        if (!($d && $d->format('Y-m-d') === $birth)) {
            return null;
        }

        $yyyy = (int) $d->format('Y');
        $currentYear = (int) date('Y');
        if ($yyyy < 1960 || $yyyy > $currentYear) {
            return null;
        }

        if ($d > new DateTime('today')) {
            return null;
        }

        return $d->format('Y-m-d');
    }

    private function failAndReturn(string $message): void
    {
        $_SESSION['flash_error'] = $message;
        header('Location: ' . APP_URL . '/index.php?login_modal=1');
        exit;
    }

    /* ----------------------------------------------------------------
     * Signed OAuth state (survives lost PHP sessions across Google redirects)
     * -------------------------------------------------------------- */
    private function createOAuthState(): string
    {
        $nonce = bin2hex(random_bytes(16));
        $exp   = (string) (time() + 600);
        $payload = $nonce . '.' . $exp;
        $sig = hash_hmac('sha256', $payload, GOOGLE_CLIENT_SECRET);
        return $payload . '.' . $sig;
    }

    private function verifyOAuthState(string $state): bool
    {
        $parts = explode('.', $state);
        if (count($parts) !== 3) {
            return false;
        }

        [$nonce, $exp, $sig] = $parts;
        if ($nonce === '' || !ctype_digit($exp) || $sig === '') {
            return false;
        }
        if ((int) $exp < time()) {
            return false;
        }

        $payload = $nonce . '.' . $exp;
        $expected = hash_hmac('sha256', $payload, GOOGLE_CLIENT_SECRET);
        return hash_equals($expected, $sig);
    }

    private function setOAuthCookie(string $name, string $value, int $ttl): void
    {
        $secure = strpos(APP_URL, 'https://') === 0;
        setcookie($name, $value, [
            'expires'  => time() + $ttl,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function clearOAuthCookie(string $name): void
    {
        $secure = strpos(APP_URL, 'https://') === 0;
        setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /* ----------------------------------------------------------------
     * Exchange authorisation code for access token
     * -------------------------------------------------------------- */
    private function fetchToken(string $code): array
    {
        $postData = http_build_query([
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]);

        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $postData,
            'timeout' => 15,
        ]]);

        $response = @file_get_contents(self::GOOGLE_TOKEN_URL, false, $ctx);
        return $response ? (json_decode($response, true) ?? []) : [];
    }

    /* ----------------------------------------------------------------
     * Fetch user profile from Google
     * -------------------------------------------------------------- */
    private function fetchUserInfo(string $accessToken): array
    {
        $ctx = stream_context_create(['http' => [
            'method'  => 'GET',
            'header'  => "Authorization: Bearer {$accessToken}\r\n",
            'timeout' => 15,
        ]]);

        $response = @file_get_contents(self::GOOGLE_USER_URL, false, $ctx);
        return $response ? (json_decode($response, true) ?? []) : [];
    }

    /* ----------------------------------------------------------------
     * Generate a unique username from name / email
     * -------------------------------------------------------------- */
    private function generateUsername(string $first, string $last, string $email): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $first . $last));
        if (strlen($base) < 3) {
            $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
        }
        $base = substr($base, 0, 20);

        $candidate = $base;
        $i = 1;
        while ($this->usernameExists($candidate)) {
            $candidate = $base . $i++;
        }
        return $candidate;
    }

    private function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return (bool) $stmt->fetch();
    }
}
