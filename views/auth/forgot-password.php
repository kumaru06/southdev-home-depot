<?php
$pageTitle = 'Forgot Password';
require_once INCLUDES_PATH . '/header.php';
?>

<?php require_once INCLUDES_PATH . '/navbar.php'; ?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card card auth-card--center">
            <div class="auth-panel">
                <div class="auth-brand">
                    <div>
                        <h2><?= APP_NAME ?></h2>
                        <p class="auth-tagline">Enter your email to reset your password</p>
                    </div>
                </div>

                <?php if (has_flash('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars(flash('error'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if (has_flash('success')): ?>
                    <div class="alert alert-success"><?= htmlspecialchars(flash('success'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form action="<?= APP_URL ?>/index.php?url=forgot-password" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="you@example.com">
                    </div>
                    <button type="submit" class="btn btn-accent btn-block">Send reset link</button>
                </form>

                <div class="auth-footer">
                    <p><a href="<?= APP_URL ?>/index.php?url=login">Back to login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
