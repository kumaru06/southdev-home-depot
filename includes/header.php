<?php
/**
 * SouthDev Home Depot – Header Include
 * Enterprise layout with Inter font, CSRF meta, design tokens
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="description" content="<?= APP_TAGLINE ?>">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – ' : '' ?><?= APP_NAME ?></title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

    <script>var APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>

<!-- Loading Overlay -->
<div id="loading-overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(28,28,28,.7);align-items:center;justify-content:center;">
    <div style="width:40px;height:40px;border:3px solid rgba(255,255,255,.2);border-top-color:#C62828;border-radius:50%;animation:spin .6s linear infinite;"></div>
    <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
</div>

<?php if (has_flash('success') || has_flash('error') || has_flash('warning')): ?>
    <div class="flash-container" role="status" aria-live="polite">
        <?php if (has_flash('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (has_flash('error')): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>
        <?php if (has_flash('warning')): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['flash_warning']); unset($_SESSION['flash_warning']); ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>
