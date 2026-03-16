<?php
$pageTitle = 'Login';
require_once INCLUDES_PATH . '/header.php';

$authImage = null;
// Preferred image (user-provided)
$preferredRel = 'assets/uploads/images/image.png';
$preferredFull = ROOT_PATH . '/' . $preferredRel;
if (file_exists($preferredFull)) {
    $authImage = APP_URL . '/' . $preferredRel;
}

// Fallbacks
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

<div class="auth-page-backdrop" style="--auth-bg: <?= $authImage ? "url('" . htmlspecialchars($authImage) . "')" : 'none' ?>;">
    <div class="auth-page-blur"></div>
    <div class="container">
        <div class="auth-wrapper auth-wrapper--blur">
            <div class="auth-card card auth-card--split">
            <div class="auth-split">
                <div class="auth-media" role="img" aria-label="SouthDev Home Depot building"
                    style="--auth-image: <?= $authImage ? "url('" . htmlspecialchars($authImage) . "')" : 'none' ?>;">
                    <div class="auth-media-overlay"></div>
                    <div class="auth-media-content">
                        <div class="auth-media-badge">Welcome back</div>
                        <div class="auth-media-title"><?= APP_NAME ?></div>
                        <div class="auth-media-subtitle"><?= APP_TAGLINE ?></div>
                    </div>
                </div>

                <div class="auth-panel">
                    <div class="auth-brand">
                        <i data-lucide="grid-3x3" class="auth-icon"></i>
                        <div>
                            <h2><?= APP_NAME ?></h2>
                            <p class="auth-tagline">Sign in to your account</p>
                        </div>
                    </div>

                    <form action="<?= APP_URL ?>/index.php?url=login" method="POST" id="login-form">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-icon-wrap">
                                <i data-lucide="mail" class="input-icon"></i>
                                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-icon-wrap">
                                <i data-lucide="lock" class="input-icon"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-accent btn-block">
                            <i data-lucide="log-in"></i> Sign In
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Need to verify your email? <a href="<?= APP_URL ?>/index.php?url=verify-email">Resend verification</a></p>
                        <p><a href="<?= APP_URL ?>/index.php?url=forgot-password">Forgot password?</a></p>
                        <p><a href="<?= APP_URL ?>/index.php?url=admin-login">Continue as administrator</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
