<?php
/* $pageTitle, $extraCss, $isAdmin set by ReportController */
$extraJs = ['charts.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
$activeTab = $_GET['tab'] ?? 'sales';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="page-content">
        <!-- Report Tabs -->
        <div class="report-tabs" style="display:flex;gap:0;margin-bottom:1.5rem;border-bottom:2px solid var(--border);">
            <a href="?url=<?= $_GET['url'] ?? 'staff/reports' ?>&tab=sales" class="report-tab <?= $activeTab === 'sales' ? 'active' : '' ?>">
                <i data-lucide="trending-up" style="width:16px;height:16px"></i> Sales Report
            </a>
            <a href="?url=<?= $_GET['url'] ?? 'staff/reports' ?>&tab=inventory" class="report-tab <?= $activeTab === 'inventory' ? 'active' : '' ?>">
                <i data-lucide="warehouse" style="width:16px;height:16px"></i> Inventory Report
            </a>
            <a href="?url=<?= $_GET['url'] ?? 'staff/reports' ?>&tab=returns" class="report-tab <?= $activeTab === 'returns' ? 'active' : '' ?>">
                <i data-lucide="rotate-ccw" style="width:16px;height:16px"></i> Returns Report
            </a>
        </div>

        <!-- ===== SALES TAB ===== -->
        <?php if ($activeTab === 'sales'): ?>
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
                    <span class="stat-label">Customers</span>
                    <span class="stat-value"><?= $totalCustomers ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="users"></i></div>
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

        <!-- ===== INVENTORY TAB ===== -->
        <?php elseif ($activeTab === 'inventory'): ?>
        <div class="stat-cards">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Inventory Value</span>
                    <span class="stat-value">₱<?= number_format($totalInventoryValue ?? 0, 2) ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="dollar-sign"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Stock Units</span>
                    <span class="stat-value"><?= number_format($totalStockUnits ?? 0) ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="package"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Low Stock Items</span>
                    <span class="stat-value" style="color:var(--warning);"><?= count($lowStockItems ?? []) ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="alert-triangle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Out of Stock</span>
                    <span class="stat-value" style="color:var(--danger);"><?= $outOfStockCount ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="x-circle"></i></div>
            </div>
        </div>

        <!-- Stock Movement Summary (Last 30 Days) -->
        <?php if (!empty($stockSummary)): ?>
        <div class="card" style="margin-bottom:1.5rem;">
            <h3><i data-lucide="activity"></i> Stock Movements (Last 30 Days)</h3>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Movements</th>
                            <th>Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stockSummary as $s): ?>
                            <tr>
                                <td><span class="badge badge-pending"><?= ucfirst($s['type']) ?></span></td>
                                <td><?= $s['movement_count'] ?></td>
                                <td>
                                    <?php if ($s['total_quantity'] > 0): ?>
                                        <span style="color:var(--success);font-weight:600;">+<?= $s['total_quantity'] ?></span>
                                    <?php else: ?>
                                        <span style="color:var(--danger);font-weight:600;"><?= $s['total_quantity'] ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Low Stock Items -->
        <?php if (!empty($lowStockItems)): ?>
        <div class="card">
            <h3><i data-lucide="alert-triangle"></i> Low Stock & Out of Stock Items</h3>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockItems as $item): ?>
                            <tr class="<?= intval($item['quantity']) <= 0 ? 'row-danger' : 'row-warning' ?>">
                                <td><code><?= htmlspecialchars($item['sku'] ?? 'N/A') ?></code></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td>
                                    <span class="badge badge-cancelled"><?= $item['quantity'] ?></span>
                                </td>
                                <td><?= $item['reorder_level'] ?? 10 ?></td>
                                <td>
                                    <?php if (intval($item['quantity']) <= 0): ?>
                                        <span class="badge badge-cancelled">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
            <div class="card" style="text-align:center;padding:2rem;">
                <i data-lucide="check-circle" style="width:40px;height:40px;color:var(--success);margin-bottom:.5rem;"></i>
                <p>All items are well-stocked!</p>
            </div>
        <?php endif; ?>

        <!-- ===== RETURNS TAB ===== -->
        <?php elseif ($activeTab === 'returns'): ?>
        <div class="stat-cards">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Returns</span>
                    <span class="stat-value"><?= $totalReturns ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="rotate-ccw"></i></div>
            </div>
            <?php foreach ($returnStatusCounts ?? [] as $rs): ?>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label"><?= ucfirst($rs['status']) ?></span>
                    <span class="stat-value"><?= $rs['count'] ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="clipboard-list"></i></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Monthly Returns Chart -->
        <div class="chart-grid">
            <div class="chart-container" style="grid-column: span 2;">
                <div class="chart-header"><h3>Monthly Returns Trend</h3></div>
                <div class="chart-body">
                    <canvas id="returnsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Returns -->
        <div class="card">
            <h3><i data-lucide="rotate-ccw"></i> Recent Return Requests</h3>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentReturns)): ?>
                            <?php foreach ($recentReturns as $ret): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($ret['created_at'])) ?></td>
                                    <td><code><?= htmlspecialchars($ret['order_number']) ?></code></td>
                                    <td><?= htmlspecialchars($ret['first_name'] . ' ' . $ret['last_name']) ?></td>
                                    <td style="max-width:200px;white-space:normal;"><?= htmlspecialchars($ret['reason'] ?? '') ?></td>
                                    <td>
                                        <?php
                                        $statusBadge = [
                                            'pending' => 'badge-pending',
                                            'approved' => 'badge-processing',
                                            'rejected' => 'badge-cancelled',
                                            'completed' => 'badge-delivered',
                                        ];
                                        ?>
                                        <span class="badge <?= $statusBadge[$ret['status']] ?? 'badge-pending' ?>"><?= ucfirst($ret['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No return requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.report-tabs { display: flex; gap: 0; }
.report-tab {
    padding: .75rem 1.25rem;
    font-size: .9rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-decoration: none;
    border-bottom: 3px solid transparent;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all .2s ease;
}
.report-tab:hover { color: var(--primary); background: var(--accent-light); }
.report-tab.active {
    color: var(--primary);
    border-bottom-color: var(--accent);
}
.row-danger { background: var(--danger-bg) !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($activeTab === 'sales'): ?>
<script>
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
<?php elseif ($activeTab === 'returns'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('returnsChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode(!empty($monthlyReturns)
                        ? array_map(function ($r) { return date('M Y', strtotime($r['month'] . '-01')); }, array_reverse($monthlyReturns))
                        : []) ?>,
                    datasets: [{
                        label: 'Returns',
                        data: <?= json_encode(!empty($monthlyReturns)
                            ? array_map('intval', array_column(array_reverse($monthlyReturns), 'count'))
                            : []) ?>,
                        backgroundColor: 'rgba(255, 87, 51, 0.6)',
                        borderColor: 'rgb(255, 87, 51)',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
    });
</script>
<?php endif; ?>
<script>
    (function () {
        if (typeof Chart === 'undefined') {
            var s = document.createElement('script');
            s.src = '/assets/vendor/chartjs/chart.min.js';
            s.defer = true;
            document.head.appendChild(s);
        }
    })();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
