<?php
/* $pageTitle, $extraCss, $isAdmin set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= htmlspecialchars($pageTitle ?? 'My Profile') ?></h2>
        </div>
    </div>

    <div class="page-content">
        <div class="card">
            <h3 style="margin: 0 0 12px;"><i data-lucide="lock"></i> Change Password</h3>
            <form action="<?= APP_URL ?>/index.php?url=profile" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="current_password">Current Password <span class="required">*</span></label>
                    <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password <span class="required">*</span></label>
                    <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" minlength="8" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8" required>
                </div>

                <button type="submit" class="btn btn-accent"><i data-lucide="key"></i> Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
