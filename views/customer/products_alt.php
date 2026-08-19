<?php
/* Alternate products listing layout (no hero) */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="section-heading" style="margin-top:1rem;">
        <span class="section-badge">
            <svg class="section-badge-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                <path d="M4 8.25L12 3.75l8 4.5v9l-8 4.5-8-4.5v-9z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                <path d="M4 8.25L12 12.75l8-4.5M12 12.75v9" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
            </svg>
            OUR PRODUCTS
        </span>
        <h2 class="section-title">Everything You Need in <span class="accent-text">One Place</span></h2>
        <p class="section-subtitle">Browse our complete range of premium building materials, fixtures, and finishes.</p>
    </div>

    <div class="category-bar storefront-chips" style="margin-bottom:18px;">
        <a href="<?= APP_URL ?>/index.php?url=products" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">
            <svg class="chip-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><rect x="2.75" y="2.75" width="8" height="8" rx="1.25" stroke="currentColor" stroke-width="1.75"/><rect x="13.25" y="2.75" width="8" height="8" rx="1.25" stroke="currentColor" stroke-width="1.75"/><rect x="2.75" y="13.25" width="8" height="8" rx="1.25" stroke="currentColor" stroke-width="1.75"/><rect x="13.25" y="13.25" width="8" height="8" rx="1.25" stroke="currentColor" stroke-width="1.75"/></svg><span>All Products</span>
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
            <?php
                $displayStock = (int) ($product['display_stock'] ?? $product['stock'] ?? 0);
                $hasSizes = !empty($product['has_sizes']);
                $isOutOfStock = $displayStock <= 0;
                $lowStockThreshold = Inventory::effectiveReorderLevel((float) ($product['price'] ?? 0));
                $isLowStock = !$isOutOfStock && $displayStock <= $lowStockThreshold;
                $isNewProduct = is_new_product($product['created_at'] ?? null);
            ?>
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
                        <?php if ($isNewProduct): ?>
                            <span class="product-new-tag">New</span>
                        <?php endif; ?>
                        <?php if ($isOutOfStock): ?>
                            <div class="product-unavailable-overlay">
                                <span>Not Available</span>
                            </div>
                            <span class="product-badge badge-danger">Out of Stock</span>
                        <?php elseif ($isLowStock): ?>
                            <span class="product-badge badge-warning">Low Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <?php if (!empty($product['category_name'])): ?>
                            <span class="product-category-pill"><?= htmlspecialchars($product['category_name']) ?></span>
                        <?php endif; ?>
                        <h3 class="product-name" title="<?= htmlspecialchars($product['name']) ?>">
                            <span class="product-name-viewport">
                                <span class="product-name-track">
                                    <span class="product-name-text"><?= htmlspecialchars($product['name']) ?></span>
                                </span>
                            </span>
                        </h3>
                        <?php
                            $rating = $productRatings[$product['id']] ?? null;
                            $soldCount = (int) ($productSold[$product['id']] ?? 0);
                            $avg = round((float) ($rating['avg_rating'] ?? 0), 1);
                            $reviewCount = (int) ($rating['review_count'] ?? 0);
                        ?>
                        <div class="product-card-meta">
                            <div class="product-rating">
                                <span class="product-stars" aria-label="<?= $avg ?> out of 5">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <?php
                                            $fill = 0;
                                            if ($avg >= $s) {
                                                $fill = 1;
                                            } elseif ($avg > $s - 1) {
                                                $fill = $avg - ($s - 1);
                                            }
                                        ?>
                                        <span class="product-star<?= $fill >= 1 ? ' is-filled' : ($fill > 0 ? ' is-half' : '') ?>">★</span>
                                    <?php endfor; ?>
                                </span>
                                <span class="rating-text"><?= $reviewCount > 0 ? number_format($avg, 1) : 'New' ?></span>
                            </div>
                            <span class="product-sold" title="<?= (int) $soldCount ?> sold"><?= htmlspecialchars(format_sold_count($soldCount)) ?> sold</span>
                        </div>
                        <?php if (!empty($product['description'])): ?>
                            <p class="product-desc-preview"><?= htmlspecialchars($product['description']) ?></p>
                        <?php else: ?>
                            <p class="product-desc-preview product-desc-preview--empty">&nbsp;</p>
                        <?php endif; ?>
                        <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                    </div>
                </a>
                <?php if ($isOutOfStock): ?>
                    <span class="btn btn-sm btn-add-cart btn-out-of-stock">Not Available</span>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/index.php?url=products/<?= (int) $product['id'] ?>" class="btn btn-accent btn-sm btn-add-cart">View and Add</a>
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

<?php $extraJs = ['cart.js', 'product-name-marquee.js']; ?>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
