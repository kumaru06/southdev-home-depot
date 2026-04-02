<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

// Determine hero images: prefer display1.jpg and display2.jpg if present,
// otherwise fall back to image.png for missing assets.
$heroRel = 'assets/uploads/images/display1.jpg';
$heroFull = ROOT_PATH . '/' . $heroRel;
if (!file_exists($heroFull)) {
    $heroRel = 'assets/uploads/images/image.png';
}

$display1 = 'assets/uploads/images/display1.jpg';
$display2 = 'assets/uploads/images/display2.jpg';
$display1Full = ROOT_PATH . '/' . $display1;
$display2Full = ROOT_PATH . '/' . $display2;
if (!file_exists($display1Full)) {
    $display1 = 'assets/uploads/images/image.png';
}
if (!file_exists($display2Full)) {
    $display2 = 'assets/uploads/images/image.png';
}
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
                        <span><img src="<?= APP_URL ?>/assets/uploads/images/png-icon/tile.png" alt="All Products" style="width:15px;height:15px"> All Products</span>
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
            <!-- Hero / Intro: left column shows display1 + display2 stacked, right column uses the main hero image -->
            <section class="products-hero">
                <div class="products-hero-inner">
                    <div class="hero-left">
                        <img src="<?= APP_URL ?>/<?= $display1 ?>" alt="Display 1" class="hero-thumb">
                        <img src="<?= APP_URL ?>/<?= $display2 ?>" alt="Display 2" class="hero-thumb">
                    </div>
                    <div class="hero-right" style="background-image: url('<?= APP_URL ?>/<?= $heroRel ?>');"></div>
                </div>
                <h1 class="sr-only">Featured</h1>
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

            <!-- Search moved to topbar; storefront toolbar removed to avoid duplication -->

            <?php // Render the products section for any route that begins with 'products' (includes search and subpaths) ?>
            <?php if ((isset($_GET['url']) && strpos($_GET['url'], 'products') === 0) || (isset($currentUrl) && strpos($currentUrl, 'products') === 0)): ?>
            <!-- Section heading -->
            <div class="section-heading">
                <span class="section-badge"><img src="<?= APP_URL ?>/assets/uploads/images/png-icon/product.png" alt="Products" style="width:17px;height:17px;"> OUR PRODUCTS</span>
                <h2 class="section-title">Everything You Need in <span class="accent-text">One Place</span></h2>
                <p class="section-subtitle">Browse our complete range of premium building materials, fixtures, and finishes.</p>
            </div>

            <div class="category-bar storefront-chips">
                <a href="<?= APP_URL ?>/index.php?url=products" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">
                    <img src="<?= APP_URL ?>/assets/uploads/images/png-icon/tile.png" alt="All Products" style="width:14px;height:14px"> All Products
                </a>
                <!-- Category select for quick filtering / mobile users -->
                <div class="category-select" style="margin-left:12px; display:inline-block; vertical-align:middle;">
                    <select id="categorySelect" class="form-control" style="min-width:160px;padding:.45rem;border-radius:6px;border:1px solid var(--border);background:var(--surface);">
                        <option value="">All Categories</option>
                        <?php if (isset($categories)): foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <?php if (isset($categories)): foreach ($categories as $cat): ?>
                    <a href="<?= APP_URL ?>/index.php?url=products&category=<?= $cat['id'] ?>" class="<?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; endif; ?>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function(){
                var sel = document.getElementById('categorySelect');
                if(!sel) return;
                sel.addEventListener('change', function(){
                    var v = this.value;
                    if(v === ''){
                        window.location.href = '<?= APP_URL ?>/index.php?url=products';
                    } else {
                        window.location.href = '<?= APP_URL ?>/index.php?url=products&category=' + encodeURIComponent(v);
                    }
                });
            });
            </script>

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
                            <?php
                                $rating = $productRatings[$product['id']] ?? null;
                                if ($rating && $rating['review_count'] > 0):
                                    $avg = round($rating['avg_rating'], 1);
                            ?>
                            <div class="product-rating">
                                <span class="product-stars">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <?php if ($s <= floor($avg)): ?>
                                            <i data-lucide="star" class="star-filled"></i>
                                        <?php elseif ($s - $avg < 1 && $s - $avg > 0): ?>
                                            <i data-lucide="star-half" class="star-filled"></i>
                                        <?php else: ?>
                                            <i data-lucide="star" class="star-empty"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </span>
                                <span class="rating-text"><?= $avg ?> (<?= $rating['review_count'] ?>)</span>
                            </div>
                            <?php endif; ?>
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

            <?php // Close the conditional that only renders this section when URL is 'products' ?>
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