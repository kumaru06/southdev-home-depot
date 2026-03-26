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
<body class="<?= isset($isAdmin) && $isAdmin ? 'admin-layout' : '' ?>">

<!-- Loading Overlay (visible by default while page loads) -->
<div id="loading-overlay" aria-hidden="false">
    <div class="loading-box">
        <div class="loading-brand">
            <img src="<?= APP_URL ?>/assets/uploads/images/image2.png" alt="<?= htmlspecialchars(APP_NAME) ?>" onerror="this.style.display='none'">
        </div>
        <div class="loading-ring" aria-hidden="true"></div>
        <div class="loading-msg">Loading <span class="loading-dot">.</span><span class="loading-dot">.</span><span class="loading-dot">.</span></div>
        <div class="loading-sub">Preparing your personalized storefront</div>
    </div>
    <noscript>
        <style>
            #loading-overlay { display:none !important; }
        </style>
    </noscript>
</div>

<script>
// Immediately show overlay (in case CSS hasn't loaded yet) using inline styles
(function(){
    var el = document.getElementById('loading-overlay');
    if(!el) return;
    el.style.position = 'fixed';
    el.style.inset = '0';
    el.style.display = 'flex';
    el.style.alignItems = 'center';
    el.style.justifyContent = 'center';
    el.style.zIndex = 9999;
    el.style.background = 'rgba(27,42,74,.75)';
    // Hide overlay after window load with fade, but enforce a minimum visible time
    var startTs = Date.now();
    var MIN_VISIBLE = 900; // milliseconds - reduced so loader is shorter but still visible
    function doHide(){
        el.style.transition = 'opacity .5s ease';
        el.style.opacity = '0';
        setTimeout(function(){ try{ el.style.display='none'; el.remove(); }catch(e){} }, 540);
    }
    function hideOverlay(){
        var elapsed = Date.now() - startTs;
        if(elapsed >= MIN_VISIBLE){
            doHide();
        } else {
            setTimeout(doHide, MIN_VISIBLE - elapsed);
        }
    }
    if(document.readyState === 'complete'){
        // If already loaded, ensure minimum visible time before hide
        hideOverlay();
    } else {
        window.addEventListener('load', hideOverlay);
        // fallback: hide after 8s if load never fires
        setTimeout(function(){ if(document.body) hideOverlay(); }, 8000);
    }
})();
</script>

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
