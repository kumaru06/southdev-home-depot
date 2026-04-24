<?php
/**
 * SouthDev Home Depot – Footer Include
 */
?>

    <!-- Customer Footer (shown on storefront pages) -->
    <?php if (!isset($isAdmin) || !$isAdmin): ?>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col footer-brand">
                    <h4><?= APP_NAME ?></h4>
                    <p class="footer-tagline"><?= APP_TAGLINE ?></p>
                    <p class="footer-desc">Your trusted partner for premium building materials, fixtures, and interior finishes. Quality you can count on.</p>
                </div>
                <div class="footer-col">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="<?= APP_URL ?>/index.php?url=products">Shop Products</a></li>
                        <li><a href="<?= APP_URL ?>/index.php?url=cart">Shopping Cart</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Contact</h5>
                    <ul class="footer-contact-list">
                        <li><?= APP_LOCATION ?></li>
                        <li>+63 (939) 939 8250</li>
                        <li>southdevhomedepo2020@gmail.com</li>
                        <li>Mon–Sat: 8:00 AM – 5:00 PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
                <p class="footer-credit">Developer: <a href="https://myportfolio-50yb0wbo2-kumaru06s-projects.vercel.app/" target="_blank" rel="noopener noreferrer">Mark Andrey Perez</a></p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <?php if (!isset($isAdmin) || !$isAdmin): ?>
    <style>
        @media (max-width: 900px) {
            body:not(.admin-layout) {
                overflow-x: hidden;
            }

            .site-header .topbar-inner {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: center;
                gap: 12px;
            }

            .site-header .brand {
                min-width: 0;
                max-width: calc(100vw - 84px);
            }

            .site-header .brand .logo-text {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .site-header .mobile-toggle {
                display: inline-flex;
                justify-self: end;
                flex-shrink: 0;
            }

            .site-header .main-nav .container {
                width: 100%;
                max-width: none;
            }

            .mobile-nav-extras {
                flex-direction: column;
            }

            .mobile-nav-extras a {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .home-shell,
            .about-page,
            .inspire-page,
            .collections-page,
            .gallery-page,
            .support-page,
            .faq-page,
            .storefront-shell {
                overflow-x: clip;
            }

            .home-hero,
            .about-hero,
            .support-hero,
            .loc-hero,
            .faq-hero {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .home-hero-inner,
            .home-mosaic-wrap,
            .home-polish,
            .collection-panel-wrap,
            .gallery-card-body,
            .support-content,
            .loc-map-header,
            .loc-directions,
            .faq-help {
                grid-template-columns: 1fr !important;
                flex-direction: column !important;
            }

            .home-hero-actions,
            .collection-actions,
            .gallery-card-actions,
            .support-actions {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: .75rem !important;
            }

            .home-hero-actions .btn,
            .collection-actions .btn,
            .gallery-card-actions .btn,
            .support-actions .support-btn,
            .faq-help a {
                width: 100%;
            }

            .home-hero-meta,
            .home-mini-grid,
            .home-signature-grid,
            .collections-metrics,
            .inspire-grid,
            .gallery-collection-grid {
                grid-template-columns: 1fr !important;
            }

            .collections-shell,
            .gallery-shell,
            .inspire-shell,
            .faq-shell,
            .inquiry-form-section,
            .loc-map-section,
            .loc-cards {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .collections-nav,
            .gallery-chip-row,
            .category-bar {
                display: flex !important;
                flex-wrap: nowrap !important;
                overflow-x: auto;
                gap: .6rem;
                padding-bottom: .25rem;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }

            .collections-nav::-webkit-scrollbar,
            .gallery-chip-row::-webkit-scrollbar,
            .category-bar::-webkit-scrollbar {
                height: 4px;
            }

            .collections-nav > *,
            .gallery-chip-row > *,
            .category-bar > * {
                flex: 0 0 auto;
            }

            .storefront-shell {
                gap: 1rem !important;
            }

            .storefront-sidebar {
                display: block;
                order: 2;
            }

            .storefront-main {
                order: 1;
            }

            .storefront-sidebar .sidebar-card {
                padding: 14px;
            }

            .products-hero {
                margin-left: 0 !important;
                margin-right: 0 !important;
                border-radius: 18px !important;
            }

            .products-hero-inner {
                display: flex !important;
                flex-direction: column !important;
                gap: .75rem !important;
                padding: .75rem !important;
            }

            .hero-left {
                flex-direction: row !important;
                gap: .75rem !important;
            }

            .hero-left .hero-thumb {
                flex: 1 1 0;
                height: 120px !important;
            }

            .hero-right {
                min-height: 220px !important;
            }

            .stats-strip {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .75rem !important;
                align-items: start;
            }

            .stat-item {
                min-width: 0;
            }

            .section-heading,
            .about-section-copy,
            .about-values-header {
                text-align: left !important;
            }

            .category-select {
                margin-left: 0 !important;
            }

            .inspire-hero-content {
                padding: 1.4rem !important;
                text-align: left;
            }

            .inspire-slider-nav {
                position: static;
                justify-content: flex-start;
                padding: .9rem 1rem 0;
            }

            .collection-panel-wrap,
            .gallery-card-body,
            .gallery-hero,
            .loc-directions,
            .faq-help,
            .support-content {
                gap: 1rem !important;
            }

            .collection-showcase-frame,
            .gallery-hero-visual,
            .gallery-card-media {
                min-height: auto !important;
            }

            .collection-panel-media,
            .gallery-hero-visual,
            .home-polish-media {
                order: -1;
            }

            .gallery-thumb-row {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .loc-cards {
                grid-template-columns: 1fr !important;
            }

            .loc-map-header span {
                margin-left: 0 !important;
                width: 100%;
                text-align: center;
            }

            .support-content,
            .inquiry-form-grid {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 480px) {
            .site-header .brand {
                max-width: calc(100vw - 74px);
                gap: 8px;
            }

            .site-header .brand .logo-text {
                font-size: 13px !important;
                letter-spacing: .3px !important;
            }

            .site-header .brand .logo-icon {
                width: 32px;
                height: 32px;
                border-radius: 8px;
            }

            .site-header .topbar {
                padding: 10px 0 !important;
            }

            .site-header .topbar-inner {
                gap: 10px;
                min-height: 48px;
            }

            .site-header .main-nav .container,
            .collections-shell,
            .gallery-shell,
            .inspire-shell,
            .faq-shell,
            .inquiry-form-section,
            .loc-map-section,
            .loc-cards {
                padding-left: .85rem !important;
                padding-right: .85rem !important;
            }

            .products-hero-inner {
                padding: .65rem !important;
            }

            .hero-left .hero-thumb {
                height: 92px !important;
            }

            .hero-right {
                min-height: 180px !important;
            }

            .stats-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .inspire-hero-content,
            .collection-panel-copy,
            .collection-panel-media,
            .gallery-card-panel,
            .support-card,
            .inquiry-form-shell,
            .faq-panel,
            .loc-card {
                padding: 1rem !important;
            }

            .collections-nav button,
            .gallery-chip-row button,
            .category-bar a {
                min-height: 42px;
                font-size: 12px !important;
            }

            .gallery-thumb-row {
                grid-template-columns: 1fr 1fr !important;
                gap: .55rem !important;
            }

            .loc-directions a,
            .support-actions .support-btn,
            .collection-actions .btn,
            .gallery-card-actions .btn,
            .faq-help a {
                min-height: 44px;
                justify-content: center;
            }
        }
    </style>
    <?php endif; ?>

    <!-- Admin Footer (shown on admin/staff pages) -->
    <?php if (isset($isAdmin) && $isAdmin): ?>
    <footer class="admin-footer">
        <span>&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; <?= APP_LOCATION ?></span>
        <span>Developer: <a href="https://myportfolio-50yb0wbo2-kumaru06s-projects.vercel.app/" target="_blank" rel="noopener noreferrer">Mark Andrey Perez</a></span>
    </footer>
    <?php endif; ?>

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    </script>

    <!-- Scripts -->
    <?php
        if (!isset($asset_v) || !is_callable($asset_v)) {
            $asset_v = function (string $relativePath) {
                $fullPath = ROOT_PATH . '/' . ltrim($relativePath, '/');
                return file_exists($fullPath) ? filemtime($fullPath) : APP_VERSION;
            };
        }
    ?>
    <script src="<?= APP_URL ?>/assets/js/main.js?v=<?= $asset_v('assets/js/main.js') ?>"></script>
    <script src="<?= APP_URL ?>/assets/js/validation.js?v=<?= $asset_v('assets/js/validation.js') ?>"></script>
    <?php if (isset($extraJs) && is_array($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= APP_URL ?>/assets/js/<?= htmlspecialchars($js) ?>?v=<?= $asset_v('assets/js/' . $js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
