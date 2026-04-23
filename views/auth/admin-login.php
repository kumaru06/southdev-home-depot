<?php
$pageTitle = 'Staff / Admin Login';
require_once INCLUDES_PATH . '/header.php';

$authImage = null;
$preferredRel = 'assets/uploads/images/image.png';
$preferredFull = ROOT_PATH . '/' . $preferredRel;
if (file_exists($preferredFull)) {
    $authImage = APP_URL . '/' . $preferredRel;
}
foreach (['jpg', 'jpeg', 'png'] as $ext) {
    if ($authImage) break;
    $rel = 'assets/uploads/auth/login-building.' . $ext;
    $full = ROOT_PATH . '/' . $rel;
    if (file_exists($full)) {
        $authImage = APP_URL . '/' . $rel;
        break;
    }
}
?>

<?php require_once INCLUDES_PATH . '/navbar.php'; ?>

<style>
.admin-icon-wrap {
    background: none !important;
    box-shadow: none !important;
    color: var(--accent) !important;
    width: auto !important;
    height: auto !important;
    border-radius: 0 !important;
    padding: 2px;
}
.admin-icon-wrap svg {
    width: 30px !important;
    height: 30px !important;
    color: var(--accent) !important;
    stroke: var(--accent) !important;
}
.auth-panel .input-icon-wrap .form-control,
.auth-panel .fl-wrap .fl-input {
    padding-left: 14px !important;
}
</style>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card card auth-card--split">
            <div class="auth-split">
                <div class="auth-media" role="img" aria-label="SouthDev Home Depot building"
                    style="--auth-image: <?= $authImage ? "url('" . htmlspecialchars($authImage) . "')" : 'none' ?>;">
                    <div class="auth-media-overlay"></div>
                    <div class="auth-media-content">
                        <div class="auth-media-badge">Staff Admin Access</div>
                        <div class="auth-media-title"><?= APP_NAME ?></div>
                        <div class="auth-media-subtitle">Admin login</div>
                    </div>
                </div>

                <div class="auth-panel">
                    <div class="auth-brand">
                        <div class="auth-icon-wrap admin-icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="auth-brand-text">
                            <h2><?= APP_NAME ?></h2>
                            <span class="auth-role-badge">Admin / Staff</span>
                            <p class="auth-tagline">Sign in to admin/staff portal</p>
                        </div>
                    </div>

                    <form action="<?= APP_URL ?>/index.php?url=admin-login" method="POST" id="admin-login-form">
                        <?= csrf_field() ?>

                        <div class="form-group fl-group">
                            <div class="fl-wrap">
                                <input type="text" id="email" name="email" class="form-control fl-input" placeholder=" " autocomplete="username" required autofocus>
                                <label for="email" class="fl-label">Username</label>
                            </div>
                        </div>

                        <div class="form-group fl-group">
                            <div class="fl-wrap">
                                <input type="password" id="password" name="password" class="form-control fl-input" placeholder=" " autocomplete="current-password" required>
                                <label for="password" class="fl-label">Password</label>
                                <button type="button" class="auth-pw-toggle" onclick="(function(b){var i=b.previousElementSibling.previousElementSibling;var isP=i.type==='password';i.type=isP?'text':'password';b.textContent=isP?'Hide':'Show';})(this)" aria-label="Toggle password visibility">Show</button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-accent btn-block">
                            Sign In
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Return to <a href="<?= APP_URL ?>/index.php?url=login">customer login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
