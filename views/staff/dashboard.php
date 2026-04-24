<?php
/* $pageTitle, $extraCss, $extraJs, $isAdmin set by DashboardController */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

$firstName = htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['user_name'] ?? 'there');
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');

// Links for this role
$isInventory = ($_SESSION['role_id'] == ROLE_INVENTORY);
$ordersUrl   = $isInventory ? '#' : APP_URL . '/index.php?url=staff/orders';
$cancelUrl   = $isInventory ? '#' : APP_URL . '/index.php?url=staff/cancel-requests';
$invUrl      = $isInventory ? APP_URL . '/index.php?url=inventory/stock' : APP_URL . '/index.php?url=staff/inventory';
$returnsUrl  = $isInventory ? '#' : APP_URL . '/index.php?url=staff/returns';
$damagedUrl  = $isInventory ? APP_URL . '/index.php?url=inventory/stock/damaged' : APP_URL . '/index.php?url=staff/inventory/damaged';
$reportsUrl  = $isInventory ? APP_URL . '/index.php?url=inventory/reports' : APP_URL . '/index.php?url=staff/reports';
$reviewsUrl  = $isInventory ? '#' : APP_URL . '/index.php?url=staff/reviews';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
        <div class="top-bar-right">
            <span class="top-bar-greeting"><?= $greeting ?>, <?= $firstName ?></span>
        </div>
    </div>

    <div class="page-content">

        <!-- ─── Hero Stat Cards ──────────────────────────────── -->
        <div class="stat-cards">
            <div class="stat-card stat-accent">
                <div class="stat-info">
                    <span class="stat-label">Total Sales</span>
                    <span class="stat-value">₱<?= number_format($totalSales ?? 0, 2) ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="trending-up"></i></div>
            </div>
            <div class="stat-card stat-info-c">
                <div class="stat-info">
                    <span class="stat-label">Total Orders</span>
                    <span class="stat-value"><?= $totalOrders ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="shopping-bag"></i></div>
            </div>
            <div class="stat-card stat-warning-c">
                <div class="stat-info">
                    <span class="stat-label">Pending Orders</span>
                    <span class="stat-value"><?= $pendingOrders ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="clock"></i></div>
            </div>
            <div class="stat-card stat-danger-c">
                <div class="stat-info">
                    <span class="stat-label">Cancel Requests</span>
                    <span class="stat-value"><?= $pendingCancels ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="x-circle"></i></div>
            </div>
            <div class="stat-card stat-red-c">
                <div class="stat-info">
                    <span class="stat-label">Damaged Products</span>
                    <span class="stat-value"><?= $totalDamaged ?? 0 ?></span>
                </div>
                <div class="stat-icon"><i data-lucide="alert-octagon"></i></div>
            </div>
        </div>

        <!-- ─── Charts Row ───────────────────────────────────── -->
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

        <!-- ─── Recent Orders + Low Stock ────────────────────── -->
        <div class="dashboard-grid-orders">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="package"></i> Recent Orders</h3>
                    <?php if (!$isInventory): ?>
                    <a href="<?= $ordersUrl ?>" class="view-all-link">View All <i data-lucide="arrow-right" style="width:14px;height:14px;"></i></a>
                    <?php endif; ?>
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
                                        <td><a href="<?= APP_URL ?>/index.php?url=staff/orders/<?= $order['id'] ?>"><?= htmlspecialchars($order['order_number']) ?></a></td>
                                        <td><?= htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?></td>
                                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                        <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="dash-empty"><i data-lucide="inbox"></i><p>No recent orders</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="alert-triangle" style="color:var(--warning);"></i> Low Stock Alert</h3>
                </div>
                <div class="dash-card-body">
                    <?php if (!empty($lowStock)): ?>
                        <table class="dash-table">
                            <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                            <tbody>
                                <?php foreach (array_slice($lowStock, 0, 6) as $item):
                                    $qty = (int)$item['quantity'];
                                    $cls = $qty <= 5 ? 'critical' : 'warning';
                                ?>
                                    <tr class="row-warning">
                                        <td><?= htmlspecialchars($item['product_name'] ?? $item['name'] ?? '') ?></td>
                                        <td><span class="stock-badge <?= $cls ?>"><?= $qty ?> left</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="dash-empty"><i data-lucide="check-circle" style="color:var(--success);opacity:.6;"></i><p>All stock levels healthy</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ─── Top Products + Damaged ───────────────────────── -->
        <div class="dashboard-grid-2">
            <?php if (!empty($topProducts)): ?>
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="award"></i> Top Selling Products</h3>
                </div>
                <div class="dash-card-body" style="padding:16px 20px;">
                    <div class="top-products-list">
                        <?php foreach ($topProducts as $i => $tp): ?>
                        <div class="tp-row">
                            <span class="tp-rank"><?= $i + 1 ?></span>
                            <div class="tp-info">
                                <span class="tp-name"><?= htmlspecialchars($tp['name']) ?></span>
                                <span class="tp-meta"><?= $tp['total_sold'] ?> sold &bull; ₱<?= number_format($tp['total_revenue'], 2) ?></span>
                            </div>
                            <div class="tp-bar-wrap">
                                <?php $maxSold = $topProducts[0]['total_sold'] ?? 1; $pct = round(($tp['total_sold'] / max($maxSold,1)) * 100); ?>
                                <div class="tp-bar" style="width:<?= $pct ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($recentDamaged)): ?>
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i data-lucide="alert-octagon" style="color:var(--danger);"></i> Damaged Products</h3>
                    <a href="<?= $damagedUrl ?>" class="view-all-link">View All <i data-lucide="arrow-right" style="width:14px;height:14px;"></i></a>
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
                                    default       => 'badge-pending'
                                };
                                // Show only the short unique suffix of the order number, full on hover
                                $orderNum = $dmg['order_number'];
                                $orderShort = '#' . substr($orderNum, strrpos($orderNum, '-') + 1);
                            ?>
                                <tr>
                                    <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($dmg['product_name']) ?>"><?= htmlspecialchars($dmg['product_name']) ?></td>
                                    <td><strong><?= $dmg['quantity'] ?></strong></td>
                                    <td><span title="<?= htmlspecialchars($orderNum) ?>"><?= htmlspecialchars($orderShort) ?></span></td>
                                    <td><span class="badge <?= $dmgClass ?>"><?= ucfirst(str_replace('_', ' ', $dmg['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
            monthly: { labels: <?= json_encode($chartLabels ?? []) ?>, data: <?= json_encode($chartData ?? []) ?> },
            daily:   { labels: <?= json_encode($dailyLabels ?? []) ?>, data: <?= json_encode($dailyData ?? []) ?>, rawDates: <?= json_encode($dailyRawDates ?? []) ?> }
        },
        category: { id: 'categoryChart', labels: <?= json_encode($catLabels ?? []) ?>, data: <?= json_encode($catData ?? []) ?> }
    };

    document.addEventListener('DOMContentLoaded', function () {
        function setView(view) {
            window._currentSalesView = view;
            if (typeof window.switchSalesView === 'function') window.switchSalesView(view);
            var m = document.getElementById('sales-view-monthly'), d = document.getElementById('sales-view-daily');
            if (m && d) {
                m.classList.toggle('btn-accent', view === 'monthly');
                m.classList.toggle('btn-outline', view !== 'monthly');
                d.classList.toggle('btn-accent', view === 'daily');
                d.classList.toggle('btn-outline', view !== 'daily');
            }
        }
        var m = document.getElementById('sales-view-monthly'), d = document.getElementById('sales-view-daily');
        if (m) m.addEventListener('click', function () { setView('monthly'); });
        if (d) d.addEventListener('click', function () { setView('daily'); });
        setView('monthly');
    });
</script>
<script>
    (function(){try{if(typeof Chart==='undefined'){var fb=document.querySelector('.category-fallback'),cvs=document.getElementById('categoryChart');if(fb)fb.style.display='block';if(cvs)cvs.style.display='none';}}catch(e){}})();
    (function(){if(typeof Chart==='undefined'){var s=document.createElement('script');s.src='/assets/vendor/chartjs/chart.min.js';s.defer=true;document.head.appendChild(s);}})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
