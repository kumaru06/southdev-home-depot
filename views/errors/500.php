<?php
$pageTitle = '500 - Server Error';
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div style="text-align:center; padding:6rem 2rem 4rem;">
        <div style="font-size:8rem; font-weight:800; color:var(--accent); line-height:1; margin-bottom:1rem;">500</div>
        <h2 style="font-size:1.5rem; font-weight:700; color:var(--charcoal); margin-bottom:.75rem;">Internal Server Error</h2>
        <p style="color:var(--steel); max-width:400px; margin:0 auto 2rem; line-height:1.6;">Something went wrong on our end. Our team has been notified. Please try again later.</p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="<?= APP_URL ?>" class="btn btn-accent">
                <i data-lucide="home" style="width:16px;height:16px;"></i> Back to Home
            </a>
            <a href="javascript:location.reload()" class="btn btn-outline">
                <i data-lucide="refresh-cw" style="width:16px;height:16px;"></i> Try Again
            </a>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
