<?php
/**
 * Application Configuration
 */

// Load Composer autoloader & .env
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load .env file if it exists (won't error if missing)
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Helper: read env var with fallback
function env(string $key, $default = null) {
    $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($val === false || $val === null) return $default;
    // Cast common string booleans
    $lower = strtolower((string) $val);
    if ($lower === 'true') return true;
    if ($lower === 'false') return false;
    if ($lower === 'null') return null;
    return $val;
}

// Set timezone to Philippine Time (UTC+8)
date_default_timezone_set(env('TIMEZONE', 'Asia/Manila'));

// Detect environment
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'])
    || strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false;

// Basic browser-side security headers. Keep CSP conservative to avoid breaking inline styles/scripts.
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('X-Permitted-Cross-Domain-Policies: none');
    if (!$is_local) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Harden PHP sessions before starting the session.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');

// Harden session cookies before starting the session
session_set_cookie_params([
    'lifetime' => 0,           // session cookie (expires when browser closes)
    'path'     => '/',
    'domain'   => '',
    'secure'   => !$is_local,  // HTTPS only in production
    'httponly'  => true,        // prevent JS access to session cookie
    'samesite' => 'Lax'        // CSRF protection
]);
session_name('SHDSESSID');     // custom session name hides PHP identity
session_start();

// Application settings
define('APP_NAME', env('APP_NAME', 'Southdev Home Depot'));
define('APP_TAGLINE', env('APP_TAGLINE', 'Davao City\'s Premier Tiles & Hardware Supply'));
define('APP_URL', env('APP_URL', $is_local 
    ? 'http://localhost/southdev-home-depot' 
    : 'https://southdevhomedepotdavao.com'));
define('APP_VERSION', env('APP_VERSION', '1.0.0'));
define('APP_LOCATION', env('APP_LOCATION', 'Davao City, Philippines'));
define('APP_MAP_LAT', env('APP_MAP_LAT', ''));
define('APP_MAP_LNG', env('APP_MAP_LNG', ''));
define('APP_GOOGLE_MAPS_API_KEY', env('APP_GOOGLE_MAPS_API_KEY', ''));

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID',     trim((string) env('GOOGLE_CLIENT_ID', '')));
define('GOOGLE_CLIENT_SECRET', trim((string) env('GOOGLE_CLIENT_SECRET', '')));
define('GOOGLE_REDIRECT_URI',  trim((string) env('GOOGLE_REDIRECT_URI', rtrim(APP_URL, '/') . '/google-callback')));

// Google reCAPTCHA v2 (https://www.google.com/recaptcha/admin) — free; skipped when keys empty
define('RECAPTCHA_SITE_KEY',   trim((string) env('RECAPTCHA_SITE_KEY', '')));
define('RECAPTCHA_SECRET_KEY', trim((string) env('RECAPTCHA_SECRET_KEY', '')));

// PayMongo Configuration
define('PAYMONGO_ENABLED', env('PAYMONGO_ENABLED', true));
define('PAYMONGO_SECRET_KEY', env('PAYMONGO_SECRET_KEY', ''));
define('PAYMONGO_PUBLIC_KEY', env('PAYMONGO_PUBLIC_KEY', ''));
define('PAYMONGO_WEBHOOK_SECRET', env('PAYMONGO_WEBHOOK_SECRET', ''));

// Mailer (PHPMailer) Configuration
define('MAIL_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', (int) env('MAIL_PORT', 587));
define('MAIL_USERNAME', env('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', ''));
define('MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_EMAIL', env('MAIL_FROM_EMAIL', ''));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'Southdev Home Depot'));

// Email verification settings
define('OTP_EXPIRY_MINUTES', 5);
define('OTP_MAX_ATTEMPTS', 5);
define('OTP_LOCKOUT_MINUTES', 15);

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ASSETS_PATH . '/uploads');

// Error reporting – show errors locally, hide in production
error_reporting(E_ALL);
if ($is_local) {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf($token = null) {
    // Check POST body, then X-CSRF-Token header (for AJAX)
    $token = $token 
        ?? ($_POST['csrf_token'] ?? null)
        ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function regenerate_csrf() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// Password validation helper (shared across all controllers)
function validate_password($password) {
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must include at least one lowercase letter.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must include at least one uppercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must include at least one number.';
    }
    return null; // valid
}

// Flash message helpers
function flash($key, $message = null) {
    if ($message) {
        $_SESSION['flash_' . $key] = $message;
    } else {
        $msg = $_SESSION['flash_' . $key] ?? null;
        unset($_SESSION['flash_' . $key]);
        return $msg;
    }
}

function has_flash($key) {
    return isset($_SESSION['flash_' . $key]);
}

/** True when both reCAPTCHA keys are configured in .env */
function recaptcha_enabled(): bool {
    return RECAPTCHA_SITE_KEY !== '' && RECAPTCHA_SECRET_KEY !== '';
}

/**
 * Verify Google reCAPTCHA v2 response.
 * Returns true when captcha is disabled (keys not set) so local/dev keeps working.
 */
function verify_recaptcha(?string $response = null): bool {
    if (!recaptcha_enabled()) {
        return true;
    }

    $response = $response ?? (string) ($_POST['g-recaptcha-response'] ?? '');
    if ($response === '') {
        return false;
    }

    $payload = http_build_query([
        'secret'   => RECAPTCHA_SECRET_KEY,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 10,
        ],
    ]);

    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    if ($raw === false) {
        return false;
    }

    $data = json_decode($raw, true);
    return !empty($data['success']);
}

/** Render the reCAPTCHA v2 checkbox widget (empty string if not configured). */
function recaptcha_widget(string $extraClass = ''): string {
    if (!recaptcha_enabled()) {
        return '';
    }
    $class = trim('g-recaptcha ' . $extraClass);
    return '<div class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"'
        . ' data-sitekey="' . htmlspecialchars(RECAPTCHA_SITE_KEY, ENT_QUOTES, 'UTF-8') . '"></div>';
}
