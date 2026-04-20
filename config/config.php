<?php
/**
 * Application Configuration
 */

// Set timezone to Philippine Time (UTC+8)
date_default_timezone_set('Asia/Manila');

session_start();

// Detect environment
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'])
    || strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false;

// Application settings
define('APP_NAME', 'Southdev Home Depot');
define('APP_TAGLINE', 'Davao City\'s Premier Tiles & Hardware Supply');
define('APP_URL', $is_local 
    ? 'http://localhost/southdev-home-depot' 
    : 'https://southdev-home-depot.infinityfreeapp.com');
define('APP_VERSION', '1.0.0');
define('APP_LOCATION', 'Davao City, Philippines');
// Optional: default coordinates for the main store location. If empty,
// the Locations page will attempt to geocode `APP_LOCATION` at runtime.
define('APP_MAP_LAT', getenv('APP_MAP_LAT') ?: '');
define('APP_MAP_LNG', getenv('APP_MAP_LNG') ?: '');
// Optional: Google Maps JavaScript API key. If set, the Locations page will
// use Google Maps for the interactive map and geocoding. Obtain a key from
// https://console.cloud.google.com/apis/credentials and enable Maps JavaScript API.
define('APP_GOOGLE_MAPS_API_KEY', getenv('APP_GOOGLE_MAPS_API_KEY') ?: '');

// PayMongo Configuration (TEST — live keys restricted, using test keys for full payment method support)
// Get keys from: https://dashboard.paymongo.com/settings/api-keys
define('PAYMONGO_ENABLED', true);  // Set to false to disable PayMongo
define('PAYMONGO_SECRET_KEY', getenv('PAYMONGO_SECRET_KEY') ?: 'sk_test_xxxxxxxxxxxx');
define('PAYMONGO_PUBLIC_KEY', getenv('PAYMONGO_PUBLIC_KEY') ?: 'pk_test_xxxxxxxxxxxx');
define('PAYMONGO_WEBHOOK_SECRET', getenv('PAYMONGO_WEBHOOK_SECRET') ?: 'whk_test_xxxxxxxxxxxx');  // Update with test webhook secret from PayMongo dashboard

// Mailer (PHPMailer) Configuration
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
// SMTP credentials (set via env or hardcoded below for convenience)
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: 'feakzume@gmail.com');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: 'tnoe hibz prqh kwfs');
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls');
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: 'feakzume@gmail.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Southdev Home Depot');

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

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf($token = null) {
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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
