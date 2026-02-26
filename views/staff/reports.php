<?php
/* $pageTitle, $extraCss, $isAdmin set by ReportController */
$extraJs = ['charts.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="page-content">
        <!-- Stat Row -->
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
                        <span class="stat-value"><?= $totalOrders ?? 0 ?></span>
                    </div>
                    <div class="stat-icon"><i data-lucide="shopping-bag"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-info">
                        <span class="stat-label">Customers</span>
                        <span class="stat-value"><?= $totalCustomers ?? 0 ?></span>
                    </div>
                    <div class="stat-icon"><i data-lucide="users"></i></div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-grid">
            <div class="chart-container">
                <div class="chart-header"><h3>Monthly Sales</h3></div>
                <div class="chart-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-header"><h3>Orders by Status</h3></div>
                <div class="chart-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="card">
            <h3><i data-lucide="award"></i> Top Selling Products</h3>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topProducts)): ?>
                            <?php foreach ($topProducts as $i => $product): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= $product['total_sold'] ?></td>
                                    <td>₱<?= number_format($product['total_revenue'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No sales data available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Let assets/js/charts.js initialize charts after footer scripts load.
    window.DASHBOARD_CHARTS = {
        sales: {
            id: 'salesChart',
            labels: <?= json_encode(!empty($monthlySales)
                ? array_map(function ($r) { return date('M Y', strtotime($r['month'] . '-01')); }, array_reverse($monthlySales))
                : []) ?>,
            data: <?= json_encode(!empty($monthlySales)
                ? array_map('floatval', array_column(array_reverse($monthlySales), 'total'))
                : []) ?>
        },
        status: {
            id: 'statusChart',
            labels: <?= json_encode(!empty($orderStatusCounts)
                ? array_map('ucfirst', array_column($orderStatusCounts, 'status'))
                : []) ?>,
            data: <?= json_encode(!empty($orderStatusCounts)
                ? array_map('intval', array_column($orderStatusCounts, 'count'))
                : []) ?>
        }
    };
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
