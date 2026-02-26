<?php
/* $pageTitle, $extraCss, $extraJs, $isAdmin set by DashboardController */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
        <div class="top-bar-right">
            <span class="top-bar-greeting">Welcome, <?= htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['user_name'] ?? '') ?></span>
        </div>
    </div>

    <div class="page-content">
        <!-- Stat Cards -->
        <div class="stat-cards">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-info">
                        <span class="stat-label">Total Sales</span>
                        <span class="stat-value">₱<?= number_format($totalSales ?? 0, 2) ?></span>
                    </div>
                    <div class="stat-icon"><i data-lucide="trending-up"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-info">
                        <span class="stat-label">Total Orders</span>
                        <span class="stat-value animated-counter" data-target="<?= $totalOrders ?? 0 ?>"><?= $totalOrders ?? 0 ?></span>
                    </div>
                    <div class="stat-icon"><i data-lucide="shopping-bag"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-info">
                        <span class="stat-label">Pending Orders</span>
                        <span class="stat-value animated-counter" data-target="<?= $pendingOrders ?? 0 ?>"><?= $pendingOrders ?? 0 ?></span>
                    </div>
                    <div class="stat-icon"><i data-lucide="clock"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-info">
                        <span class="stat-label">Cancel Requests</span>
                        <span class="stat-value animated-counter" data-target="<?= $pendingCancels ?? 0 ?>"><?= $pendingCancels ?? 0 ?></span>
                    </div>
                    <div class="stat-icon"><i data-lucide="alert-triangle"></i></div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="chart-grid">
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Sales Overview</h3>
                </div>
                <div class="chart-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Revenue by Category</h3>
                </div>
                <div class="chart-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="dashboard-grid-2">
            <!-- Recent Orders -->
            <div class="card">
                <h3><i data-lucide="package"></i> Recent Orders</h3>
                <?php if (!empty($recentOrders)): ?>
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order['order_number']) ?></td>
                                        <td><?= htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?></td>
                                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                        <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                        <td><a href="<?= APP_URL ?>/index.php?url=staff/orders/<?= $order['id'] ?>" class="action-btn view">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent orders.</p>
                <?php endif; ?>
            </div>

            <!-- Activity Feed -->
            <div class="card">
                <h3><i data-lucide="activity"></i> Recent Activity</h3>
                <?php if (!empty($recentLogs)): ?>
                    <div class="activity-feed">
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="activity-item">
                                <div class="activity-icon activity-icon-<?= strpos($log['action'], 'ORDER') !== false ? 'order' : (strpos($log['action'], 'USER') !== false ? 'user' : (strpos($log['action'], 'PRODUCT') !== false ? 'product' : 'payment')) ?>">
                                    <i data-lucide="<?= strpos($log['action'], 'ORDER') !== false ? 'package' : (strpos($log['action'], 'USER') !== false ? 'user' : (strpos($log['action'], 'PRODUCT') !== false ? 'box' : 'activity')) ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p><?= htmlspecialchars($log['description']) ?></p>
                                    <span class="activity-time"><?= date('M d, h:i A', strtotime($log['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent activity.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock & Top Products -->
        <div class="dashboard-grid-2">
            <?php if (!empty($lowStock)): ?>
                <div class="card low-stock-alert">
                    <h3><i data-lucide="alert-circle"></i> Low Stock Alerts</h3>
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr><th>Product</th><th>Stock</th><th>Reorder</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($lowStock, 0, 5) as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><span class="badge badge-cancelled"><?= $item['quantity'] ?></span></td>
                                        <td><?= $item['reorder_level'] ?? 10 ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($topProducts)): ?>
                <div class="card">
                    <h3><i data-lucide="award"></i> Top Selling Products</h3>
                    <div class="top-products">
                        <?php foreach ($topProducts as $i => $tp): ?>
                            <div class="top-product-row">
                                <span class="rank">#<?= $i + 1 ?></span>
                                <span class="tp-name"><?= htmlspecialchars($tp['name']) ?></span>
                                <span class="tp-sold"><?= $tp['total_sold'] ?> sold</span>
                                <span class="tp-revenue">₱<?= number_format($tp['total_revenue'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.DASHBOARD_CHARTS = {
        sales: {
            id: 'salesChart',
            labels: <?= json_encode($chartLabels ?? []) ?>,
            data: <?= json_encode($chartData ?? []) ?>
        },
        category: {
            id: 'categoryChart',
            labels: <?= json_encode($catLabels ?? []) ?>,
            data: <?= json_encode($catData ?? []) ?>
        }
    };
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
