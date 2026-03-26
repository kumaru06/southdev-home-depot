<?php
/**
 * SouthDev Home Depot – Admin/Staff Sidebar
 * Dark charcoal sidebar with animated expansion and red active indicator
 */
$currentUrl = isset($_GET['url']) ? $_GET['url'] : '';
$roleName   = ($_SESSION['role_id'] == ROLE_SUPER_ADMIN) ? 'Super Admin' : (($_SESSION['role_id'] == ROLE_INVENTORY) ? 'Inventory In‑Charge' : 'Staff Admin');
$initials   = strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1) . substr($_SESSION['last_name'] ?? '', 0, 1));
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
                    <span class="nav-icon"><i class="lucide-layout-dashboard"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
        </ul>

        <?php if ($_SESSION['role_id'] == ROLE_STAFF): ?>
        <div class="nav-label">Operations</div>
        <ul class="nav-menu">
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/orders" class="<?= strpos($currentUrl, 'orders') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-clipboard-list"></i></span>
                    <span class="nav-text">Manage Orders</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/cancel-requests" class="<?= strpos($currentUrl, 'cancel') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-x-circle"></i></span>
                    <span class="nav-text">Cancel Requests</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/inventory" class="<?= strpos($currentUrl, 'inventory') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-warehouse"></i></span>
                    <span class="nav-text">Inventory</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/returns" class="<?= strpos($currentUrl, 'returns') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-rotate-ccw"></i></span>
                    <span class="nav-text">Return Requests</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/reports" class="<?= strpos($currentUrl, 'reports') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-bar-chart-3"></i></span>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=staff/reviews" class="<?= strpos($currentUrl, 'reviews') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-message-square"></i></span>
                    <span class="nav-text">Reviews</span>
                </a>
            </li>
        </ul>
        <?php endif; ?>

        <?php if ($_SESSION['role_id'] == ROLE_INVENTORY): ?>
        <div class="nav-label">Inventory</div>
        <ul class="nav-menu">
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/stock" class="<?= strpos($currentUrl, 'inventory/stock') !== false && strpos($currentUrl, 'movements') === false && strpos($currentUrl, 'price-history') === false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-warehouse"></i></span>
                    <span class="nav-text">Manage Stock</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/stock/movements" class="<?= strpos($currentUrl, 'movements') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-activity"></i></span>
                    <span class="nav-text">Stock Movements</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/stock/price-history" class="<?= strpos($currentUrl, 'price-history') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-trending-up"></i></span>
                    <span class="nav-text">Price History</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=inventory/reports" class="<?= strpos($currentUrl, 'reports') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-bar-chart-3"></i></span>
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
                    <span class="nav-icon"><i class="lucide-clipboard-list"></i></span>
                    <span class="nav-text">All Orders</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/cancel-requests" class="<?= strpos($currentUrl, 'cancel') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-x-circle"></i></span>
                    <span class="nav-text">Cancel Requests</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/users" class="<?= strpos($currentUrl, 'users') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-users"></i></span>
                    <span class="nav-text">Manage Users</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/products" class="<?= strpos($currentUrl, 'products') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-package"></i></span>
                    <span class="nav-text">Products</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/categories" class="<?= strpos($currentUrl, 'categories') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-tags"></i></span>
                    <span class="nav-text">Categories</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/inventory" class="<?= strpos($currentUrl, 'inventory') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-warehouse"></i></span>
                    <span class="nav-text">Inventory</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/returns" class="<?= strpos($currentUrl, 'returns') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-rotate-ccw"></i></span>
                    <span class="nav-text">Returns</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/reviews" class="<?= strpos($currentUrl, 'reviews') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-message-square"></i></span>
                    <span class="nav-text">Reviews</span>
                </a>
            </li>
        </ul>

        <div class="nav-label">System</div>
        <ul class="nav-menu">
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/logs" class="<?= strpos($currentUrl, 'logs') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-scroll-text"></i></span>
                    <span class="nav-text">System Logs</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/reports" class="<?= strpos($currentUrl, 'reports') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-bar-chart-3"></i></span>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/index.php?url=admin/settings" class="<?= strpos($currentUrl, 'settings') !== false ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="lucide-settings"></i></span>
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
            <i class="lucide-log-out"></i>
            <span class="logout-emoji" aria-hidden="true">➜]</span>
            <span class="logout-text">Logout</span>
        </a>

    </div>
</aside>

<!-- Sidebar backdrop for mobile -->
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>
