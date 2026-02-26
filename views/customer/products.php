<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="storefront-shell">
        <!-- Left Sidebar -->
        <aside class="storefront-sidebar" aria-label="Browse categories">
            <div class="sidebar-card">
                <div class="sidebar-title">Explore</div>
                <nav class="sidebar-nav">
                    <a href="<?= APP_URL ?>/index.php?url=products" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">
                        <span>All Products</span>
                    </a>
                    <?php if (isset($categories)): foreach ($categories as $cat): ?>
                        <a href="<?= APP_URL ?>/index.php?url=products&category=<?= $cat['id'] ?>" class="<?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : '' ?>">
                            <span><?= htmlspecialchars($cat['name']) ?></span>
                        </a>
                    <?php endforeach; endif; ?>
                </nav>
            </div>

            <div class="sidebar-footer-link">
                <a href="<?= APP_URL ?>/index.php?url=profile">Help Center</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="storefront-main">
            <div class="storefront-toolbar">
                <form action="<?= APP_URL ?>/index.php" method="GET" class="search-form storefront-search">
                    <input type="hidden" name="url" value="products/search">
                    <div class="input-icon-wrap">
                        <i data-lucide="search" class="input-icon"></i>
                        <input type="text" name="q" class="form-control" placeholder="Search for items, brands, materials…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-accent">Search</button>
                </form>
            </div>

            <div class="category-bar storefront-chips">
                <a href="<?= APP_URL ?>/index.php?url=products" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">All</a>
                <?php if (isset($categories)): foreach ($categories as $cat): ?>
                    <a href="<?= APP_URL ?>/index.php?url=products&category=<?= $cat['id'] ?>" class="<?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; endif; ?>
            </div>

            <!-- Product Grid -->
            <?php if (!empty($products)): ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="<?= APP_URL ?>/index.php?url=products/<?= $product['id'] ?>">
                        <div class="product-img-wrap">
                            <img src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                            <?php if (isset($product['stock']) && $product['stock'] <= 0): ?>
                                <span class="product-badge badge-danger">Out of Stock</span>
                            <?php elseif (isset($product['stock']) && $product['stock'] <= 5): ?>
                                <span class="product-badge badge-warning">Low Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                        </div>
                    </a>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER && ($product['stock'] ?? 0) > 0): ?>
                        <button class="btn btn-accent btn-sm btn-add-cart" onclick="addToCart(<?= $product['id'] ?>, 1)">
                            <i data-lucide="shopping-cart"></i> Add to Cart
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

            <!-- Pagination -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= APP_URL ?>/index.php?url=products&page=<?= $page - 1 ?><?= isset($_GET['category']) ? '&category=' . $_GET['category'] : '' ?>" class="btn btn-outline">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= APP_URL ?>/index.php?url=products&page=<?= $i ?><?= isset($_GET['category']) ? '&category=' . $_GET['category'] : '' ?>" class="btn <?= ($page ?? 1) == $i ? 'btn-accent' : 'btn-outline' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= APP_URL ?>/index.php?url=products&page=<?= $page + 1 ?><?= isset($_GET['category']) ? '&category=' . $_GET['category'] : '' ?>" class="btn btn-outline">Next &raquo;</a>
                <?php endif; ?>
        </div>
            <?php endif; ?>
            <?php else: ?>
        <div class="empty-state">
            <i data-lucide="package-x" class="empty-icon"></i>
            <h3>No products found</h3>
            <p>Try adjusting your search or browse a different category.</p>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">View All Products</a>
        </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php $extraJs = ['cart.js']; ?>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
