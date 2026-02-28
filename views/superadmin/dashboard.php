<?php
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>Dashboard</h2>
        </div>
    </div>

    <div class="page-content">

        <!-- Stat Cards -->
        <div class="stat-cards">
            <div class="stat-card">
                <div class="stat-icon"><i data-lucide="peso-sign"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-count="<?= $totalSales ?? 0 ?>">₱<?= number_format($totalSales ?? 0, 2) ?></span>
                    <span class="stat-label">Total Revenue</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i data-lucide="shopping-cart"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-count="<?= $totalOrders ?? 0 ?>"><?= $totalOrders ?? 0 ?></span>
                    <span class="stat-label">Total Orders</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i data-lucide="clock"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-count="<?= $pendingOrders ?? 0 ?>"><?= $pendingOrders ?? 0 ?></span>
                    <span class="stat-label">Pending Orders</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i data-lucide="users"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-count="<?= $totalCustomers ?? 0 ?>"><?= $totalCustomers ?? 0 ?></span>
                    <span class="stat-label">Total Customers</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i data-lucide="package"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-count="<?= $totalProducts ?? 0 ?>"><?= $totalProducts ?? 0 ?></span>
                    <span class="stat-label">Products</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i data-lucide="x-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-count="<?= $pendingCancels ?? 0 ?>"><?= $pendingCancels ?? 0 ?></span>
                    <span class="stat-label">Pending Cancellations</span>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-grid">
            <div class="chart-container">
                <div class="chart-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 style="margin:0">Sales</h3>
                    <div>
                        <button id="sales-view-monthly" class="btn btn-sm btn-outline" style="margin-right:6px;">Monthly</button>
                        <button id="sales-view-daily" class="btn btn-sm btn-accent">Daily</button>
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

        <!-- Recent Orders + Activity -->
        <div class="dashboard-grid-2">
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                    <h3 style="margin:0;font-size:1rem;font-weight:600;">Recent Orders</h3>
                    <a href="<?= APP_URL ?>/index.php?url=admin/orders" class="btn btn-sm" style="font-size:.8rem;">View All</a>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><a href="<?= APP_URL ?>/index.php?url=admin/orders/<?= $order['id'] ?>"><?= htmlspecialchars($order['order_number']) ?></a></td>
                                        <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                        <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center;color:var(--steel);">No recent orders</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3 style="margin:0 0 1rem;font-size:1rem;font-weight:600;">Recent Activity</h3>
                <div class="activity-feed">
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="activity-item">
                                <div class="activity-dot"></div>
                                <div class="activity-content">
                                    <p class="activity-text"><?= htmlspecialchars($log['action']) ?></p>
                                    <span class="activity-meta">
                                        <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?>
                                        &bull; <?= date('M d, g:i A', strtotime($log['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:var(--steel);text-align:center;padding:1rem;">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Low Stock + Top Products -->
        <div class="dashboard-grid-2">
            <div class="card">
                <h3 style="margin:0 0 1rem;font-size:1rem;font-weight:600;">
                    <i data-lucide="alert-triangle" style="width:16px;height:16px;color:var(--accent);vertical-align:middle;"></i>
                    Low Stock Alert
                </h3>
                <?php if (!empty($lowStock)): ?>
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                            <tbody>
                                <?php foreach ($lowStock as $item): ?>
                                    <tr class="row-warning">
                                        <td><?= htmlspecialchars($item['product_name'] ?? $item['name']) ?></td>
                                        <td><span class="badge badge-cancelled"><?= $item['quantity'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color:var(--steel);text-align:center;padding:1rem;">All stock levels healthy</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="margin:0 0 1rem;font-size:1rem;font-weight:600;">Top Selling Products</h3>
                <?php if (!empty($topProducts)): ?>
                    <div class="data-table-wrap">
                        <table class="data-table">
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
                    </div>
                <?php else: ?>
                    <p style="color:var(--steel);text-align:center;padding:1rem;">No sales data yet</p>
                <?php endif; ?>
            </div>
        </div>

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

    // After charts boot, wire the toggle buttons to swap datasets
    document.addEventListener('DOMContentLoaded', function () {
        function setView(view) {
            // track current view for rollover logic
            window._currentSalesView = view;
            if (!window.DASHBOARD_CHARTS || !window.DASHBOARD_CHARTS.sales) return;
            var payload = window.DASHBOARD_CHARTS.sales[view];
            if (!payload) return;
            if (typeof window.updateSalesChart === 'function') {
                window.updateSalesChart(payload.labels, payload.data);
            }
            // update button styles (use outline for inactive, accent for active)
            var monthlyBtn = document.getElementById('sales-view-monthly');
            var dailyBtn = document.getElementById('sales-view-daily');
            if (monthlyBtn && dailyBtn) {
                monthlyBtn.classList.toggle('btn-accent', view === 'monthly');
                monthlyBtn.classList.toggle('btn-outline', view !== 'monthly');
                dailyBtn.classList.toggle('btn-accent', view === 'daily');
                dailyBtn.classList.toggle('btn-outline', view !== 'daily');
            }
            // start/stop midnight rollover when viewing daily
            if (view === 'daily') {
                // ensure rawDates exist for the rollover logic
                if (payload.rawDates && Array.isArray(payload.rawDates)) {
                    window._salesDailyRawDates = payload.rawDates.slice();
                }
                if (typeof window.shiftDailyForMidnight === 'function') {
                    window.shiftDailyForMidnight();
                }
                if (typeof window.startDailyMidnightTimer === 'function') {
                    window.startDailyMidnightTimer();
                }
            } else if (typeof window.stopDailyMidnightTimer === 'function') {
                window.stopDailyMidnightTimer();
            }
        }

        document.getElementById('sales-view-monthly').addEventListener('click', function () { setView('monthly'); });
        document.getElementById('sales-view-daily').addEventListener('click', function () { setView('daily'); });
        // ensure initial view is monthly (matches previous default)
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
