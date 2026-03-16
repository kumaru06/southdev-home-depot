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
                <div class="sidebar-title">
                    <i data-lucide="layout-grid" style="width:16px;height:16px;opacity:.5"></i>
                    Explore
                </div>
                <nav class="sidebar-nav">
                    <a href="<?= APP_URL ?>/index.php?url=products" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">
                        <span><i data-lucide="layers" style="width:15px;height:15px"></i> All Products</span>
                        <?php if (!empty($products) && !isset($_GET['category'])): ?>
                            <span class="sidebar-count"><?= count($products) ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isset($categories)): foreach ($categories as $cat): ?>
                        <a href="<?= APP_URL ?>/index.php?url=products&category=<?= $cat['id'] ?>" class="<?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : '' ?>">
                            <span><i data-lucide="tag" style="width:14px;height:14px"></i> <?= htmlspecialchars($cat['name']) ?></span>
                        </a>
                    <?php endforeach; endif; ?>
                </nav>
            </div>

            <div class="sidebar-footer-link">
                <i data-lucide="help-circle" style="width:13px;height:13px"></i>
                <a href="<?= APP_URL ?>/index.php?url=profile">Help Center</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="storefront-main">
            <!-- Featured Tiles Banner -->
            <!-- Hero / Intro (uses image.png) -->
            <section class="products-hero" style="background-image: url('<?= APP_URL ?>/assets/uploads/images/image.png'); background-size: cover; background-position: center;">
                <div style="max-width:1100px;margin:0 auto;padding:3.5rem 2rem;display:flex;align-items:center;justify-content:space-between;gap:2rem;width:100%;">
                    <div style="flex:1;max-width:600px;">
                        <span class="hero-badge"><span style="color:var(--accent);font-size:10px;">&#9632;</span> PREMIUM BUILDING MATERIALS</span>
                        <h1>Build Your<br><span class="accent-text">Dream Space</span><br>With Us</h1>
                        <p style="margin:0 0 1.5rem;">From flooring to structural materials, bathroom fixtures to interior finishes — everything you need to create stunning spaces, all in one place.</p>
                        <button class="btn btn-accent btn-lg btn-explore" style="padding:.75rem 2rem;font-size:.95rem;border-radius:999px;display:inline-flex;align-items:center;gap:8px;">
                            Explore Products <i data-lucide="arrow-right" style="width:16px;height:16px"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Stats strip -->
            <div class="stats-strip">
                <div class="stat-item">
                    <span class="stat-number"><?= isset($products) ? count($products) : '500' ?>+</span>
                    <span class="stat-label">PRODUCTS</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= isset($categories) ? count($categories) : '5' ?></span>
                    <span class="stat-label">CATEGORIES</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">QUALITY</span>
                </div>
            </div>

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

            <!-- Section heading -->
            <div class="section-heading">
                <span class="section-badge"><i data-lucide="package" style="width:13px;height:13px;"></i> OUR PRODUCTS</span>
                <h2 class="section-title">Everything You Need in <span class="accent-text">One Place</span></h2>
                <p class="section-subtitle">Browse our complete range of premium building materials, fixtures, and finishes.</p>
            </div>

            <div class="category-bar storefront-chips">
                <a href="<?= APP_URL ?>/index.php?url=products" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">
                    <i data-lucide="grid-3x3" style="width:14px;height:14px"></i> All Products
                </a>
                <?php if (isset($categories)): foreach ($categories as $cat): ?>
                    <a href="<?= APP_URL ?>/index.php?url=products&category=<?= $cat['id'] ?>" class="<?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; endif; ?>
            </div>

            <!-- Product Grid -->
            <?php if (!empty($products)): ?>
        <div id="product-list" class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php $isOutOfStock = isset($product['stock']) && $product['stock'] <= 0; ?>
                <div class="product-card <?= $isOutOfStock ? 'product-card--unavailable' : '' ?>">
                    <a href="<?= APP_URL ?>/index.php?url=products/<?= $product['id'] ?>">
                        <div class="product-img-wrap">
                            <?php if (!empty($product['image']) && file_exists(ROOT_PATH . '/assets/uploads/' . $product['image'])): ?>
                                <img src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="product-no-image">
                                    <i data-lucide="image" class="no-img-icon"></i>
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['category_name'])): ?>
                                <span class="product-category-tag"><?= htmlspecialchars($product['category_name']) ?></span>
                            <?php endif; ?>
                            <?php if ($isOutOfStock): ?>
                                <div class="product-unavailable-overlay">
                                    <i data-lucide="x-circle" style="width:28px;height:28px;margin-bottom:4px;"></i>
                                    <span>Not Available</span>
                                </div>
                                <span class="product-badge badge-danger"><i data-lucide="alert-circle" style="width:11px;height:11px"></i> Out of Stock</span>
                            <?php elseif (isset($product['stock']) && $product['stock'] <= 5): ?>
                                <span class="product-badge badge-warning"><i data-lucide="alert-triangle" style="width:11px;height:11px"></i> Low Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <?php if (!empty($product['description'])): ?>
                                <p class="product-desc-preview"><?= htmlspecialchars(mb_strimwidth($product['description'], 0, 80, '…')) ?></p>
                            <?php endif; ?>
                            <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                        </div>
                    </a>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER && !$isOutOfStock): ?>
                        <button class="btn btn-accent btn-sm btn-add-cart" onclick="addToCart(<?= $product['id'] ?>, 1)">
                            <i data-lucide="shopping-cart"></i> Add to Cart
                        </button>
                    <?php elseif ($isOutOfStock): ?>
                        <button class="btn btn-sm btn-add-cart btn-out-of-stock" disabled>
                            <i data-lucide="x-circle"></i> Not Available
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
<script>
// Smooth scroll when clicking Explore Products buttons
document.addEventListener('DOMContentLoaded', function(){
    function scrollToProducts(e){
        e.preventDefault();
        var target = document.getElementById('product-list');
        if(target){
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    var btns = document.querySelectorAll('.btn-explore');
    btns.forEach(function(b){ b.addEventListener('click', scrollToProducts); });
});
</script>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
