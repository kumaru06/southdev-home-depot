<?php
/**
 * SouthDev Home Depot – Auth Controller
 * Login, Register, Logout with CSRF and system logging
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../includes/Mailer.php';

class AuthController {
    private $userModel;
    private $logModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
        $this->logModel  = new Log($pdo);
    }

    public function showLogin() {
        $pageTitle = 'Login';
        require_once VIEWS_PATH . '/auth/login.php';
    }

    public function showRegister() {
        $pageTitle = 'Create Account';
        require_once VIEWS_PATH . '/auth/register.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?url=login');
            exit;
        }

        AuthMiddleware::csrf();

        // Detect AJAX / JSON requests so we can return JSON instead of redirects
        $isAjax = (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            if ($isAjax) {
                session_write_close();
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
                exit;
            }
            flash('error', 'Please fill in all fields.');
            header('Location: ' . APP_URL . '/index.php?url=login');
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_active']) {
                if ($isAjax) {
                    session_write_close();
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Contact support.']);
                    exit;
                }
                flash('error', 'Your account has been deactivated. Contact support.');
                header('Location: ' . APP_URL . '/index.php?url=login');
                exit;
            }

            if (empty($user['email_verified_at'])) {
                if ($isAjax) {
                    session_write_close();
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Please verify your email before logging in.', 'redirect' => APP_URL . '/index.php?url=verify-email']);
                    exit;
                }
                $_SESSION['pending_verify_email'] = $user['email'];
                flash('error', 'Please verify your email before logging in.');
                header('Location: ' . APP_URL . '/index.php?url=verify-email');
                exit;
            }

            $_SESSION['user_id']    = $user['id'];
            $_SESSION['role_id']    = $user['role_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];
            $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? null;

            $this->logModel->create(LOG_LOGIN, 'User logged in: ' . $user['email'], $user['id']);

            $redirect = ($user['role_id'] == ROLE_CUSTOMER) ? 'products' : 'dashboard';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'redirect' => APP_URL . '/index.php?url=' . $redirect]);
                exit;
            }
            header('Location: ' . APP_URL . '/index.php?url=' . $redirect);
            exit;
        }

        if ($isAjax) {
            session_write_close();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            exit;
        }

        flash('error', 'Invalid email or password.');
        header('Location: ' . APP_URL . '/index.php?url=login');
        exit;
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?url=register');
            exit;
        }

        AuthMiddleware::csrf();

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'password'   => $_POST['password'] ?? '',
            'phone'      => trim($_POST['phone'] ?? ''),
            'birthdate'  => trim($_POST['birthdate'] ?? ''),
            'role_id'    => ROLE_CUSTOMER
        ];

        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['password'])) {
            flash('error', 'Please fill in all required fields.');
            header('Location: ' . APP_URL . '/index.php?url=register');
            exit;
        }

        if (strlen($data['password']) < 8) {
            flash('error', 'Password must be at least 8 characters.');
            header('Location: ' . APP_URL . '/index.php?url=register');
            exit;
        }

        // Validate optional birthdate if provided. Accept mm/dd/yyyy or yyyy-mm-dd.
        if (!empty($data['birthdate'])) {
            $birth = $data['birthdate'];
            $currentYear = (int) date('Y');
            $normalized = null;

            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}$/', $birth)) {
                list($mm, $dd, $yyyy) = explode('/', $birth);
                $mm = (int) $mm; $dd = (int) $dd; $rawYear = trim($yyyy);
                $currentYear = (int) date('Y');

                // expand 2-digit years (00..currentTwo -> 2000s, else 1900s)
                if (strlen($rawYear) === 2) {
                    $yy = (int) $rawYear;
                    $currentTwo = $currentYear % 100;
                    if ($yy <= $currentTwo) $yyyy = 2000 + $yy;
                    else $yyyy = 1900 + $yy;
                } else {
                    $yyyy = (int) $rawYear;
                }

                if (!checkdate($mm, $dd, $yyyy)) {
                    flash('error', 'Invalid birthdate.');
                    header('Location: ' . APP_URL . '/index.php?url=register');
                    exit;
                }
                if ($yyyy < 1960 || $yyyy > $currentYear) {
                    flash('error', 'Birth year must be between 1960 and ' . $currentYear);
                    header('Location: ' . APP_URL . '/index.php?url=register');
                    exit;
                }
                $normalized = sprintf('%04d-%02d-%02d', $yyyy, $mm, $dd);
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth)) {
                $d = DateTime::createFromFormat('Y-m-d', $birth);
                if (!($d && $d->format('Y-m-d') === $birth)) {
                    flash('error', 'Invalid birthdate format.');
                    header('Location: ' . APP_URL . '/index.php?url=register');
                    exit;
                }
                $yyyy = (int) $d->format('Y');
                $currentYear = (int) date('Y');
                if ($yyyy < 1960 || $yyyy > $currentYear) {
                    flash('error', 'Birth year must be between 1960 and ' . $currentYear);
                    header('Location: ' . APP_URL . '/index.php?url=register');
                    exit;
                }
                $normalized = $d->format('Y-m-d');
            } else {
                flash('error', 'Invalid birthdate format. Use mm/dd/yyyy.');
                header('Location: ' . APP_URL . '/index.php?url=register');
                exit;
            }

            $data['birthdate'] = $normalized;
        } else {
            $data['birthdate'] = null;
        }

        if ($this->userModel->findByEmail($data['email'])) {
            flash('error', 'Email already registered.');
            header('Location: ' . APP_URL . '/index.php?url=register');
            exit;
        }

        $verificationToken = bin2hex(random_bytes(32));
        $otpCode = strval(random_int(100000, 999999));
        $otpExpiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));

        $data['verification_token'] = $verificationToken;
        $data['otp_code'] = $otpCode;
        $data['otp_expires_at'] = $otpExpiresAt;

        if ($this->userModel->create($data)) {
            $this->logModel->create(LOG_USER_CREATE, 'New customer registered: ' . $data['email']);

            // Send verification email
            $emailResult = $this->sendVerificationEmail($data['email'], $data['first_name'], $verificationToken, $otpCode);

            $_SESSION['pending_verify_email'] = $data['email'];
            if ($emailResult['sent']) {
                flash('success', 'Registration successful! Please verify your email.');
            } elseif ($this->isLocalEnvironment()) {
                flash('warning', 'Registration successful, but email sending failed. For local testing, use this OTP: ' . $otpCode);
            } else {
                flash('warning', 'Registration successful, but verification email could not be sent. Please configure SMTP and click Resend Verification Email.');
            }
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
        } else {
            flash('error', 'Registration failed. Please try again.');
            header('Location: ' . APP_URL . '/index.php?url=register');
        }
        exit;
    }

    public function showVerifyEmail() {
        $pageTitle = 'Verify Email';

        // Do not expose OTPs in the UI. Verification must be done via email.
        $devOtp = null;
        $devVerifyUrl = null;

        require_once VIEWS_PATH . '/auth/verify-email.php';
    }

    public function verifyEmailLink() {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            flash('error', 'Invalid verification link.');
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        $user = $this->userModel->getByVerificationToken($token);
        if (!$user) {
            flash('error', 'Verification link is invalid or expired.');
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        $this->userModel->markEmailVerified($user['id']);
        flash('success', 'Email verified successfully. You can now log in.');
        header('Location: ' . APP_URL . '/index.php?url=login');
        exit;
    }

    public function verifyOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        AuthMiddleware::csrf();

        $email = trim($_POST['email'] ?? '');
        $otp = trim($_POST['otp'] ?? '');

        if (empty($email) || empty($otp)) {
            flash('error', 'Please enter your email and OTP.');
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        $user = $this->userModel->getByEmailForVerification($email);
        if (!$user) {
            flash('error', 'Email not found.');
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        if (!empty($user['email_verified_at'])) {
            flash('success', 'Your email is already verified. Please log in.');
            header('Location: ' . APP_URL . '/index.php?url=login');
            exit;
        }

        $lockedUntil = $user['otp_locked_until'] ? strtotime($user['otp_locked_until']) : 0;
        if ($lockedUntil && $lockedUntil > time()) {
            $remaining = ceil(($lockedUntil - time()) / 60);
            flash('error', 'Too many OTP attempts. Try again in ' . $remaining . ' minute(s).');
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        $now = time();
        $expiresAt = $user['otp_expires_at'] ? strtotime($user['otp_expires_at']) : 0;

        if ($user['otp_code'] !== $otp || $expiresAt < $now) {
            $attempts = (int) ($user['otp_attempts'] ?? 0) + 1;
            if ($attempts >= OTP_MAX_ATTEMPTS) {
                $lockUntil = date('Y-m-d H:i:s', time() + (OTP_LOCKOUT_MINUTES * 60));
                $this->userModel->setOtpLockout($user['id'], $lockUntil);
                $this->userModel->setOtpAttempts($user['id'], 0);
                flash('error', 'Too many OTP attempts. Please try again later.');
            } else {
                $this->userModel->setOtpAttempts($user['id'], $attempts);
                flash('error', 'Invalid or expired OTP.');
            }
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        $this->userModel->markEmailVerified($user['id']);
        flash('success', 'Email verified successfully. You can now log in.');
        header('Location: ' . APP_URL . '/index.php?url=login');
        exit;
    }

    public function resendVerification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        AuthMiddleware::csrf();

        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            flash('error', 'Please provide your email.');
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        $user = $this->userModel->getByEmailForVerification($email);
        if (!$user) {
            flash('error', 'Email not found.');
            header('Location: ' . APP_URL . '/index.php?url=verify-email');
            exit;
        }

        if (!empty($user['email_verified_at'])) {
            flash('success', 'Your email is already verified. Please log in.');
            header('Location: ' . APP_URL . '/index.php?url=login');
            exit;
        }

        $verificationToken = bin2hex(random_bytes(32));
        $otpCode = strval(random_int(100000, 999999));
        $otpExpiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));

        $this->userModel->setVerificationData($user['id'], $verificationToken, $otpCode, $otpExpiresAt);
        $emailResult = $this->sendVerificationEmail($email, $user['first_name'], $verificationToken, $otpCode);

        $_SESSION['pending_verify_email'] = $email;
        if ($emailResult['sent']) {
            flash('success', 'Verification email sent. Please check your inbox.');
        } elseif ($this->isLocalEnvironment()) {
            flash('warning', 'Email sending failed. For local testing, use this OTP: ' . $otpCode);
        } else {
            flash('error', 'Could not send verification email. Please check SMTP configuration and try again.');
        }
        header('Location: ' . APP_URL . '/index.php?url=verify-email');
        exit;
    }

    private function sendVerificationEmail($email, $firstName, $token, $otpCode) {
        $verifyUrl = APP_URL . '/index.php?url=verify-email&token=' . urlencode($token);

        $subject = 'Verify your email - ' . APP_NAME;
        $templatePath = ROOT_PATH . '/templates/email/verify-email.html';
        if (file_exists($templatePath)) {
            $template = file_get_contents($templatePath);
            $html = strtr($template, [
                '{{app_name}}' => APP_NAME,
                '{{first_name}}' => htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'),
                '{{verify_url}}' => $verifyUrl,
                '{{otp_code}}' => $otpCode,
                '{{otp_expiry}}' => OTP_EXPIRY_MINUTES
            ]);
        } else {
            $html = "<p>Hi {$firstName},</p>" .
                '<p>Thank you for registering. Please verify your email using one of the options below:</p>' .
                '<p><strong>Option 1 (Link):</strong><br><a href="' . $verifyUrl . '">Verify Email</a></p>' .
                '<p><strong>Option 2 (OTP):</strong><br>Your OTP code is: <strong>' . $otpCode . '</strong></p>' .
                '<p>This OTP expires in ' . OTP_EXPIRY_MINUTES . ' minutes.</p>' .
                '<p>If you did not register, you can ignore this email.</p>';
        }

        $text = "Hi {$firstName},\n\n" .
            "Verify your email using this link: {$verifyUrl}\n" .
            "Or use this OTP: {$otpCode} (expires in " . OTP_EXPIRY_MINUTES . " minutes).\n\n" .
            "If you did not register, ignore this email.";

        try {
            $mailer = new Mailer();
            $mailer->send($email, $firstName, $subject, $html, $text);
            return ['sent' => true, 'error' => null];
        } catch (\Throwable $e) {
            // Keep registration flow; email can be resent
            $this->logModel->create(LOG_USER_UPDATE, 'Email send failed for ' . $email . ': ' . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    private function isLocalEnvironment() {
        $host = parse_url(APP_URL, PHP_URL_HOST);
        return in_array($host, ['localhost', '127.0.0.1'], true);
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logModel->create(LOG_LOGOUT, 'User logged out', $_SESSION['user_id']);
        }
        session_destroy();
        session_start();
        flash('success', 'You have been logged out.');
        header('Location: ' . APP_URL . '/index.php?url=login');
        exit;
    }
}
