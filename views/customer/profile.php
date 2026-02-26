<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <h1 class="page-heading"><i data-lucide="user"></i> My Profile</h1>

    <div class="card">
        <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="profile-form" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <?php
                $profileImage = $user['profile_image'] ?? '';
                $profileImageUrl = $profileImage ? (APP_URL . '/assets/uploads/profiles/' . rawurlencode($profileImage)) : '';
                $initials = strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1));
            ?>

            <div class="profile-photo-row">
                <div class="profile-photo">
                    <?php if (!empty($profileImage)): ?>
                        <img src="<?= $profileImageUrl ?>" alt="Profile photo" class="profile-avatar-img">
                    <?php else: ?>
                        <div class="profile-avatar-fallback" aria-label="Profile initials"><?= htmlspecialchars($initials) ?></div>
                    <?php endif; ?>
                </div>

                <div class="profile-photo-actions">
                    <div class="profile-photo-title">Profile Photo</div>
                    <div class="profile-photo-subtitle">Upload a JPG, PNG, or WebP (max 2MB).</div>
                    <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-col">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                <div class="form-group form-col">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-col">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group form-col">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group form-col">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                </div>
                <div class="form-group form-col">
                    <label for="state">Province</label>
                    <input type="text" id="state" name="state" class="form-control" value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                </div>
                <div class="form-group form-col">
                    <label for="zip_code">Zip Code</label>
                    <input type="text" id="zip_code" name="zip_code" class="form-control" value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-accent"><i data-lucide="save"></i> Save Changes</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top: 16px;">
        <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="password-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_password">

            <h3 style="margin: 0 0 12px;"><i data-lucide="lock"></i> Change Password</h3>

            <div class="form-row">
                <div class="form-group form-col">
                    <label for="current_password">Current Password <span class="required">*</span></label>
                    <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                </div>
                <div class="form-group form-col">
                    <label for="new_password">New Password <span class="required">*</span></label>
                    <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="form-group form-col">
                    <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-outline"><i data-lucide="key"></i> Update Password</button>
            </div>
        </form>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
