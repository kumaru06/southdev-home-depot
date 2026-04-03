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
                        <div class="auth-icon-wrap">
                            <i data-lucide="shield" class="auth-icon"></i>
                        </div>
                        <div class="auth-brand-text">
                            <h2><?= APP_NAME ?></h2>
                            <span class="auth-role-badge">Admin / Staff</span>
                            <p class="auth-tagline">Sign in to admin/staff portal</p>
                        </div>
                    </div>

                    <form action="<?= APP_URL ?>/index.php?url=admin-login" method="POST" id="admin-login-form">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-icon-wrap">
                                <i data-lucide="mail" class="input-icon"></i>
                                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" autocomplete="username" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-icon-wrap">
                                <i data-lucide="lock" class="input-icon"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
                                <button type="button" class="auth-pw-toggle" onclick="(function(b){var i=b.previousElementSibling;var isP=i.type==='password';i.type=isP?'text':'password';b.innerHTML=isP?'<i data-lucide=\'eye-off\'></i>':'<i data-lucide=\'eye\'></i>';if(window.lucide)lucide.createIcons();})(this)" aria-label="Toggle password visibility">
                                    <i data-lucide="eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-accent btn-block">
                            <i data-lucide="log-in"></i> Sign In
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
