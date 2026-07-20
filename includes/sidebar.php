<?php
/**
 * SouthDev Home Depot – Admin/Staff Sidebar
 * Dark charcoal sidebar with animated expansion and red active indicator
 */
$currentUrl = isset($_GET['url']) ? $_GET['url'] : '';
$roleName   = ($_SESSION['role_id'] == ROLE_SUPER_ADMIN) ? 'Super Admin' : (($_SESSION['role_id'] == ROLE_INVENTORY) ? 'Inventory In‑Charge' : 'Staff Admin');
$initials   = strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1) . substr($_SESSION['last_name'] ?? '', 0, 1));

$pendingOrdersCount    = 0;
$pendingCancelsCount   = 0;
$pendingReturnsCount   = 0;
$pendingSuppliersCount = 0;
$lowStockCount         = 0;
$roleId = (int) ($_SESSION['role_id'] ?? 0);
$showOrderBadges = in_array($roleId, [ROLE_STAFF, ROLE_SUPER_ADMIN], true);
$showSupplierBadge = in_array($roleId, [ROLE_STAFF, ROLE_SUPER_ADMIN, ROLE_INVENTORY], true);

if (($showOrderBadges || $showSupplierBadge) && !empty($GLOBALS['pdo'])) {
    try {
        $pdoRef = $GLOBALS['pdo'];
        if ($showOrderBadges) {
            if (!class_exists('Order')) {
                require_once MODELS_PATH . '/Order.php';
            }
            if (!class_exists('CancelRequest')) {
                require_once MODELS_PATH . '/CancelRequest.php';
            }
            if (!class_exists('ReturnRequest')) {
                require_once MODELS_PATH . '/ReturnRequest.php';
            }
            $pendingOrdersCount  = (int) (new Order($pdoRef))->countByStatus(ORDER_PENDING);
            $pendingCancelsCount = (int) (new CancelRequest($pdoRef))->countPending();
            $pendingReturnsCount = (int) (new ReturnRequest($pdoRef))->countPending();
        }
        if ($showSupplierBadge) {
            if (!class_exists('SupplierRequest')) {
                require_once MODELS_PATH . '/SupplierRequest.php';
            }
            if (!class_exists('Inventory')) {
                require_once MODELS_PATH . '/Inventory.php';
            }
            $pendingSuppliersCount = (int) (new SupplierRequest($pdoRef))->countPending();
            $lowStockCount         = (int) (new Inventory($pdoRef))->countLowStock();
        }
    } catch (Throwable $e) {
        $pendingOrdersCount    = 0;
        $pendingCancelsCount   = 0;
        $pendingReturnsCount   = 0;
        $pendingSuppliersCount = 0;
        $lowStockCount         = 0;
    }
}

$badgeLabel = static function ($count) {
    $count = (int) $count;
    return $count > 99 ? '99+' : (string) $count;
};
?>
<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="brand">
        <div class="brand-icon">SHD</div>
        <span class="brand-text"><?= APP_NAME ?></span>
    </div>

    <!-- Navigation -->
    <div class="nav-section">

        <div class="nav-label">Main</div>
        <ul class="nav-menu">
            <li>
                <a href="<?= APP_URL ?>/index.php?url=dashboard" class="<?= $currentUrl == 'dashboard' ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
        </ul>

        <?php if ($_SESSION['role_id'] == ROLE_STAFF): ?>
        <div class="nav-label">Operations</div>
        <ul class="nav-menu">
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/orders" class="<?= strpos($currentUrl, 'orders') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="clipboard-list"></i></span>
                    <span class="nav-text">Manage Orders</span>
                    <span class="nav-badge" id="pendingOrdersBadge" style="<?= $pendingOrdersCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingOrdersCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/cancel-requests" class="<?= strpos($currentUrl, 'cancel') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="x-circle"></i></span>
                    <span class="nav-text">Cancel Requests</span>
                    <span class="nav-badge" id="pendingCancelsBadge" style="<?= $pendingCancelsCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingCancelsCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/inventory" class="<?= strpos($currentUrl, 'inventory') !== false && strpos($currentUrl, 'damaged') === false && strpos($currentUrl, 'supplier') === false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="warehouse"></i></span>
                    <span class="nav-text">Inventory</span>
                    <span class="nav-badge" id="lowStockBadge" style="<?= $lowStockCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($lowStockCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/inventory/supplier-requests" class="<?= strpos($currentUrl, 'supplier') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="truck"></i></span>
                    <span class="nav-text">Supplier Requests</span>
                    <span class="nav-badge" id="pendingSuppliersBadge" style="<?= $pendingSuppliersCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingSuppliersCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/returns" class="<?= strpos($currentUrl, 'returns') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="rotate-ccw"></i></span>
                    <span class="nav-text">Return Requests</span>
                    <span class="nav-badge" id="pendingReturnsBadge" style="<?= $pendingReturnsCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingReturnsCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/inventory/damaged" class="<?= strpos($currentUrl, 'damaged') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="alert-octagon"></i></span>
                    <span class="nav-text">Damaged Products</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/reports" class="<?= strpos($currentUrl, 'reports') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="bar-chart-3"></i></span>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/reviews" class="<?= strpos($currentUrl, 'reviews') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="message-square"></i></span>
                    <span class="nav-text">Reviews</span>
                </a>
            </li>
        </ul>
        <?php endif; ?>

        <?php if ($_SESSION['role_id'] == ROLE_INVENTORY): ?>
        <div class="nav-label">Inventory</div>
        <ul class="nav-menu">
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/stock" class="<?= strpos($currentUrl, 'inventory/stock') !== false && strpos($currentUrl, 'movements') === false && strpos($currentUrl, 'price-history') === false && strpos($currentUrl, 'damaged') === false && strpos($currentUrl, 'supplier') === false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="warehouse"></i></span>
                    <span class="nav-text">Manage Stock</span>
                    <span class="nav-badge" id="lowStockBadge" style="<?= $lowStockCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($lowStockCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/stock/supplier-requests" class="<?= strpos($currentUrl, 'supplier') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="truck"></i></span>
                    <span class="nav-text">Supplier Requests</span>
                    <span class="nav-badge" id="pendingSuppliersBadge" style="<?= $pendingSuppliersCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingSuppliersCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/stock/movements" class="<?= strpos($currentUrl, 'movements') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="activity"></i></span>
                    <span class="nav-text">Stock Movements</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/stock/price-history" class="<?= strpos($currentUrl, 'price-history') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="trending-up"></i></span>
                    <span class="nav-text">Price History</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/stock/damaged" class="<?= strpos($currentUrl, 'damaged') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="alert-octagon"></i></span>
                    <span class="nav-text">Damaged Products</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/reports" class="<?= strpos($currentUrl, 'reports') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="bar-chart-3"></i></span>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
        </ul>
        <?php endif; ?>

        <?php if ($_SESSION['role_id'] == ROLE_SUPER_ADMIN): ?>
        <div class="nav-label">Management</div>
        <ul class="nav-menu">
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/orders" class="<?= strpos($currentUrl, 'orders') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="clipboard-list"></i></span>
                    <span class="nav-text">All Orders</span>
                    <span class="nav-badge" id="pendingOrdersBadge" style="<?= $pendingOrdersCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingOrdersCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/cancel-requests" class="<?= strpos($currentUrl, 'cancel') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="x-circle"></i></span>
                    <span class="nav-text">Cancel Requests</span>
                    <span class="nav-badge" id="pendingCancelsBadge" style="<?= $pendingCancelsCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingCancelsCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/users" class="<?= strpos($currentUrl, 'users') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="users"></i></span>
                    <span class="nav-text">Manage Users</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/products" class="<?= strpos($currentUrl, 'products') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="package"></i></span>
                    <span class="nav-text">Products</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/categories" class="<?= strpos($currentUrl, 'categories') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="tags"></i></span>
                    <span class="nav-text">Categories</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/inventory" class="<?= strpos($currentUrl, 'inventory') !== false && strpos($currentUrl, 'damaged') === false && strpos($currentUrl, 'supplier') === false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="warehouse"></i></span>
                    <span class="nav-text">Inventory</span>
                    <span class="nav-badge" id="lowStockBadge" style="<?= $lowStockCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($lowStockCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/inventory/supplier-requests" class="<?= strpos($currentUrl, 'supplier') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="truck"></i></span>
                    <span class="nav-text">Supplier Requests</span>
                    <span class="nav-badge" id="pendingSuppliersBadge" style="<?= $pendingSuppliersCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingSuppliersCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/returns" class="<?= strpos($currentUrl, 'returns') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="rotate-ccw"></i></span>
                    <span class="nav-text">Returns</span>
                    <span class="nav-badge" id="pendingReturnsBadge" style="<?= $pendingReturnsCount > 0 ? '' : 'display:none' ?>"><?= htmlspecialchars($badgeLabel($pendingReturnsCount)) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/inventory/damaged" class="<?= strpos($currentUrl, 'damaged') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="alert-octagon"></i></span>
                    <span class="nav-text">Damaged Products</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/reviews" class="<?= strpos($currentUrl, 'reviews') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="message-square"></i></span>
                    <span class="nav-text">Reviews</span>
                </a>
            </li>
        </ul>

        <div class="nav-label">System</div>
        <ul class="nav-menu">
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/logs" class="<?= strpos($currentUrl, 'logs') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="scroll-text"></i></span>
                    <span class="nav-text">System Logs</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/reports" class="<?= strpos($currentUrl, 'reports') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="bar-chart-3"></i></span>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/settings" class="<?= strpos($currentUrl, 'settings') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i data-lucide="settings"></i></span>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
        </ul>
        <?php endif; ?>

    </div>

    <!-- Sidebar Footer / User -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar"><?= $initials ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?></div>
                <div class="user-role"><?= $roleName ?></div>
            </div>
        </div>

        <a href="<?= APP_URL ?>/index.php?url=logout" class="sidebar-logout" aria-label="Logout">
            <i data-lucide="log-out"></i>
            <span class="logout-emoji" aria-hidden="true">➜]</span>
            <span class="logout-text">Logout</span>
        </a>

    </div>
</aside>

<!-- Sidebar backdrop for mobile -->
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

<?php if ($showOrderBadges || $showSupplierBadge): ?>
<script>
(function () {
    var ordersBadge    = document.getElementById('pendingOrdersBadge');
    var cancelsBadge   = document.getElementById('pendingCancelsBadge');
    var returnsBadge   = document.getElementById('pendingReturnsBadge');
    var suppliersBadge = document.getElementById('pendingSuppliersBadge');
    var lowStockBadge  = document.getElementById('lowStockBadge');

    var roleId = <?= (int) $roleId ?>;
    var isSuperAdmin = roleId === <?= (int) ROLE_SUPER_ADMIN ?>;
    var isInventory  = roleId === <?= (int) ROLE_INVENTORY ?>;

    function formatCount(count) {
        count = parseInt(count, 10) || 0;
        return count > 99 ? '99+' : String(count);
    }

    function applyCount(badge, count) {
        if (!badge) return;
        count = parseInt(count, 10) || 0;
        if (count > 0) {
            badge.textContent = formatCount(count);
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }

    function fetchJson(url) {
        return fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) {
            if (!r.ok) throw new Error('bad status');
            return r.json();
        });
    }

    function refreshSidebarBadges() {
        var stamp = '&_=' + Date.now();

        if (ordersBadge || cancelsBadge || returnsBadge) {
            var orderPrefix = isSuperAdmin ? 'admin' : 'staff';
            fetchJson('index.php?url=' + orderPrefix + '/orders/pending-count' + stamp)
                .then(function (data) {
                    if (!data) return;
                    applyCount(ordersBadge, data.orders != null ? data.orders : data.count);
                    applyCount(cancelsBadge, data.cancels);
                    applyCount(returnsBadge, data.returns);
                })
                .catch(function () {});
        }

        if (suppliersBadge || lowStockBadge) {
            var supplierUrl;
            if (isInventory) {
                supplierUrl = 'index.php?url=inventory/stock/supplier-pending-count';
            } else if (isSuperAdmin) {
                supplierUrl = 'index.php?url=admin/inventory/supplier-pending-count';
            } else {
                supplierUrl = 'index.php?url=staff/inventory/supplier-pending-count';
            }
            fetchJson(supplierUrl + stamp)
                .then(function (data) {
                    if (!data) return;
                    applyCount(suppliersBadge, data.suppliers != null ? data.suppliers : data.count);
                    applyCount(lowStockBadge, data.low_stock);
                })
                .catch(function () {});
        }
    }

    refreshSidebarBadges();
    setInterval(refreshSidebarBadges, 2000);

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) refreshSidebarBadges();
    });
})();
</script>
<?php endif; ?>
