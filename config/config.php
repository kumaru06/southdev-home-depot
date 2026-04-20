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
    : 'https://southdev-home-depot.infinityfreeapp.com'));
define('APP_VERSION', env('APP_VERSION', '1.0.0'));
define('APP_LOCATION', env('APP_LOCATION', 'Davao City, Philippines'));
define('APP_MAP_LAT', env('APP_MAP_LAT', ''));
define('APP_MAP_LNG', env('APP_MAP_LNG', ''));
define('APP_GOOGLE_MAPS_API_KEY', env('APP_GOOGLE_MAPS_API_KEY', ''));

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
