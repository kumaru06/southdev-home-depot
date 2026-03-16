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
            <?php else: ?>
                <li><a href="<?= APP_URL ?>/index.php?url=login" class="<?= $currentUrl == 'login' ? 'active' : '' ?>">Login</a></li>
                <li><a href="<?= APP_URL ?>/index.php?url=register" class="btn btn-accent btn-sm">Register</a></li>
            <?php endif; ?>
        </ul>

        <button class="mobile-toggle" aria-label="Toggle menu">
            <i class="lucide-menu"></i>
        </button>
    </div>
</nav>

<?php if (!isset($_SESSION['user_id'])): ?>
<!-- Login Modal Overlay (blur background like logout dialog) -->
<div class="login-modal-overlay" id="loginModalOverlay" role="dialog" aria-modal="true" aria-label="Sign in">
    <div class="login-modal">
        <button type="button" class="login-modal-close" aria-label="Close" id="loginModalClose">
            <i data-lucide="x"></i>
        </button>

        <div class="login-modal-split">
            <?php
                $modalImage = null;
                $modalImageRel = 'assets/uploads/images/image.png';
                $modalImageFull = ROOT_PATH . '/' . $modalImageRel;
                if (file_exists($modalImageFull)) {
                    $modalImage = APP_URL . '/' . $modalImageRel;
                }
            ?>
            <div class="login-modal-media" style="--login-modal-img: <?= $modalImage ? "url('" . htmlspecialchars($modalImage) . "')" : 'none' ?>;">
                <div class="login-modal-media-overlay"></div>
                <div class="login-modal-media-content">
                    <div class="login-modal-badge">Welcome back</div>
                    <div class="login-modal-store"><?= APP_NAME ?></div>
                    <div class="login-modal-tagline"><?= APP_TAGLINE ?></div>
                </div>
            </div>

            <div class="login-modal-form-panel">
                <div class="login-modal-brand">
                    <i data-lucide="grid-3x3" class="login-modal-icon"></i>
                    <div>
                        <h2><?= APP_NAME ?></h2>
                        <p>Sign in to your account</p>
                    </div>
                </div>

                <div class="login-modal-error" id="loginModalError" style="display:none;"></div>

                <form id="loginModalForm" method="POST" action="<?= APP_URL ?>/index.php?url=login">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="form-group">
                        <label for="loginModalEmail">Email Address</label>
                        <div class="input-icon-wrap">
                            <i data-lucide="mail" class="input-icon"></i>
                            <input type="email" id="loginModalEmail" name="email" class="form-control" placeholder="you@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="loginModalPassword">Password</label>
                        <div class="input-icon-wrap">
                            <i data-lucide="lock" class="input-icon"></i>
                            <input type="password" id="loginModalPassword" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent btn-block" id="loginModalSubmit">
                        <i data-lucide="log-in"></i> Sign In
                    </button>
                </form>

                <div class="login-modal-footer">
                    <p>Need to verify your email? <a href="<?= APP_URL ?>/index.php?url=verify-email">Resend verification</a></p>
                    <p><a href="<?= APP_URL ?>/index.php?url=forgot-password">Forgot password?</a></p>
                    <p><a href="<?= APP_URL ?>/index.php?url=admin-login">Continue as administrator</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
