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
            $_SESSION['flash_error'] = 'Google login is not configured yet. Please contact the administrator.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        // Signed state is CSRF-safe without relying on PHP session surviving the Google round-trip.
        $state = $this->createOAuthState();

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
            $_SESSION['flash_error'] = 'Invalid OAuth state. Please try again.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        if (isset($_GET['error'])) {
            $_SESSION['flash_error'] = 'Google login was cancelled or denied.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        if (empty($_GET['code'])) {
            $_SESSION['flash_error'] = 'No authorisation code received from Google.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        // Exchange code for access token
        $tokenData = $this->fetchToken($_GET['code']);
        if (empty($tokenData['access_token'])) {
            $_SESSION['flash_error'] = 'Failed to obtain access token from Google.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        // Fetch user info from Google
        $googleUser = $this->fetchUserInfo($tokenData['access_token']);
        if (empty($googleUser['email'])) {
            $_SESSION['flash_error'] = 'Could not retrieve your Google account information.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        try {
            $this->loginOrRegister($googleUser);
        } catch (Throwable $e) {
            error_log('Google OAuth login failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Google login failed due to a server error. Please try again or use email login.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    }

    /* ----------------------------------------------------------------
     * Find existing user or create a new customer account
     * -------------------------------------------------------------- */
    private function loginOrRegister(array $g): void
    {
        $email     = strtolower(trim($g['email']));
        $googleId  = $g['sub']          ?? '';
        $firstName = $g['given_name']   ?? explode(' ', $g['name'] ?? '')[0] ?? 'User';
        $lastName  = $g['family_name']  ?? (explode(' ', $g['name'] ?? '', 2)[1] ?? '');
        $avatar    = $g['picture']      ?? null;

        // 1. Try to find user by google_id
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE google_id = ? LIMIT 1");
        $stmt->execute([$googleId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Try by email
        if (!$user) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Link google_id to existing account
                $upd = $this->pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                $upd->execute([$googleId, $user['id']]);

                // Update avatar if none
                if (empty($user['profile_image']) && $avatar) {
                    $upd2 = $this->pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $upd2->execute([$avatar, $user['id']]);
                    $user['profile_image'] = $avatar;
                }
            }
        }

        // 3. Create new customer account
        if (!$user) {
            // Block if email not verified on Google account
            if (empty($g['email_verified'])) {
                $_SESSION['flash_error'] = 'Your Google account email is not verified.';
                header('Location: ' . APP_URL . '/index.php');
                exit;
            }

            $username = $this->generateUsername($firstName, $lastName, $email);

            $stmt = $this->pdo->prepare("
                INSERT INTO users
                    (role_id, first_name, last_name, username, email, password, google_id, profile_image, email_verified_at, is_active)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)
            ");
            $stmt->execute([
                ROLE_CUSTOMER,
                $firstName,
                $lastName,
                $username,
                $email,
                password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT), // unusable random password
                $googleId,
                $avatar,
            ]);

            $newId = $this->pdo->lastInsertId();
            $stmt  = $this->pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$newId]);
            $user  = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Block non-customer roles from using Google login
        if ((int)$user['role_id'] !== ROLE_CUSTOMER) {
            $_SESSION['flash_error'] = 'Admin accounts cannot sign in with Google.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        // Block inactive accounts
        if (isset($user['is_active']) && !(int)$user['is_active']) {
            $_SESSION['flash_error'] = 'Your account has been deactivated. Please contact support.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        // Set session (same fields as AuthController)
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

        header('Location: ' . $back);
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
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return (bool)$stmt->fetch();
    }
}
