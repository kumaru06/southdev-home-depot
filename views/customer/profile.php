<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <h1 class="page-heading"><i data-lucide="user"></i> My Profile</h1>

    <div class="profile-grid">
        <!-- Left column: Profile card -->
        <div class="profile-card-sidebar card">
            <div class="profile-card-photo-area">
                <?php
                    $profileImage = $user['profile_image'] ?? '';
                    $profileImageUrl = $profileImage ? (APP_URL . '/assets/uploads/profiles/' . rawurlencode($profileImage)) : '';
                    $initials = strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1));
                ?>
                <div class="profile-photo profile-photo--lg">
                    <?php if (!empty($profileImage)): ?>
                        <img src="<?= $profileImageUrl ?>" alt="Profile photo" class="profile-avatar-img">
                    <?php else: ?>
                        <div class="profile-avatar-fallback" aria-label="Profile initials"><?= htmlspecialchars($initials) ?></div>
                    <?php endif; ?>
                </div>
                <h3 class="profile-card-name"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h3>
                <p class="profile-card-email"><i data-lucide="mail"></i> <?= htmlspecialchars($user['email']) ?></p>
                <?php if (!empty($user['phone'])): ?>
                    <p class="profile-card-phone"><i data-lucide="phone"></i> <?= htmlspecialchars($user['phone']) ?></p>
                <?php endif; ?>
            </div>
            <div class="profile-card-upload">
                <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" enctype="multipart/form-data" id="photo-form">
                    <?= csrf_field() ?>
                    <label class="profile-upload-label">
                        <i data-lucide="camera"></i>
                        <span>Change Photo</span>
                        <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="this.form.submit()">
                    </label>
                    <p class="profile-upload-hint">JPG, PNG or WebP. Max 2MB.</p>
                </form>
            </div>
        </div>

        <!-- Right column: Forms -->
        <div class="profile-forms">
            <div class="card profile-section-card">
                <div class="profile-section-header">
                    <i data-lucide="user-circle"></i>
                    <div>
                        <h3>Personal Information</h3>
                        <p>Update your personal details and contact information.</p>
                    </div>
                </div>
                <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="profile-form">
                    <?= csrf_field() ?>

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

                    <div class="form-divider"></div>

                    <div class="form-section-label"><i data-lucide="map-pin"></i> Shipping Address</div>

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

            <div class="card profile-section-card">
                <div class="profile-section-header">
                    <i data-lucide="shield"></i>
                    <div>
                        <h3>Security</h3>
                        <p>Change your password to keep your account secure.</p>
                    </div>
                </div>
                <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="password-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Current Password <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                    </div>

                    <div class="form-row">
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
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
