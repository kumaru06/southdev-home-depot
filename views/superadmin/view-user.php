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
                            if ($user['role_name'] === 'super_admin') echo 'badge-processing';
                            elseif (in_array($user['role_name'], ['staff', 'inventory_incharge'])) echo 'badge-pending';
                            else echo 'badge-delivered';
                        ?>"><?= ucfirst(str_replace('_', ' ', $user['role_name'])) ?></span>
                        <span class="badge <?= $user['is_active'] ? 'badge-delivered' : 'badge-cancelled' ?>">
                            <?php if (!$user['is_active'] && $isCustomer): ?>Blocked<?php else: ?><?= $user['is_active'] ? 'Active' : 'Inactive' ?><?php endif; ?>
                        </span>
                    </div>
                </div>
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

        <!-- Details + Orders Grid -->
        <div class="vp-content-grid">
            <!-- User Details -->
            <div class="card vp-detail-card">
                <div class="vp-section-header">
                    <i data-lucide="user" style="width:18px;height:18px;"></i>
                    <h3>Personal Information</h3>
                </div>
                <div class="vp-detail-list">
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="hash"></i> User ID</span>
                        <span class="vp-detail-value">#<?= $user['id'] ?></span>
                    </div>
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="mail"></i> Email</span>
                        <span class="vp-detail-value"><?= htmlspecialchars($user['email'] ?? '') ?: '<em class="vp-empty">Not set</em>' ?></span>
                    </div>
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
                    <div class="vp-detail-item">
                        <span class="vp-detail-label"><i data-lucide="calendar"></i> Member Since</span>
                        <span class="vp-detail-value"><?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
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
        </div>

        <!-- Reviews Section -->
        <?php if (!empty($reviews)): ?>
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
    background: var(--white, #fff);
    border: 1px solid var(--border, #e8ecf1);
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

/* ===== Content Grid ===== */
.vp-content-grid {
    display: grid;
    grid-template-columns: .9fr 1.1fr;
    gap: 20px;
    align-items: start;
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

/* ===== Detail List ===== */
.vp-detail-list { display: flex; flex-direction: column; }
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
}
@media (max-width: 600px) {
    .vp-stats-row { grid-template-columns: 1fr; }
    .vp-hero-body { flex-direction: column; align-items: flex-start; gap: 12px; }
    .vp-hero-actions { width: 100%; }
}
</style>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
