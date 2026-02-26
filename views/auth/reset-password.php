<?php
$pageTitle = 'Reset Password';
require_once INCLUDES_PATH . '/header.php';
$token = $token ?? ($_GET['token'] ?? '');
?>

<?php require_once INCLUDES_PATH . '/navbar.php'; ?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card card auth-card--center">
            <div class="auth-panel">
                <div class="auth-brand">
                    <i data-lucide="hard-hat" class="auth-icon"></i>
                    <div>
                        <h2><?= APP_NAME ?></h2>
                        <p class="auth-tagline">Choose a new password</p>
                    </div>
                </div>

                <?php if (has_flash('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars(flash('error'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if (has_flash('success')): ?>
                    <div class="alert alert-success"><?= htmlspecialchars(flash('success'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form action="<?= APP_URL ?>/index.php?url=reset-password" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="form-group">
                        <label for="password">New password</label>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="Enter new password">
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirm password</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required placeholder="Confirm new password">
                    </div>

                    <button type="submit" class="btn btn-accent btn-block">Update password</button>
                </form>

                <div class="auth-footer">
                    <p><a href="<?= APP_URL ?>/index.php?url=login">Back to login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
