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
// Load categories for Products dropdown (non-blocking; fall back if DB not available)
$categoriesForNav = [];
try {
    $pdo = $GLOBALS['pdo'] ?? null;
    if ($pdo) {
        require_once __DIR__ . '/../models/Category.php';
        $catModel = new Category($pdo);
        $categoriesForNav = $catModel->getAll();
    }
} catch (Throwable $e) {
    $categoriesForNav = [];
}
?>
<header class="site-header">
    <div class="topbar">
        <div class="container topbar-inner">
            <a href="<?= APP_URL ?>" class="brand">
                <?php
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

            <form action="<?= APP_URL ?>/index.php" method="GET" class="search-inline" role="search">
                <input type="hidden" name="url" value="products/search">
                <input type="text" name="q" class="form-control" placeholder="Looking for tiles?" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <span class="btn btn-primary search-icon" aria-hidden="true" style="pointer-events:none;">
                    <i data-lucide="search"></i>
                </span>
            </form>

            <div class="auth-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role_id'] == ROLE_CUSTOMER): ?>
                        <a href="<?= APP_URL ?>/index.php?url=cart" class="nav-cart"><i data-lucide="shopping-cart"></i>
                            <?php $cc = isset($cartCount) ? (int)$cartCount : 0; ?>
                            <span class="cart-count" style="<?= $cc > 0 ? '' : 'display:none' ?>"><?= $cc ?></span>
                        </a>
                    <?php endif; ?>
                    <!-- Topbar PROFILE and Logout removed: use main navigation PROFILE dropdown instead -->
                <?php else: ?>
                    <a href="<?= APP_URL ?>/index.php?url=login" class="auth-link">Login</a>
                    <a href="<?= APP_URL ?>/index.php?url=register" class="btn btn-accent">Register</a>
                <?php endif; ?>
            </div>
            <button class="mobile-toggle" aria-label="Open menu" aria-expanded="false">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </div>

    <nav class="main-nav">
        <div class="container">
            <div class="mobile-search" style="display:none;">
                <form action="<?= APP_URL ?>/index.php" method="GET">
                    <input type="hidden" name="url" value="products/search">
                    <div class="input-icon-wrap">
                        <i data-lucide="search" class="input-icon"></i>
                        <input type="text" name="q" class="form-control" placeholder="Looking for tiles?" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    </div>
                </form>
            </div>
            <ul class="main-menu">
                <li><a href="<?= APP_URL ?>">Home</a></li>
                <li><a href="<?= APP_URL ?>/index.php?url=about">About Us</a></li>
                <li class="menu-has-dropdown">
                    <a href="<?= APP_URL ?>/index.php?url=products">Products <span class="caret">▾</span></a>
                    <div class="submenu">
                        <ul>
                            <li class="submenu-header"><strong>All Products</strong></li>
                            <?php if (!empty($categoriesForNav)): foreach ($categoriesForNav as $cat): ?>
                                <li><a href="<?= APP_URL ?>/index.php?url=products&category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                            <?php endforeach; else: ?>
                                <li><a href="<?= APP_URL ?>/index.php?url=products">Tile Calculator</a></li>
                                <li><a href="<?= APP_URL ?>/index.php?url=products">Tiles</a></li>
                                <li><a href="<?= APP_URL ?>/index.php?url=products">Vinyl</a></li>
                                <li><a href="<?= APP_URL ?>/index.php?url=products">Borders</a></li>
                                <li><a href="<?= APP_URL ?>/index.php?url=products">Mosaics</a></li>
                                <li><a href="<?= APP_URL ?>/index.php?url=products">Sanitary Wares</a></li>
                                <li><a href="<?= APP_URL ?>/index.php?url=products">Adhesives &amp; Tools</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <li><a href="#">Styles &amp; Ideas <span class="caret">▾</span></a></li>
                <li><a href="<?= APP_URL ?>/index.php?url=locations">Locations</a></li>
                <li><a href="#">Contact Us <span class="caret">▾</span></a></li>
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] === ROLE_CUSTOMER): ?>
                        <li class="menu-has-dropdown">
                            <a href="<?= APP_URL ?>/index.php?url=profile">PROFILE <span class="caret">▾</span></a>
                            <div class="submenu">
                                <ul>
                                    <li><a href="<?= APP_URL ?>/index.php?url=profile">Account</a></li>
                                    <li><a href="<?= APP_URL ?>/index.php?url=orders">My Orders</a></li>
                                    <li><a href="<?= APP_URL ?>/index.php?url=logout">Logout</a></li>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                <!-- mobile-only auth links removed to avoid duplicate topbar links -->
            </ul>
        </div>
        <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function(){
            var header = document.querySelector('.site-header');
            var toggle = document.querySelector('.mobile-toggle');
            var mainNav = document.querySelector('.main-nav');
            var mobileSearch = document.querySelector('.mobile-search');
            if(toggle && header){
                toggle.addEventListener('click', function(){
                    var expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', (!expanded).toString());
                    header.classList.toggle('mobile-open');
                    this.classList.toggle('open');
                    // focus mobile search input after menu opens
                    if(!expanded && mobileSearch){
                        setTimeout(function(){
                            var input = mobileSearch.querySelector('input[name="q"]');
                            if(input) input.focus();
                        }, 360);
                    }
                });
            }
            // make dropdowns clickable on mobile
            document.querySelectorAll('.menu-has-dropdown > a').forEach(function(link){
                link.addEventListener('click', function(e){
                    if(window.innerWidth <= 900){
                        e.preventDefault();
                        var parent = this.parentElement;
                        parent.classList.toggle('open');
                    }
                });
            });

            
        });
        </script>
        <script>
        // Server-side flag for logged-in state (used by client logic below)
        window.appLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

        // If the URL contains `login_modal` but the user is already logged in,
        // remove the query param and hide/remove the modal overlay so the
        // page shows account content instead of the sign-in dialog.
        document.addEventListener('DOMContentLoaded', function(){
            try {
                var params = new URLSearchParams(window.location.search);
                if (params.has('login_modal') && window.appLoggedIn) {
                    params.delete('login_modal');
                    var newQs = params.toString();
                    var newUrl = window.location.pathname + (newQs ? '?' + newQs : '');
                    history.replaceState(null, '', newUrl + window.location.hash);

                    var overlay = document.getElementById('loginModalOverlay');
                    if (overlay) {
                        overlay.style.display = 'none';
                        overlay.remove();
                    }
                }
            } catch (e) {
                // non-fatal: ignore older browsers
            }
        });
        </script>
    </nav>
</header>

<?php if (!isset($_SESSION['user_id']) && $currentUrl !== 'login'): ?>
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
