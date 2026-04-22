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
// Ensure notification count is available for customer navbar badge
$notifCount = 0;
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER) {
    try {
        $pdo = $GLOBALS['pdo'] ?? null;
        if ($pdo) {
            require_once __DIR__ . '/../models/Notification.php';
            $notifModel = new Notification($pdo);
            $notifCount = (int)$notifModel->getUnreadCount((int)$_SESSION['user_id']);
        }
    } catch (Throwable $e) {
        $notifCount = 0;
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
                    $logoRel = 'assets/uploads/images/southdev.png';
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
                <div class="search-box">
                    <input type="text" name="q" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="search-btn" aria-label="Search"></button>
                </div>
            </form>

            <div class="auth-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role_id'] == ROLE_CUSTOMER): ?>
                        <div class="notif-dropdown-wrap" id="notifDropdownWrap">
                            <button type="button" class="nav-notif-btn" title="Notifications" id="notifBellBtn">
                                <i data-lucide="bell"></i>
                                <?php $nc = isset($notifCount) ? (int)$notifCount : 0; ?>
                                <span class="notif-count" id="notifBadge" style="<?= $nc > 0 ? '' : 'display:none' ?>"><?= $nc ?></span>
                            </button>
                            <div class="notif-dropdown" id="notifDropdown">
                                <div class="notif-dropdown-header">
                                    <h3>Notifications</h3>
                                    <a href="<?= APP_URL ?>/index.php?url=notifications/mark-all-read" class="notif-mark-all" id="notifMarkAll">Mark all read</a>
                                </div>
                                <div class="notif-dropdown-body" id="notifDropdownBody">
                                    <div class="notif-dropdown-loading">
                                        <div class="notif-spinner"></div>
                                    </div>
                                </div>
                                <a href="<?= APP_URL ?>/index.php?url=notifications" class="notif-dropdown-footer">See all notifications</a>
                            </div>
                        </div>
                        <a href="<?= APP_URL ?>/index.php?url=cart" class="nav-cart-btn" title="Shopping Cart">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="9" cy="20" r="1"></circle>
                                <circle cx="18" cy="20" r="1"></circle>
                                <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.5L21 7H8"></path>
                            </svg>
                            <?php $cc = isset($cartCount) ? (int)$cartCount : 0; ?>
                            <span class="cart-count" style="<?= $cc > 0 ? '' : 'display:none' ?>"><?= $cc ?></span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/index.php?url=login" class="auth-link">Login</a>
                    <a href="<?= APP_URL ?>/index.php?url=register" class="btn btn-accent">Register</a>
                <?php endif; ?>
            </div>
            <button class="mobile-toggle" aria-label="Open menu" aria-expanded="false">
                &#9776;
            </button>
        </div>
    </div>

    <nav class="main-nav">
        <div class="container">
            <div class="mobile-search" style="display:none;">
                <form action="<?= APP_URL ?>/index.php" method="GET">
                    <input type="hidden" name="url" value="products/search">
                    <div class="input-icon-wrap">
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
                <li class="menu-has-dropdown">
                    <a href="#">Styles &amp; Ideas <span class="caret">▾</span></a>
                    <div class="submenu">
                        <ul>
                            <li class="submenu-header"><strong>Inspiration</strong></li>
                            <li><a href="<?= APP_URL ?>/index.php?url=blog">Blog</a></li>
                            <li><a href="<?= APP_URL ?>/index.php?url=featured-collections">Featured Collections</a></li>
                            <li><a href="<?= APP_URL ?>/index.php?url=room-gallery">Room Gallery</a></li>
                        </ul>
                    </div>
                </li>
                <li><a href="<?= APP_URL ?>/index.php?url=locations">Location</a></li>
                <li class="menu-has-dropdown">
                    <a href="#">Contact Us <span class="caret">▾</span></a>
                    <div class="submenu">
                        <ul>
                            <li class="submenu-header"><strong>Customer Support</strong></li>
                            <li><a href="<?= APP_URL ?>/index.php?url=product-inquiry">Product Inquiry</a></li>
                            <li><a href="<?= APP_URL ?>/index.php?url=faqs">FAQs</a></li>
                        </ul>
                    </div>
                </li>
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

            <!-- Mobile extras: cart + auth buttons visible in hamburger menu -->
            <div class="mobile-nav-extras">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role_id'] == ROLE_CUSTOMER): ?>
                        <a href="<?= APP_URL ?>/index.php?url=notifications" class="mobile-cart-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            Notifications<?php $nc2 = isset($notifCount) ? (int)$notifCount : 0; if ($nc2 > 0): ?> (<?= $nc2 ?>)<?php endif; ?>
                        </a>
                        <a href="<?= APP_URL ?>/index.php?url=cart" class="mobile-cart-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            Cart<?php $cc = isset($cartCount) ? (int)$cartCount : 0; if ($cc > 0): ?> (<?= $cc ?>)<?php endif; ?>
                        </a>
                        <a href="<?= APP_URL ?>/index.php?url=orders" class="mobile-auth-link">My Orders</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/index.php?url=login" class="mobile-auth-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Login
                    </a>
                    <a href="<?= APP_URL ?>/index.php?url=register" class="mobile-cart-link">Register</a>
                <?php endif; ?>
            </div>
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
                    this.classList.toggle('open');                    // swap hamburger ↔ X
                    this.innerHTML = expanded ? '&#9776;' : '&times;';                    // focus mobile search input after menu opens
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
                        var wasOpen = parent.classList.contains('open');
                        // close ALL open dropdowns first
                        document.querySelectorAll('.menu-has-dropdown.open').forEach(function(dd){
                            dd.classList.remove('open');
                        });
                        // toggle the clicked one (re-open if it wasn't already open)
                        if(!wasOpen) parent.classList.add('open');
                    }
                });
            });

            // Close dropdowns when clicking non-dropdown menu links
            document.querySelectorAll('.main-menu > li:not(.menu-has-dropdown) > a').forEach(function(link){
                link.addEventListener('click', function(){
                    document.querySelectorAll('.menu-has-dropdown.open').forEach(function(dd){
                        dd.classList.remove('open');
                    });
                });
            });

            // Close dropdowns when clicking non-dropdown menu links
            document.querySelectorAll('.main-menu > li:not(.menu-has-dropdown) > a').forEach(function(link){
                link.addEventListener('click', function(){
                    document.querySelectorAll('.menu-has-dropdown.open').forEach(function(dd){
                        dd.classList.remove('open');
                    });
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
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER): ?>
        <script>
        // Notification Dropdown
        (function(){
            var APP_URL = '<?= APP_URL ?>';
            var wrap = document.getElementById('notifDropdownWrap');
            var btn = document.getElementById('notifBellBtn');
            var dropdown = document.getElementById('notifDropdown');
            var body = document.getElementById('notifDropdownBody');
            var badge = document.getElementById('notifBadge');
            var markAllBtn = document.getElementById('notifMarkAll');
            if (!wrap || !btn || !dropdown) return;

            var isOpen = false;
            var loaded = false;

            var iconMap = {
                'order_processing': 'package',
                'order_shipped': 'truck',
                'order_delivered': 'check-circle',
                'order_cancelled': 'x-circle',
                'order_update': 'bell',
                'order': 'bell',
                'cancel_requested': 'clock',
                'cancel_approved': 'check-circle',
                'cancel_rejected': 'x-circle',
                'return_requested': 'rotate-ccw',
                'return_approved': 'rotate-ccw',
                'return_rejected': 'x-circle',
                'return_completed': 'check-circle'
            };
            var colorMap = {
                'order_processing': '#f97316',
                'order_shipped': '#3b82f6',
                'order_delivered': '#22c55e',
                'order_cancelled': '#ef4444',
                'cancel_requested': '#f59e0b',
                'cancel_approved': '#22c55e',
                'cancel_rejected': '#ef4444',
                'return_requested': '#f59e0b',
                'return_approved': '#22c55e',
                'return_rejected': '#ef4444',
                'return_completed': '#22c55e'
            };

            btn.addEventListener('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                isOpen = !isOpen;
                dropdown.classList.toggle('open', isOpen);
                if (isOpen) loadNotifications();
            });

            // Open on hover
            var hoverTimer = null;
            wrap.addEventListener('mouseenter', function(){
                clearTimeout(hoverTimer);
                if (!isOpen) {
                    isOpen = true;
                    dropdown.classList.add('open');
                    loadNotifications();
                }
            });
            wrap.addEventListener('mouseleave', function(){
                hoverTimer = setTimeout(function(){
                    isOpen = false;
                    dropdown.classList.remove('open');
                }, 250);
            });

            // Close on outside click
            document.addEventListener('click', function(e){
                if (isOpen && !wrap.contains(e.target)) {
                    isOpen = false;
                    dropdown.classList.remove('open');
                }
            });

            // Mark all read
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    fetch(APP_URL + '/index.php?url=notifications/mark-all-read', {
                        method: 'GET',
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    }).then(function(){ 
                        if (badge) { badge.style.display = 'none'; badge.textContent = '0'; }
                        document.querySelectorAll('.nd-item--unread').forEach(function(el){
                            el.classList.remove('nd-item--unread');
                        });
                        document.querySelectorAll('.nd-dot').forEach(function(el){
                            el.remove();
                        });
                    });
                });
            }

            function loadNotifications(){
                body.innerHTML = '<div class="notif-dropdown-loading"><div class="notif-spinner"></div></div>';
                fetch(APP_URL + '/index.php?url=notifications/api-unread')
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (badge) {
                            if (data.count > 0) { badge.textContent = data.count; badge.style.display = ''; }
                            else { badge.style.display = 'none'; }
                        }
                        if (!data.notifications || data.notifications.length === 0) {
                            body.innerHTML = '<div class="nd-empty"><i data-lucide="bell-off"></i><p>No notifications yet</p></div>';
                            if (window.lucide) lucide.createIcons({attrs:{class:'lucide-icon'}});
                            return;
                        }
                        var html = '';
                        data.notifications.forEach(function(n){
                            var icon = iconMap[n.type] || 'bell';
                            var color = colorMap[n.type] || '#f97316';
                            var unread = !n.is_read;
                            var link = n.link || (APP_URL + '/index.php?url=notifications/read/' + n.id);
                            html += '<a href="' + APP_URL + '/index.php?url=notifications/read/' + n.id + '" class="nd-item' + (unread ? ' nd-item--unread' : '') + '">';
                            html += '<div class="nd-icon" style="background:' + color + '15;color:' + color + '"><i data-lucide="' + icon + '"></i></div>';
                            html += '<div class="nd-content">';
                            html += '<div class="nd-text"><strong>' + escHtml(n.title) + '</strong> ' + escHtml(n.message) + '</div>';
                            html += '<div class="nd-time">' + escHtml(n.time_ago) + '</div>';
                            html += '</div>';
                            if (unread) html += '<span class="nd-dot"></span>';
                            html += '</a>';
                        });
                        body.innerHTML = html;
                        if (window.lucide) lucide.createIcons({attrs:{class:'lucide-icon'}});
                    })
                    .catch(function(){
                        body.innerHTML = '<div class="nd-empty"><p>Failed to load</p></div>';
                    });
            }

            function escHtml(s) {
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            // Poll for new notifications every 30s
            setInterval(function(){
                fetch(APP_URL + '/index.php?url=notifications/api-unread')
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (badge) {
                            if (data.count > 0) { badge.textContent = data.count; badge.style.display = ''; }
                            else { badge.style.display = 'none'; }
                        }
                    }).catch(function(){});
            }, 30000);
        })();
        </script>
        <?php endif; ?>
    </nav>
</header>

<?php if (!isset($_SESSION['user_id'])): ?>
<!-- Login Modal Overlay (blur background like logout dialog) -->
<div class="login-modal-overlay" id="loginModalOverlay" role="dialog" aria-modal="true" aria-label="Sign in">
    <div class="login-modal">
        <button type="button" class="login-modal-close" aria-label="Close" id="loginModalClose">
            &times;
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
                    <div class="login-modal-icon-wrap">
                        <img src="<?= APP_URL ?>/assets/uploads/images/png-icon/tile.png" alt="Tile" class="login-modal-icon">
                    </div>
                    <div class="login-modal-brand-text">
                        <h2><?= APP_NAME ?></h2>
                        <p>Sign in to your account</p>
                    </div>
                </div>

                <div class="login-modal-error" id="loginModalError" style="display:none;"></div>

                <form id="loginModalForm" method="POST" action="<?= APP_URL ?>/index.php?url=login">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="form-group">
                        <label for="loginModalEmail">Email or Username</label>
                        <div class="input-icon-wrap">
                            <input type="text" id="loginModalEmail" name="email" class="form-control" placeholder="you@example.com or username" autocomplete="username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="loginModalPassword">Password</label>
                        <div class="input-icon-wrap">
                            <input type="password" id="loginModalPassword" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="login-pw-toggle" onclick="(function(b){var i=b.previousElementSibling;var isP=i.type==='password';i.type=isP?'text':'password';b.textContent=isP?'Hide':'Show';})(this)" aria-label="Toggle password visibility">
                                Show
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent btn-block" id="loginModalSubmit">
                        Sign In
                    </button>
                </form>

                <div class="login-modal-footer">
                    <p>Need to verify your email? <a href="<?= APP_URL ?>/index.php?url=verify-email">Resend verification</a></p>
                    <p><a href="<?= APP_URL ?>/index.php?url=forgot-password">Forgot password?</a></p>
                    <div class="login-modal-divider">
                        <span>or</span>
                    </div>
                    <p style="text-align:center;"><a href="<?= APP_URL ?>/index.php?url=admin-login" class="login-admin-link">Continue as administrator</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
