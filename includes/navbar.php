<?php
/**
 * SouthDev Home Depot – Navigation Bar
 * Charcoal navbar with accent-red active states
 */
$currentUrl = isset($_GET['url']) ? $_GET['url'] : '';

// Ensure cart count is available for the customer navbar badge.
// Most controllers don't pass $cartCount, so compute it safely here.
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER && !isset($cartCount)) {
    $cartCount = 0;
    try {
        $pdo = $GLOBALS['pdo'] ?? null;
        if ($pdo) {
            require_once __DIR__ . '/../models/Cart.php';
            $cartModel = new Cart($pdo);
            $cartCount = (int)$cartModel->getCartCount((int)$_SESSION['user_id']);
        }
    } catch (Throwable $e) {
        $cartCount = 0;
    }
}
?>
<nav class="navbar">
    <div class="container">
        <a href="<?= APP_URL ?>/index.php?url=products" class="logo">
            <?php
                // Prefer an uploaded square logo `image2.png` if available
                $logoRel = 'assets/uploads/images/image2.png';
                $logoFull = ROOT_PATH . '/' . $logoRel;
                if (file_exists($logoFull)):
                    $logoUrl = APP_URL . '/' . $logoRel;
            ?>
                <span class="logo-icon"><img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars(APP_NAME) ?> logo"></span>
            <?php else: ?>
                <span class="logo-icon">SHD</span>
            <?php endif; ?>
            <span class="logo-text"><?= APP_NAME ?></span>
        </a>

        <ul class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role_id'] == ROLE_CUSTOMER): ?>
                    <li>
                        <a href="<?= APP_URL ?>/index.php?url=products" class="<?= $currentUrl == 'products' ? 'active' : '' ?>">
                            <i class="lucide-package"></i> Products
                        </a>
                    </li>
                    <li>
                        <a href="<?= APP_URL ?>/index.php?url=cart" class="nav-cart <?= $currentUrl == 'cart' ? 'active' : '' ?>">
                            <i class="lucide-shopping-cart"></i> Cart
                            <?php $cc = isset($cartCount) ? (int)$cartCount : 0; ?>
                            <span class="cart-count" style="<?= $cc > 0 ? '' : 'display:none' ?>"><?= $cc ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= APP_URL ?>/index.php?url=orders" class="<?= $currentUrl == 'orders' ? 'active' : '' ?>">
                            <i class="lucide-clipboard-list"></i> Orders
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?= APP_URL ?>/index.php?url=profile" class="<?= $currentUrl == 'profile' ? 'active' : '' ?>">
                        <?php
                            $navName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
                            if ($navName === '') $navName = 'Account';
                            $navInitials = strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1) . substr($_SESSION['last_name'] ?? '', 0, 1));
                            $navProfile = $_SESSION['profile_image'] ?? '';
                        ?>
                        <?php if (!empty($navProfile)): ?>
                            <img class="nav-avatar" src="<?= APP_URL ?>/assets/uploads/profiles/<?= rawurlencode($navProfile) ?>" alt="Profile">
                        <?php else: ?>
                            <span class="nav-avatar nav-avatar-fallback" aria-hidden="true"><?= htmlspecialchars($navInitials) ?></span>
                        <?php endif; ?>
                        <span class="nav-account-name"><?= htmlspecialchars($navName) ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?= APP_URL ?>/index.php?url=logout" class="nav-logout">
                        <i class="lucide-log-out"></i> Logout
                    </a>
                </li>
                <li>
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
                        <i data-lucide="moon" class="theme-icon-dark"></i>
                        <i data-lucide="sun" class="theme-icon-light"></i>
                    </button>
                </li>
            <?php else: ?>
                <li><a href="<?= APP_URL ?>/index.php?url=login" class="<?= $currentUrl == 'login' ? 'active' : '' ?>">Login</a></li>
                <li><a href="<?= APP_URL ?>/index.php?url=register" class="btn btn-accent btn-sm">Register</a></li>
                <li>
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
                        <i data-lucide="moon" class="theme-icon-dark"></i>
                        <i data-lucide="sun" class="theme-icon-light"></i>
                    </button>
                </li>
            <?php endif; ?>
        </ul>

        <button class="mobile-toggle" aria-label="Toggle menu">
            <i class="lucide-menu"></i>
        </button>
    </div>
</nav>
