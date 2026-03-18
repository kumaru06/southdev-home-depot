<?php
/* Alternate products listing layout (no hero) */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="section-heading" style="margin-top:1rem;">
        <span class="section-badge"><i data-lucide="package" style="width:13px;height:13px;"></i> OUR PRODUCTS</span>
        <h2 class="section-title">Everything You Need in <span class="accent-text">One Place</span></h2>
        <p class="section-subtitle">Browse our complete range of premium building materials, fixtures, and finishes.</p>
    </div>

    <div class="category-bar storefront-chips" style="margin-bottom:18px;">
        <a href="<?= APP_URL ?>/index.php?url=products" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">
            <i data-lucide="grid-3x3" style="width:14px;height:14px"></i> All Products
        </a>
        <?php if (isset($categories)): foreach ($categories as $cat): ?>
            <a href="<?= APP_URL ?>/index.php?url=products&category=<?= $cat['id'] ?>" class="<?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; endif; ?>
    </div>

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
</div>

<?php $extraJs = ['cart.js']; ?>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
