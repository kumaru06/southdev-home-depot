<?php
$pageTitle = '404 - Page Not Found';
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div style="text-align:center; padding:6rem 2rem 4rem;">
        <div style="font-size:8rem; font-weight:800; color:var(--accent); line-height:1; margin-bottom:1rem;">404</div>
        <h2 style="font-size:1.5rem; font-weight:700; color:var(--charcoal); margin-bottom:.75rem;">Page Not Found</h2>
        <p style="color:var(--steel); max-width:400px; margin:0 auto 2rem; line-height:1.6;">The page you're looking for doesn't exist or has been moved to a different location.</p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="<?= APP_URL ?>" class="btn btn-accent">
                <i data-lucide="home" style="width:16px;height:16px;"></i> Back to Home
            </a>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-outline">
                <i data-lucide="package" style="width:16px;height:16px;"></i> Browse Products
            </a>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
