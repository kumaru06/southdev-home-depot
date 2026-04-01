<?php
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

// Helper: map log action to icon + colour class
function _dash_activity_icon(string $action): array {
    if (str_contains($action, 'login') || str_contains($action, 'logout'))
        return ['log-in',      'ab-login'];
    if (str_contains($action, 'order'))
        return ['shopping-bag', 'ab-order'];
    if (str_contains($action, 'user') || str_contains($action, 'register'))
        return ['user',         'ab-user'];
    if (str_contains($action, 'product') || str_contains($action, 'category'))
        return ['package',      'ab-product'];
    if (str_contains($action, 'stock') || str_contains($action, 'inventory'))
        return ['layers',       'ab-stock'];
    return ['activity', 'ab-default'];
}

// Friendly action label
function _dash_action_label(string $raw): string {
    return ucwords(str_replace('_', ' ', $raw));
}
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>Dashboard</h2>
        </div>
    </div>

    <div class="page-content">

        <!-- ========== Stat Cards ========== -->
        <div class="stat-cards">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Revenue</span>
                    <span class="stat-value">₱<?= number_format($totalSales ?? 0, 2) ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="trending-up"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Orders</span>
                    <span class="stat-value"><?= $totalOrders ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="shopping-cart"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Pending Orders</span>
                    <span class="stat-value"><?= $pendingOrders ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="clock"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Customers</span>
                    <span class="stat-value"><?= $totalCustomers ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="users"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Products</span>
                    <span class="stat-value"><?= $totalProducts ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="package"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Pending Cancellations</span>
                    <span class="stat-value"><?= $pendingCancels ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="x-circle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Damaged Products</span>
                    <span class="stat-value"><?= $totalDamaged ?? 0 ?></span>
                </div>
                <div class="stat-icon" style="background:var(--danger-bg, #FEE2E2);color:var(--danger, #DC2626);"><i data-lucide="alert-octagon"></i></div>
            </div>
        </div>

        <!-- ========== Charts ========== -->
        <div class="chart-grid">
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Sales Overview</h3>
                    <div>
                        <button id="sales-view-monthly" class="btn btn-sm btn-outline">MONTHLY</button>
                        <button id="sales-view-daily" class="btn btn-sm btn-accent">DAILY</button>
                    </div>
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
                    <div class="category-fallback" style="display:none;padding:12px;color:var(--steel);">
                        <strong>Category totals</strong>
                        <ul style="margin:8px 0 0;padding-left:18px;">
                            <?php if (!empty($catLabels) && !empty($catData) && count($catLabels) === count($catData)): ?>
                                <?php for ($i = 0; $i < count($catLabels); $i++): ?>
                                    <li><?= htmlspecialchars($catLabels[$i]) ?> — ₱<?= number_format($catData[$i], 2) ?></li>
                                <?php endfor; ?>
                            <?php else: ?>
                                <li>No category data available.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== Recent Orders + Activity ========== -->
        <div class="dashboard-grid-orders">
            <!-- Recent Orders -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="shopping-bag"></i> Recent Orders</h3>
                    <a href="<?= APP_URL ?>/index.php?url=admin/orders" class="view-all-link">
                        View All <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                    </a>
                </div>
                <div class="dash-card-body">
                    <?php if (!empty($recentOrders)): ?>
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><a href="<?= APP_URL ?>/index.php?url=admin/orders/<?= $order['id'] ?>"><?= htmlspecialchars($order['order_number']) ?></a></td>
                                        <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                        <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="dash-empty">
                            <i data-lucide="inbox"></i>
                            <p>No recent orders</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="activity"></i> Recent Activity</h3>
                </div>
                <div class="dash-card-body">
                    <?php if (!empty($recentLogs)): ?>
                        <div class="activity-scroll">
                            <div class="activity-list">
                                <?php foreach ($recentLogs as $log):
                                    [$icon, $colorCls] = _dash_activity_icon($log['action']);
                                ?>
                                    <div class="activity-row">
                                        <div class="activity-badge <?= $colorCls ?>">
                                            <i data-lucide="<?= $icon ?>"></i>
                                        </div>
                                        <div class="activity-body">
                                            <p class="activity-action"><?= htmlspecialchars(_dash_action_label($log['action'])) ?></p>
                                            <span class="activity-who">
                                                <?= htmlspecialchars(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?>
                                                <span class="sep"></span>
                                                <?= date('M d, g:i A', strtotime($log['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="dash-empty">
                            <i data-lucide="clock"></i>
                            <p>No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ========== Low Stock + Top Products ========== -->
        <div class="dashboard-grid-2">
            <!-- Low Stock Alert -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="alert-triangle" style="color:var(--danger);"></i> Low Stock Alert</h3>
                </div>
                <div class="dash-card-body">
                    <?php if (!empty($lowStock)): ?>
                        <table class="dash-table">
                            <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                            <tbody>
                                <?php foreach ($lowStock as $item):
                                    $qty = (int)$item['quantity'];
                                    $cls = $qty <= 5 ? 'critical' : 'warning';
                                ?>
                                    <tr class="row-warning">
                                        <td><?= htmlspecialchars($item['product_name'] ?? $item['name']) ?></td>
                                        <td><span class="stock-badge <?= $cls ?>"><?= $qty ?> left</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="dash-empty">
                            <i data-lucide="check-circle" style="color:var(--success);opacity:.6;"></i>
                            <p>All stock levels healthy</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Selling Products -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="bar-chart-2"></i> Top Selling Products</h3>
                </div>
                <div class="dash-card-body">
                    <?php if (!empty($topProducts)): ?>
                        <table class="dash-table">
                            <thead><tr><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead>
                            <tbody>
                                <?php foreach ($topProducts as $tp): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tp['name']) ?></td>
                                        <td><?= $tp['total_sold'] ?></td>
                                        <td>₱<?= number_format($tp['total_revenue'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="dash-empty">
                            <i data-lucide="bar-chart"></i>
                            <p>No sales data yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ========== Damaged Products ========== -->
        <?php if (!empty($recentDamaged)): ?>
        <div class="dashboard-grid-2" style="margin-top: 0;">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="alert-octagon" style="color:var(--danger);"></i> Damaged Products</h3>
                    <a href="<?= APP_URL ?>/index.php?url=admin/inventory/damaged" class="view-all-link">
                        View All <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                    </a>
                </div>
                <div class="dash-card-body">
                    <table class="dash-table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Order</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentDamaged as $dmg):
                                $dmgClass = match($dmg['status']) {
                                    'received'    => 'badge-pending',
                                    'inspected'   => 'badge-processing',
                                    'written_off' => 'badge-cancelled',
                                    'repaired'    => 'badge-delivered',
                                    default       => 'badge-pending'
                                };
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($dmg['product_name']) ?></td>
                                    <td><strong><?= $dmg['quantity'] ?></strong></td>
                                    <td><?= htmlspecialchars($dmg['order_number']) ?></td>
                                    <td><span class="badge <?= $dmgClass ?>"><?= ucfirst(str_replace('_', ' ', $dmg['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.DASHBOARD_CHARTS = {
        sales: {
            id: 'salesChart',
            // monthly (default)
            monthly: {
                labels: <?= json_encode($chartLabels ?? []) ?>,
                data: <?= json_encode($chartData ?? []) ?>
            },
            // daily (last 30 days)
            daily: {
                labels: <?= json_encode($dailyLabels ?? []) ?>,
                data: <?= json_encode($dailyData ?? []) ?>,
                rawDates: <?= json_encode($dailyRawDates ?? []) ?>
            }
        },
        category: {
            id: 'categoryChart',
            labels: <?= json_encode($catLabels ?? []) ?>,
            data: <?= json_encode($catData ?? []) ?>
        }
    };

    // Wire Monthly/Daily toggle buttons
    document.addEventListener('DOMContentLoaded', function () {
        function setView(view) {
            window._currentSalesView = view;
            // Use the new switchSalesView which properly handles daily vs monthly
            if (typeof window.switchSalesView === 'function') {
                window.switchSalesView(view);
            }
            // Toggle button styles
            var monthlyBtn = document.getElementById('sales-view-monthly');
            var dailyBtn = document.getElementById('sales-view-daily');
            if (monthlyBtn && dailyBtn) {
                monthlyBtn.classList.toggle('btn-accent', view === 'monthly');
                monthlyBtn.classList.toggle('btn-outline', view !== 'monthly');
                dailyBtn.classList.toggle('btn-accent', view === 'daily');
                dailyBtn.classList.toggle('btn-outline', view !== 'daily');
            }
        }

        document.getElementById('sales-view-monthly').addEventListener('click', function () { setView('monthly'); });
        document.getElementById('sales-view-daily').addEventListener('click', function () { setView('daily'); });
        // Default to monthly
        setView('monthly');
    });
</script>
    <script>
        // If Chart.js isn't available, reveal the textual fallback so users see category totals.
        (function () {
            try {
                if (typeof Chart === 'undefined') {
                    var fb = document.querySelector('.category-fallback');
                    var cvs = document.getElementById('categoryChart');
                    if (fb) fb.style.display = 'block';
                    if (cvs) cvs.style.display = 'none';
                }
            } catch (e) {}
        })();
    </script>
<script>
    // If CDN fails, load local Chart.js fallback from assets/vendor
    (function () {
        if (typeof Chart === 'undefined') {
            var s = document.createElement('script');
            s.src = '/assets/vendor/chartjs/chart.min.js';
            s.defer = true;
            s.onload = function () { try { console.info('Loaded local Chart.js fallback'); } catch (e) {} };
            document.head.appendChild(s);
        }
    })();
</script>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
