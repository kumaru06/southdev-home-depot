<?php
/**
 * SouthDev Home Depot – Header Include
 * Enterprise layout with Inter font, CSRF meta, design tokens
 */
// Prevent caching of pages that depend on authentication state so browsers
// don't serve stale HTML showing the wrong Login/Register state after logout.
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Security headers
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    // Content Security Policy – allow same-origin assets plus approved third-party providers (Maps, reCAPTCHA)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://www.gstatic.com; font-src 'self' https://fonts.gstatic.com https://unpkg.com; img-src 'self' data: blob: https:; connect-src 'self' https://api.paymongo.com https://www.google.com https://www.gstatic.com; frame-src 'self' https://www.google.com https://www.gstatic.com https://recaptcha.google.com https://maps.google.com; child-src 'self' https://www.google.com https://www.gstatic.com https://recaptcha.google.com https://maps.google.com; frame-ancestors 'self';");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="description" content="<?= APP_TAGLINE ?>">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – ' : '' ?><?= APP_NAME ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?= APP_URL ?>/assets/uploads/favicon/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= APP_URL ?>/assets/uploads/favicon/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= APP_URL ?>/assets/uploads/favicon/favicon-16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= APP_URL ?>/assets/uploads/favicon/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <link rel="stylesheet" href="https://unpkg.com/lucide-static@latest/font/lucide.css">

    <!-- Stylesheets -->
    <?php
        $asset_v = function (string $relativePath) {
            $fullPath = ROOT_PATH . '/' . ltrim($relativePath, '/');
            return file_exists($fullPath) ? filemtime($fullPath) : APP_VERSION;
        };
    ?>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= $asset_v('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/responsive.css?v=<?= $asset_v('assets/css/responsive.css') ?>">
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/<?= htmlspecialchars($css) ?>?v=<?= $asset_v('assets/css/' . $css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

        <script>var APP_URL = <?= json_encode(APP_URL) ?>;</script>
</head>
<body class="<?= isset($isAdmin) && $isAdmin ? 'admin-layout' : '' ?>">

<?php if (has_flash('success') || has_flash('error') || has_flash('warning')): ?>
    <div class="toast-container" role="status" aria-live="polite">
        <?php if (has_flash('success')): ?>
            <div class="toast toast--success" data-auto-dismiss="5000">
                <div class="toast-icon"><i data-lucide="check-circle"></i></div>
                <div class="toast-body">
                    <span class="toast-title">Success</span>
                    <span class="toast-message"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
                </div>
                <button class="toast-close" aria-label="Close"><i data-lucide="x"></i></button>
                <div class="toast-progress"><div class="toast-progress-bar"></div></div>
            </div>
        <?php endif; ?>
        <?php if (has_flash('error')): ?>
            <div class="toast toast--error" data-auto-dismiss="6000">
                <div class="toast-icon"><i data-lucide="alert-circle"></i></div>
                <div class="toast-body">
                    <span class="toast-title">Error</span>
                    <span class="toast-message"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
                </div>
                <button class="toast-close" aria-label="Close"><i data-lucide="x"></i></button>
                <div class="toast-progress"><div class="toast-progress-bar"></div></div>
            </div>
        <?php endif; ?>
        <?php if (has_flash('warning')): ?>
            <div class="toast toast--warning" data-auto-dismiss="5000">
                <div class="toast-icon"><i data-lucide="alert-triangle"></i></div>
                <div class="toast-body">
                    <span class="toast-title">Warning</span>
                    <span class="toast-message"><?= htmlspecialchars($_SESSION['flash_warning']); unset($_SESSION['flash_warning']); ?></span>
                </div>
                <button class="toast-close" aria-label="Close"><i data-lucide="x"></i></button>
                <div class="toast-progress"><div class="toast-progress-bar"></div></div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
