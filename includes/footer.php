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
