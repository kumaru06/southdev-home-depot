<?php
$pageTitle = 'My Account';
$extraCss = ['customer.css'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="page-heading">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Customer') ?>!</h1>
        <p style="color:var(--steel); margin-top:.25rem;">Here's a summary of your account activity.</p>
    </div>

    <!-- Stat Cards -->
    <div class="stat-cards" style="margin-bottom:2rem;">
        <div class="stat-card">
            <div class="stat-icon"><i data-lucide="shopping-bag"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $totalOrders ?? 0 ?></span>
                <span class="stat-label">Total Orders</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i data-lucide="clock"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $pendingOrders ?? 0 ?></span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i data-lucide="check-circle"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $deliveredOrders ?? 0 ?></span>
                <span class="stat-label">Delivered</span>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
            <h3 style="margin:0; font-size:1.05rem; font-weight:600;">
                <i data-lucide="clipboard-list" style="width:18px;height:18px;vertical-align:middle;color:var(--accent);"></i>
                Recent Orders
            </h3>
            <a href="<?= APP_URL ?>/index.php?url=orders" class="btn btn-sm btn-outline" style="font-size:.8rem;">View All</a>
        </div>
        <?php if (!empty($recentOrders)): ?>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td data-label="Order #"><?= htmlspecialchars($order['order_number']) ?></td>
                                <td data-label="Date"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td data-label="Total"><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                                <td data-label="Status"><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                <td data-label="Action">
                                    <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>" class="action-btn view" title="View Details">
                                        <i data-lucide="eye" style="width:15px;height:15px;"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="text-align:center; padding:3rem 1rem;">
                <i data-lucide="package" style="width:48px;height:48px;color:var(--steel);margin-bottom:1rem;"></i>
                <p style="color:var(--steel); margin-bottom:1rem;">You haven't placed any orders yet.</p>
                <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">
                    <i data-lucide="shopping-bag" style="width:16px;height:16px;"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
