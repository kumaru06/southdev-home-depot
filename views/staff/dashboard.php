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
                <div class="stat-info">
                    <span class="stat-label">Total Sales</span>
                    <span class="stat-value">₱<?= number_format($totalSales ?? 0, 2) ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="trending-up"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Orders</span>
                    <span class="stat-value"><?= $totalOrders ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="shopping-bag"></i></div>
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
                    <span class="stat-label">Cancel Requests</span>
                    <span class="stat-value"><?= $pendingCancels ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="alert-triangle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Damaged Products</span>
                    <span class="stat-value"><?= $totalDamaged ?? 0 ?></span>
                </div>
                <div class="stat-icon" style="background:var(--danger-bg, #FEE2E2);color:var(--danger, #DC2626);"><i data-lucide="alert-octagon"></i></div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="chart-grid">
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Sales</h3>
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

        <div class="dashboard-grid-2">
            <!-- Recent Orders -->
            <div class="card">
                <h3><i data-lucide="package"></i> Profile</h3>
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

            <!-- Activity Feed (super admin only) -->
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

        <!-- Damaged Products -->
        <?php if (!empty($recentDamaged)): ?>
        <div class="dashboard-grid-2" style="margin-top: 0;">
            <div class="card">
                <h3><i data-lucide="alert-octagon" style="color:var(--danger, #DC2626);"></i> Damaged Products</h3>
                <?php
                    $dmgUrl = ($_SESSION['role_id'] == ROLE_INVENTORY)
                        ? APP_URL . '/index.php?url=inventory/stock/damaged'
                        : APP_URL . '/index.php?url=staff/inventory/damaged';
                ?>
                <div class="data-table-wrap">
                    <table class="data-table">
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
                <div style="margin-top:12px;text-align:right;">
                    <a href="<?= $dmgUrl ?>" class="btn btn-outline btn-sm">View All Damaged Products →</a>
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
            monthly: {
                labels: <?= json_encode($chartLabels ?? []) ?>,
                data: <?= json_encode($chartData ?? []) ?>
            },
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

    // Wire Monthly/Daily toggle for staff dashboard
    document.addEventListener('DOMContentLoaded', function () {
        function setView(view) {
            window._currentSalesView = view;
            if (typeof window.switchSalesView === 'function') {
                window.switchSalesView(view);
            }
            var monthlyBtn = document.getElementById('sales-view-monthly');
            var dailyBtn = document.getElementById('sales-view-daily');
            if (monthlyBtn && dailyBtn) {
                monthlyBtn.classList.toggle('btn-accent', view === 'monthly');
                monthlyBtn.classList.toggle('btn-outline', view !== 'monthly');
                dailyBtn.classList.toggle('btn-accent', view === 'daily');
                dailyBtn.classList.toggle('btn-outline', view !== 'daily');
            }
        }

        var m = document.getElementById('sales-view-monthly');
        var d = document.getElementById('sales-view-daily');
        if (m) m.addEventListener('click', function () { setView('monthly'); });
        if (d) d.addEventListener('click', function () { setView('daily'); });
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
