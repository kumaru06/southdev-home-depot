<?php
$pageTitle = '403 - Access Denied';
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div style="text-align:center; padding:6rem 2rem 4rem;">
        <div style="font-size:8rem; font-weight:800; color:var(--accent); line-height:1; margin-bottom:1rem;">403</div>
        <h2 style="font-size:1.5rem; font-weight:700; color:var(--charcoal); margin-bottom:.75rem;">Access Denied</h2>
        <p style="color:var(--steel); max-width:400px; margin:0 auto 2rem; line-height:1.6;">You don't have permission to access this resource. If you believe this is an error, please contact the administrator.</p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="<?= APP_URL ?>" class="btn btn-accent">
                <i data-lucide="home" style="width:16px;height:16px;"></i> Back to Home
            </a>
            <a href="<?= APP_URL ?>/index.php?url=login" class="btn btn-outline">
                <i data-lucide="log-in" style="width:16px;height:16px;"></i> Sign In
            </a>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
