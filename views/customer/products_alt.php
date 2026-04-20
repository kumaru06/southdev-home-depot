<?php
/* Alternate products listing layout (no hero) */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="section-heading" style="margin-top:1rem;">
        <span class="section-badge"><img src="<?= APP_URL ?>/assets/uploads/images/png-icon/product.png" alt="Products" style="width:17px;height:17px;"> OUR PRODUCTS</span>
        <h2 class="section-title">Everything You Need in <span class="accent-text">One Place</span></h2>
        <p class="section-subtitle">Browse our complete range of premium building materials, fixtures, and finishes.</p>
    </div>

    <div class="category-bar storefront-chips" style="margin-bottom:18px;">
        <a href="<?= APP_URL ?>/index.php?url=products" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">
            <img src="<?= APP_URL ?>/assets/uploads/images/png-icon/tile.png" alt="All Products" style="width:14px;height:14px"> All Products
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
                                <span>No Image</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['category_name'])): ?>
                            <span class="product-category-tag"><?= htmlspecialchars($product['category_name']) ?></span>
                        <?php endif; ?>
                        <?php if ($isOutOfStock): ?>
                            <div class="product-unavailable-overlay">
                                <span>Not Available</span>
                            </div>
                            <span class="product-badge badge-danger">Out of Stock</span>
                        <?php elseif (isset($product['stock']) && $product['stock'] <= 5): ?>
                            <span class="product-badge badge-warning">Low Stock</span>
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> ADD TO CART
                    </button>
                <?php elseif ($isOutOfStock): ?>
                    <button class="btn btn-sm btn-add-cart btn-out-of-stock" disabled>
                        Not Available
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
        <h3>No products found</h3>
        <p>Try adjusting your search or browse a different category.</p>
        <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">View All Products</a>
    </div>
    <?php endif; ?>
</div>

<?php $extraJs = ['cart.js']; ?>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
