<?php
$pageTitle = $pageTitle ?? 'View User';
$extraCss  = ['admin.css'];
$isAdmin   = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$initials = strtoupper(substr($user['first_name'] ?? '', 0, 1) . substr($user['last_name'] ?? '', 0, 1));
$isCustomer = ($user['role_name'] ?? '') === 'customer';
$profileImage = $user['profile_image'] ?? '';
$profileImageUrl = $profileImage ? (APP_URL . '/assets/uploads/profiles/' . rawurlencode($profileImage)) : '';
$completedOrders = array_filter($orders ?? [], fn($o) => ($o['status'] ?? '') === 'delivered');
$pendingOrders   = array_filter($orders ?? [], fn($o) => in_array($o['status'] ?? '', ['pending', 'processing']));
$roleName = (string)($user['role_name'] ?? '');
$roleLabel = ucwords(str_replace('_', ' ', $roleName));
$isVerified = !empty($user['email_verified_at']);
$statusLabel = $user['is_active'] ? 'Active' : ($isCustomer ? 'Blocked' : 'Inactive');
$joinedDate = !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A';
$updatedDate = !empty($user['updated_at']) ? date('M d, Y', strtotime($user['updated_at'])) : 'N/A';
$verifiedDate = $isVerified ? date('M d, Y', strtotime($user['email_verified_at'])) : null;

$portalScopeLabel = 'Standard portal access';
$portalAccessLabel = 'Standard portal access';
switch ($roleName) {
    case 'super_admin':
        $portalScopeLabel = 'Super Admin Portal';
        $portalAccessLabel = 'Full system access';
        break;
    case 'inventory_incharge':
        $portalScopeLabel = 'Inventory Portal';
        $portalAccessLabel = 'Inventory and stock controls';
        break;
    case 'staff':
        $portalScopeLabel = 'Admin / Staff Portal';
        $portalAccessLabel = 'Orders, support, and operations';
        break;
}

$staffProfileFields = [
    ['label' => 'User ID', 'value' => '#' . (int)$user['id'], 'icon' => 'hash'],
    ['label' => 'Username', 'value' => !empty($user['username']) ? htmlspecialchars($user['username']) : '<em class="vp-empty">Not set</em>', 'icon' => 'at-sign'],
    ['label' => 'Phone', 'value' => !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<em class="vp-empty">Not set</em>', 'icon' => 'phone'],
    ['label' => 'Joined', 'value' => $joinedDate, 'icon' => 'calendar'],
    ['label' => 'Email Verification', 'value' => $verifiedDate ?: '<em class="vp-empty">Not verified</em>', 'icon' => 'shield-check'],
    ['label' => 'Last Updated', 'value' => $updatedDate, 'icon' => 'clock'],
];
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>User Profile</h2>
        </div>
        <div class="top-bar-right">
            <a href="<?= APP_URL ?>/index.php?url=admin/users" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:6px;font-size:.85rem;">
                <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Back to Users
            </a>
        </div>
    </div>

    <div class="page-content">

        <!-- Profile Hero -->
        <div class="vp-hero">
            <div class="vp-hero-body">
                <div class="vp-avatar-wrap">
                    <?php if (!empty($profileImage)): ?>
                        <img src="<?= $profileImageUrl ?>" alt="<?= htmlspecialchars($fullName) ?>" class="vp-avatar-img">
                    <?php else: ?>
                        <div class="vp-avatar-fallback"><?= $initials ?></div>
                    <?php endif; ?>
                </div>
                <div class="vp-hero-info">
                    <h2 class="vp-name"><?= htmlspecialchars($fullName) ?: '<em style="opacity:.5">No name</em>' ?></h2>
                    <?php if (!empty($user['username'])): ?>
                        <span class="vp-username">@<?= htmlspecialchars($user['username']) ?></span>
                    <?php endif; ?>
                    <div class="vp-badges">
                        <span class="badge <?php
                            if ($roleName === 'super_admin') echo 'badge-processing';
                            elseif (in_array($roleName, ['staff', 'inventory_incharge'])) echo 'badge-pending';
                            else echo 'badge-delivered';
                        ?>\"><?= $roleLabel ?></span>
                        <span class="badge <?= $user['is_active'] ? 'badge-delivered' : 'badge-cancelled' ?>">
                            <?= htmlspecialchars($statusLabel) ?>
                        </span>
                        <?php if (!$isCustomer): ?>
                            <span class="badge <?= $isVerified ? 'badge-delivered' : 'badge-pending' ?>">
                                <?= $isVerified ? 'Verified Account' : 'Verification Pending' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$isCustomer): ?>
                <div class="vp-hero-panel">
                    <span class="vp-hero-panel-title">Account Overview</span>
                    <div class="vp-hero-panel-grid">
                        <div class="vp-mini-stat">
                            <span class="vp-mini-stat-label">Access</span>
                            <strong class="vp-mini-stat-value"><?= htmlspecialchars($portalScopeLabel) ?></strong>
                        </div>
                        <div class="vp-mini-stat">
                            <span class="vp-mini-stat-label">Joined</span>
                            <strong class="vp-mini-stat-value"><?= htmlspecialchars($joinedDate) ?></strong>
                        </div>
                        <div class="vp-mini-stat">
                            <span class="vp-mini-stat-label">Updated</span>
                            <strong class="vp-mini-stat-value"><?= htmlspecialchars($updatedDate) ?></strong>
                        </div>
                        <div class="vp-mini-stat">
                            <span class="vp-mini-stat-label">Status</span>
                            <strong class="vp-mini-stat-value"><?= htmlspecialchars($statusLabel) ?></strong>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="vp-hero-actions">
                    <?php if ($isCustomer): ?>
                        <a href="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/toggle"
                           class="btn <?= $user['is_active'] ? 'btn-danger-outline' : 'btn-success-outline' ?>"
                           onclick="return confirm('<?= $user['is_active'] ? 'Block' : 'Unblock' ?> this customer?');"
                           style="display:inline-flex;align-items:center;gap:6px;font-size:.82rem;">
                            <i data-lucide="<?= $user['is_active'] ? 'ban' : 'shield-check' ?>" style="width:15px;height:15px;"></i>
                            <?= $user['is_active'] ? 'Block User' : 'Unblock User' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <?php if ($isCustomer): ?>
        <div class="vp-stats-row">
            <div class="vp-stat-card">
                <div class="vp-stat-number"><?= count($orders ?? []) ?></div>
                <div class="vp-stat-label">Total Orders</div>
            </div>
            <div class="vp-stat-card">
                <div class="vp-stat-number"><?= count($completedOrders) ?></div>
                <div class="vp-stat-label">Completed</div>
            </div>
            <div class="vp-stat-card">
                <div class="vp-stat-number">₱<?= number_format(array_sum(array_column($orders ?? [], 'total_amount')), 2) ?></div>
                <div class="vp-stat-label">Total Spent</div>
            </div>
            <div class="vp-stat-card">
                <div class="vp-stat-number"><?= count($reviews ?? []) ?></div>
                <div class="vp-stat-label">Reviews</div>
            </div>
        </div>
        <?php else: ?>
        <div class="vp-stats-row">
            <div class="vp-stat-card vp-stat-card--staff">
                <div class="vp-stat-icon"><i data-lucide="briefcase"></i></div>
                <div class="vp-stat-copy">
                    <div class="vp-stat-label">Role</div>
                    <div class="vp-stat-number"><?= htmlspecialchars($roleLabel) ?></div>
                    <div class="vp-stat-meta">Assigned access group</div>
                </div>
            </div>
            <div class="vp-stat-card vp-stat-card--staff">
                <div class="vp-stat-icon"><i data-lucide="activity"></i></div>
                <div class="vp-stat-copy">
                    <div class="vp-stat-label">Status</div>
                    <div class="vp-stat-number"><?= htmlspecialchars($statusLabel) ?></div>
                    <div class="vp-stat-meta"><?= $user['is_active'] ? 'Can access the portal' : 'Access currently disabled' ?></div>
                </div>
            </div>
            <div class="vp-stat-card vp-stat-card--staff">
                <div class="vp-stat-icon"><i data-lucide="badge-check"></i></div>
                <div class="vp-stat-copy">
                    <div class="vp-stat-label">Account</div>
                    <div class="vp-stat-number"><?= $isVerified ? 'Verified' : 'Pending' ?></div>
                    <div class="vp-stat-meta"><?= $isVerified ? htmlspecialchars($verifiedDate ?? 'Verified') : 'Needs email verification' ?></div>
                </div>
            </div>
            <div class="vp-stat-card vp-stat-card--staff">
                <div class="vp-stat-icon"><i data-lucide="shield"></i></div>
                <div class="vp-stat-copy">
                    <div class="vp-stat-label">Portal Access</div>
                    <div class="vp-stat-number"><?= htmlspecialchars($portalScopeLabel) ?></div>
                    <div class="vp-stat-meta"><?= htmlspecialchars($portalAccessLabel) ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Details + Orders Grid -->
        <div class="vp-content-grid <?= !$isCustomer ? 'vp-content-grid--staff' : '' ?>">
            <!-- User Details -->
            <div class="card vp-detail-card">
                <div class="vp-section-header">
                    <i data-lucide="user" style="width:18px;height:18px;"></i>
                    <h3>Personal Information</h3>
                </div>
                <?php if (!$isCustomer): ?>
                <div class="vp-inline-banner <?= $user['is_active'] ? 'vp-inline-banner--success' : 'vp-inline-banner--danger' ?>">
                    <i data-lucide="<?= $user['is_active'] ? 'shield-check' : 'shield-alert' ?>"></i>
                    <div>
                        <strong><?= $user['is_active'] ? 'Account is active' : 'Account is inactive' ?></strong>
                        <span><?= $user['is_active'] ? 'This team member can sign in and use assigned tools.' : 'This team member cannot access the portal until reactivated.' ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($isCustomer): ?>
                <div class="vp-detail-list">
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="hash"></i> User ID</span>
                        <span class="vp-detail-value">#<?= $user['id'] ?></span>
                    </div>
                    <?php if ($isCustomer): ?>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="mail"></i> Email</span>
                        <span class="vp-detail-value"><?= htmlspecialchars($user['email'] ?? '') ?: '<em class="vp-empty">Not set</em>' ?></span>
                    </div>
                    <?php else: ?>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="at-sign"></i> Username</span>
                        <span class="vp-detail-value"><?= htmlspecialchars($user['username'] ?? '') ?: '<em class="vp-empty">Not set</em>' ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="phone"></i> Phone</span>
                        <span class="vp-detail-value"><?= htmlspecialchars($user['phone'] ?? '') ?: '<em class="vp-empty">Not set</em>' ?></span>
                    </div>
                    <?php if (!empty($user['birthdate'])): ?>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="cake"></i> Birthdate</span>
                        <span class="vp-detail-value"><?= date('M d, Y', strtotime($user['birthdate'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($isCustomer): ?>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="map-pin"></i> Address</span>
                        <span class="vp-detail-value">
                            <?php
                            $addrParts = array_filter([
                                $user['address'] ?? '',
                                $user['city'] ?? '',
                                $user['state'] ?? '',
                                $user['zip_code'] ?? ''
                            ]);
                            echo $addrParts ? htmlspecialchars(implode(', ', $addrParts)) : '<em class="vp-empty">Not set</em>';
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="calendar"></i> Member Since</span>
                        <span class="vp-detail-value"><?= htmlspecialchars($joinedDate) ?></span>
                    </div>
                    <?php if (!$isCustomer): ?>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="shield-check"></i> Email Verified</span>
                        <span class="vp-detail-value"><?= !empty($user['email_verified_at']) ? date('M d, Y', strtotime($user['email_verified_at'])) : '<em class="vp-empty">Not verified</em>' ?></span>
                    </div>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="clock"></i> Last Updated</span>
                        <span class="vp-detail-value"><?= !empty($user['updated_at']) ? date('M d, Y', strtotime($user['updated_at'])) : '<em class="vp-empty">N/A</em>' ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="vp-info-grid">
                    <?php foreach ($staffProfileFields as $field): ?>
                    <div class="vp-info-card">
                        <span class="vp-info-label"><i data-lucide="<?= htmlspecialchars($field['icon']) ?>"></i><?= htmlspecialchars($field['label']) ?></span>
                        <div class="vp-info-value"><?= $field['value'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($isCustomer): ?>
            <!-- Recent Orders (customers only) -->
            <div class="card vp-orders-card">
                <div class="vp-section-header">
                    <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
                    <h3>Recent Orders</h3>
                    <span class="tab-count"><?= count($orders ?? []) ?></span>
                </div>
                <?php if (!empty($orders)): ?>
                    <div class="vp-order-list">
                        <?php foreach (array_slice($orders, 0, 8) as $order): ?>
                        <div class="vp-order-item">
                            <div class="vp-order-main">
                                <span class="vp-order-number"><?= htmlspecialchars($order['order_number']) ?></span>
                                <span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                            </div>
                            <div class="vp-order-meta">
                                <span><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                <span class="vp-order-amount">₱<?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($orders) > 8): ?>
                        <div style="text-align:center; padding:12px 0 4px; border-top:1px solid var(--border,#e8ecf1); margin-top:4px;">
                            <span style="font-size:.82rem; color:var(--steel); font-weight:600;">+ <?= count($orders) - 8 ?> more orders</span>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="vp-empty-state">
                        <i data-lucide="inbox" style="width:32px;height:32px;"></i>
                        <p>No orders yet</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <!-- Account Info (staff/admin/inventory) -->
            <div class="vp-side-stack">
                <div class="card vp-orders-card vp-admin-card">
                    <div class="vp-section-header">
                        <i data-lucide="shield" style="width:18px;height:18px;"></i>
                        <h3>Access & Permissions</h3>
                    </div>
                    <div class="vp-detail-list vp-detail-list--compact">
                        <div class="vp-detail-item">
                            <span class="vp-detail-label"><i data-lucide="briefcase"></i> Role</span>
                            <span class="vp-detail-value"><?= htmlspecialchars($roleLabel) ?></span>
                        </div>
                        <div class="vp-detail-item">
                            <span class="vp-detail-label"><i data-lucide="lock"></i> Portal Access</span>
                            <span class="vp-detail-value"><?= htmlspecialchars($portalScopeLabel) ?></span>
                        </div>
                        <div class="vp-detail-item">
                            <span class="vp-detail-label"><i data-lucide="sparkles"></i> Access Scope</span>
                            <span class="vp-detail-value"><?= htmlspecialchars($portalAccessLabel) ?></span>
                        </div>
                        <div class="vp-detail-item">
                            <span class="vp-detail-label"><i data-lucide="calendar-check"></i> Account Verified</span>
                            <span class="vp-detail-value"><?= $isVerified ? '<span class="badge badge-delivered">Yes</span>' : '<span class="badge badge-cancelled">No</span>' ?></span>
                        </div>
                    </div>
                </div>

                <div class="card vp-admin-card vp-admin-card--actions">
                    <div class="vp-section-header">
                        <i data-lucide="settings-2" style="width:18px;height:18px;"></i>
                        <h3>Account Actions</h3>
                    </div>
                    <div class="vp-action-summary">
                        <div class="vp-action-summary-item">
                            <span class="vp-action-summary-label">Current status</span>
                            <span class="badge <?= $user['is_active'] ? 'badge-delivered' : 'badge-cancelled' ?>"><?= htmlspecialchars($statusLabel) ?></span>
                        </div>
                        <div class="vp-action-summary-item">
                            <span class="vp-action-summary-label">Verification</span>
                            <span class="badge <?= $isVerified ? 'badge-delivered' : 'badge-pending' ?>"><?= $isVerified ? 'Verified' : 'Pending' ?></span>
                        </div>
                    </div>
                    <div class="vp-action-note">
                        <i data-lucide="info"></i>
                        <p>Use these controls to manage access for this staff or admin account.</p>
                    </div>
                    <div class="vp-action-buttons">
                        <a href="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/toggle"
                           class="btn <?= $user['is_active'] ? 'btn-danger-outline' : 'btn-success-outline' ?>"
                           onclick="return confirm('<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> this user?');"
                           style="display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:.82rem;">
                            <i data-lucide="<?= $user['is_active'] ? 'user-x' : 'user-check' ?>" style="width:15px;height:15px;"></i>
                            <?= $user['is_active'] ? 'Deactivate Account' : 'Activate Account' ?>
                        </a>
                        <form action="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/delete" method="POST" style="margin:0;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger-outline" onclick="return confirm('Permanently delete this user?');" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:.82rem;width:100%;">
                                <i data-lucide="trash-2" style="width:15px;height:15px;"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Reviews Section (customers only) -->
        <?php if ($isCustomer && !empty($reviews)): ?>
        <div class="card" style="margin-top:20px;">
            <div class="vp-section-header">
                <i data-lucide="star" style="width:18px;height:18px;"></i>
                <h3>Reviews</h3>
                <span class="tab-count"><?= count($reviews) ?></span>
            </div>
            <div class="vp-review-grid">
                <?php foreach ($reviews as $review): ?>
                <div class="vp-review-card">
                    <div class="vp-review-top">
                        <?php if (!empty($review['product_image'])): ?>
                            <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($review['product_image']) ?>" alt="" class="vp-review-product-img">
                        <?php else: ?>
                            <div class="vp-review-product-img vp-review-product-placeholder">
                                <i data-lucide="package" style="width:18px;height:18px;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="vp-review-product-info">
                            <span class="vp-review-product-name"><?= htmlspecialchars($review['product_name'] ?? 'Unknown') ?></span>
                            <span class="vp-review-date"><?= date('M d, Y', strtotime($review['created_at'])) ?></span>
                        </div>
                        <div class="vp-review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?= $i <= ($review['rating'] ?? 0) ? 'star-filled' : 'star-empty' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php if (!empty($review['comment'])): ?>
                        <p class="vp-review-comment"><?= htmlspecialchars($review['comment']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($orders) && empty($reviews)): ?>
        <div class="card" style="margin-top:20px; text-align:center; padding:2.5rem;">
            <i data-lucide="inbox" style="width:40px;height:40px;color:var(--steel);margin-bottom:.75rem;"></i>
            <p style="color:var(--steel); margin:0;">This user has no orders or reviews yet.</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<style>
/* ===== Profile Hero ===== */
.vp-hero {
    border-radius: var(--radius-lg, 14px);
    overflow: hidden;
    background:
        radial-gradient(circle at top right, rgba(249,115,22,.12), transparent 34%),
        linear-gradient(145deg, rgba(11,61,145,.045), rgba(255,255,255,.96));
    border: 1px solid rgba(15, 23, 42, .08);
    box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
    margin-bottom: 20px;
}
.vp-hero-body {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px 28px;
    flex-wrap: wrap;
}
.vp-avatar-wrap {
    flex-shrink: 0;
}
.vp-avatar-img {
    width: 88px;
    height: 88px;
    border-radius: 14px;
    object-fit: cover;
    border: 4px solid var(--white, #fff);
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    display: block;
}
.vp-avatar-fallback {
    width: 88px;
    height: 88px;
    border-radius: 14px;
    background: var(--primary, #0B3D91);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: 1px;
    border: 4px solid var(--white, #fff);
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
}
.vp-hero-info {
    flex: 1;
    min-width: 200px;
    padding-bottom: 4px;
}
.vp-hero-panel {
    min-width: 280px;
    max-width: 360px;
    padding: 16px 18px;
    border-radius: 16px;
    background: rgba(255,255,255,.9);
    border: 1px solid rgba(148, 163, 184, .22);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
}
.vp-hero-panel-title {
    display: block;
    margin-bottom: 12px;
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--steel, #6c7a8d);
}
.vp-hero-panel-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}
.vp-mini-stat {
    padding: 10px 12px;
    border-radius: 12px;
    background: linear-gradient(180deg, rgba(248,250,252,.95), rgba(255,255,255,.9));
    border: 1px solid rgba(226, 232, 240, .95);
}
.vp-mini-stat-label {
    display: block;
    margin-bottom: 4px;
    font-size: .72rem;
    font-weight: 700;
    color: var(--steel, #6c7a8d);
    text-transform: uppercase;
    letter-spacing: .05em;
}
.vp-mini-stat-value {
    display: block;
    font-size: .85rem;
    line-height: 1.35;
    color: var(--charcoal, #1B2A4A);
}
.vp-name {
    margin: 0 0 2px;
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--charcoal, #1B2A4A);
}
.vp-username {
    display: block;
    font-size: .85rem;
    color: var(--steel, #6c7a8d);
    font-weight: 600;
    margin-bottom: 8px;
}
.vp-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.vp-hero-actions {
    flex-shrink: 0;
    padding-bottom: 4px;
}
.btn-danger-outline {
    border: 1px solid #fca5a5;
    color: #dc2626;
    background: #fef2f2;
    border-radius: var(--radius-sm, 6px);
    padding: 7px 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
}
.btn-danger-outline:hover { background: #fee2e2; border-color: #f87171; }
.btn-success-outline {
    border: 1px solid #86efac;
    color: #16a34a;
    background: #f0fdf4;
    border-radius: var(--radius-sm, 6px);
    padding: 7px 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
}
.btn-success-outline:hover { background: #dcfce7; border-color: #4ade80; }

/* ===== Stat Cards Row ===== */
.vp-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
.vp-stat-card {
    background: var(--white, #fff);
    border: 1px solid var(--border, #e8ecf1);
    border-radius: var(--radius-lg, 14px);
    padding: 20px;
    text-align: center;
}
.vp-stat-card--staff {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    text-align: left;
    padding: 18px;
    background: linear-gradient(180deg, #ffffff, #f8fafc);
    box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
}
.vp-stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(249, 115, 22, .12);
    color: var(--accent, #F97316);
    flex-shrink: 0;
}
.vp-stat-icon i,
.vp-stat-icon svg {
    width: 20px;
    height: 20px;
}
.vp-stat-copy {
    min-width: 0;
}
.vp-stat-number {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--charcoal, #1B2A4A);
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}
.vp-stat-label {
    font-size: .75rem;
    font-weight: 600;
    color: var(--steel, #6c7a8d);
    text-transform: uppercase;
    letter-spacing: .3px;
}
.vp-stat-meta {
    margin-top: 6px;
    font-size: .78rem;
    color: var(--steel, #6c7a8d);
    font-weight: 600;
}

/* ===== Content Grid ===== */
.vp-content-grid {
    display: grid;
    grid-template-columns: .9fr 1.1fr;
    gap: 20px;
    align-items: start;
}
.vp-content-grid--staff {
    grid-template-columns: 1.15fr .85fr;
}
.vp-section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border, #e8ecf1);
}
.vp-section-header i, .vp-section-header svg { color: var(--accent, #F97316); }
.vp-section-header h3 {
    margin: 0;
    font-size: .95rem;
    font-weight: 700;
    flex: 1;
}
.vp-inline-banner {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 14px 16px;
    margin-bottom: 18px;
    border-radius: 14px;
    border: 1px solid transparent;
}
.vp-inline-banner i,
.vp-inline-banner svg {
    width: 18px;
    height: 18px;
    margin-top: 1px;
    flex-shrink: 0;
}
.vp-inline-banner strong {
    display: block;
    margin-bottom: 2px;
    font-size: .88rem;
    color: var(--charcoal, #1B2A4A);
}
.vp-inline-banner span {
    display: block;
    font-size: .8rem;
    line-height: 1.45;
    color: var(--steel, #6c7a8d);
}
.vp-inline-banner--success {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #16a34a;
}
.vp-inline-banner--danger {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

/* ===== Detail List ===== */
.vp-detail-list { display: flex; flex-direction: column; }
.vp-detail-list--compact .vp-detail-item {
    padding: 12px 0;
}
.vp-detail-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 11px 0;
    border-bottom: 1px solid var(--surface, #F0F2F5);
}
.vp-detail-item:last-child { border-bottom: none; }
.vp-detail-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--steel, #6c7a8d);
    font-size: .84rem;
    font-weight: 600;
    white-space: nowrap;
}
.vp-detail-label i, .vp-detail-label svg { width: 15px; height: 15px; opacity: .6; }
.vp-detail-value {
    font-weight: 600;
    color: var(--charcoal, #1B2A4A);
    font-size: .88rem;
    text-align: right;
    word-break: break-word;
}
.vp-empty { opacity: .35; font-weight: 500; }
.vp-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}
.vp-info-card {
    padding: 16px;
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff, #f8fafc);
    border: 1px solid rgba(226, 232, 240, .95);
    box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
}
.vp-info-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: .74rem;
    color: var(--steel, #6c7a8d);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.vp-info-label i,
.vp-info-label svg {
    width: 15px;
    height: 15px;
    color: var(--accent, #F97316);
}
.vp-info-value {
    margin-top: 10px;
    font-size: .96rem;
    font-weight: 700;
    line-height: 1.4;
    color: var(--charcoal, #1B2A4A);
    word-break: break-word;
}

.vp-side-stack {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.vp-admin-card {
    background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.96));
}
.vp-admin-card--actions {
    border: 1px solid rgba(248, 113, 113, .14);
}
.vp-action-summary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}
.vp-action-summary-item {
    padding: 14px;
    border-radius: 14px;
    background: var(--surface, #F0F2F5);
    border: 1px solid var(--border, #e8ecf1);
}
.vp-action-summary-label {
    display: block;
    margin-bottom: 10px;
    font-size: .75rem;
    font-weight: 700;
    color: var(--steel, #6c7a8d);
    text-transform: uppercase;
    letter-spacing: .05em;
}
.vp-action-note {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 14px 15px;
    margin-bottom: 16px;
    border-radius: 14px;
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fdba74;
}
.vp-action-note i,
.vp-action-note svg {
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    margin-top: 2px;
}
.vp-action-note p {
    margin: 0;
    font-size: .82rem;
    line-height: 1.5;
}
.vp-action-buttons {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

/* ===== Order List ===== */
.vp-order-list { display: flex; flex-direction: column; gap: 2px; }
.vp-order-item {
    padding: 10px 12px;
    border-radius: var(--radius-sm, 6px);
    transition: background .15s;
}
.vp-order-item:hover { background: var(--surface, #F0F2F5); }
.vp-order-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 3px;
}
.vp-order-number {
    font-weight: 700;
    font-size: .85rem;
    color: var(--charcoal, #1B2A4A);
}
.vp-order-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: .78rem;
    color: var(--steel, #6c7a8d);
}
.vp-order-amount {
    font-weight: 700;
    color: var(--charcoal, #1B2A4A);
}
.vp-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 1rem;
    color: var(--steel, #6c7a8d);
}
.vp-empty-state p {
    margin: 8px 0 0;
    font-size: .85rem;
    font-weight: 600;
}

/* ===== Review Grid ===== */
.vp-review-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
}
.vp-review-card {
    background: var(--surface, #F0F2F5);
    border-radius: var(--radius-md, 10px);
    padding: 14px 16px;
}
.vp-review-top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.vp-review-product-img {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border, #e8ecf1);
    flex-shrink: 0;
}
.vp-review-product-placeholder {
    background: var(--white, #fff);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--steel, #6c7a8d);
}
.vp-review-product-info {
    flex: 1;
    min-width: 0;
}
.vp-review-product-name {
    display: block;
    font-weight: 700;
    font-size: .84rem;
    color: var(--charcoal, #1B2A4A);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.vp-review-date {
    font-size: .72rem;
    color: var(--steel, #6c7a8d);
    font-weight: 600;
}
.vp-review-stars {
    flex-shrink: 0;
    font-size: .85rem;
}
.star-filled { color: #f59e0b; }
.star-empty { color: #d1d5db; }
.vp-review-comment {
    margin: 0;
    font-size: .82rem;
    color: var(--text-secondary, #4b5563);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ===== Responsive ===== */
@media (max-width: 900px) {
    .vp-stats-row { grid-template-columns: repeat(2, 1fr); }
    .vp-content-grid { grid-template-columns: 1fr; }
    .vp-hero-panel { max-width: none; width: 100%; }
}
@media (max-width: 600px) {
    .vp-stats-row { grid-template-columns: 1fr; }
    .vp-hero-body { flex-direction: column; align-items: flex-start; gap: 12px; }
    .vp-hero-actions { width: 100%; }
    .vp-hero-panel-grid,
    .vp-info-grid,
    .vp-action-summary { grid-template-columns: 1fr; }
}
</style>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
