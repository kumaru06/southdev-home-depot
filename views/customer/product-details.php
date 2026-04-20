<?php
/* $pageTitle set by controller */
$extraJs = ['cart.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
$inStock = ($product['stock'] ?? 0) > 0;

// Load reviews for this product
require_once __DIR__ . '/../../models/Review.php';
$reviewModel = new Review($pdo);
$reviews = $reviewModel->getByProductId($product['id'] ?? 0, 50);
$reviewCount = count($reviews);
$avgRating = 0;
if ($reviewCount) {
    $sum = 0;
    foreach ($reviews as $r) $sum += intval($r['rating']);
    $avgRating = round($sum / $reviewCount, 2);
}

if (!function_exists('mask_name')) {
    function mask_name($first, $last) {
        $parts = [];
        foreach ([$first, $last] as $p) {
            $p = trim($p);
            if ($p === '') continue;
            $len = mb_strlen($p);
            if ($len <= 2) {
                $parts[] = mb_substr($p,0,1) . str_repeat('*', max(0,$len-1));
            } else {
                $firstChar = mb_substr($p,0,1);
                $lastChar  = mb_substr($p,-1);
                $midLen    = max(1, $len - 2);
                $parts[]   = $firstChar . str_repeat('*', $midLen) . $lastChar;
            }
        }
        return implode(' ', $parts);
    }
}

// Rating distribution for summary
$ratingDist = [5=>0,4=>0,3=>0,2=>0,1=>0];
foreach ($reviews as $r) { $ratingDist[intval($r['rating'])]++; }
?>

<div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="<?= APP_URL ?>/index.php?url=products">Products</a>
        <span>/</span>
        <span><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
        <span>/</span>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- ========== Product Detail Grid ========== -->
    <div class="pd-grid">
        <!-- Left: Image Gallery -->
        <div class="pd-gallery">
            <div class="pd-gallery__badge"><?= htmlspecialchars($product['category_name'] ?? 'Product') ?></div>
            <?php if ($inStock): ?>
                <img class="pd-gallery__img" src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php else: ?>
                <img class="pd-gallery__img pd-gallery__img--oos" src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <div class="pd-gallery__oos-overlay">
                    <span>Not Available</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Product Info -->
        <div class="pd-info">
            <!-- Title & Rating Summary -->
            <div class="pd-info__header">
                <h1 class="pd-info__title"><?= htmlspecialchars($product['name']) ?></h1>
                <?php if ($reviewCount): ?>
                    <a href="#pd-reviews" class="pd-info__rating-link">
                        <span class="pd-stars pd-stars--sm">
                            <?php for ($i=1;$i<=5;$i++): ?>
                                <span class="<?= $i <= round($avgRating) ? 'pd-star--filled' : 'pd-star--empty' ?>">★</span>
                            <?php endfor; ?>
                        </span>
                        <span class="pd-info__rating-text"><?= $avgRating ?> (<?= $reviewCount ?> review<?= $reviewCount > 1 ? 's' : '' ?>)</span>
                    </a>
                <?php else: ?>
                    <span class="pd-info__rating-text pd-info__rating-text--none">No reviews yet</span>
                <?php endif; ?>
            </div>

            <!-- Price & Stock Badge -->
            <div class="pd-price-row">
                <span class="pd-price">₱<?= number_format($product['price'], 2) ?></span>
                <?php if ($inStock): ?>
                    <span class="pd-stock-badge pd-stock-badge--in">
                        <?= $product['stock'] ?> in stock
                    </span>
                <?php else: ?>
                    <span class="pd-stock-badge pd-stock-badge--out">
                        Out of stock
                    </span>
                <?php endif; ?>
            </div>

            <hr class="pd-divider">

            <!-- Description -->
            <div class="pd-description">
                <h3 class="pd-section-label">Description</h3>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <hr class="pd-divider">

            <!-- Meta Info -->
            <div class="pd-meta">
                <div class="pd-meta__item">
                    <span class="pd-meta__label">SKU</span>
                    <span class="pd-meta__value"><?= htmlspecialchars($product['sku'] ?? 'N/A') ?></span>
                </div>
                <div class="pd-meta__item">
                    <span class="pd-meta__label">Category</span>
                    <span class="pd-meta__value"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></span>
                </div>
                <div class="pd-meta__item">
                    <span class="pd-meta__label">Availability</span>
                    <?php if ($inStock): ?>
                        <span class="pd-meta__value pd-meta__value--success">Available</span>
                    <?php else: ?>
                        <span class="pd-meta__value pd-meta__value--danger">Unavailable</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add to Cart -->
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER && $inStock): ?>
                <div class="pd-actions">
                    <div class="qty-stepper">
                        <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown(); this.nextElementSibling.dispatchEvent(new Event('change'))">−</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
                        <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp(); this.previousElementSibling.dispatchEvent(new Event('change'))">+</button>
                    </div>
                    <button onclick="addToCart(<?= $product['id'] ?>, document.getElementById('quantity').value)" class="btn btn-accent btn-lg pd-btn-cart"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Add to Cart</button>
                </div>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
                <div class="pd-actions">
                    <a href="<?= APP_URL ?>/index.php?url=login" class="btn btn-accent btn-lg pd-btn-cart">Sign in to purchase</a>
                </div>
            <?php endif; ?>

            <a href="<?= APP_URL ?>/index.php?url=products" class="pd-back-link">&larr; Back to Products</a>
        </div>
    </div>

    <!-- ========== Reviews Section (Full Width) ========== -->
    <section class="pd-reviews" id="pd-reviews">
        <div class="pd-reviews__header">
            <h2 class="pd-reviews__title">Customer Reviews</h2>
            <?php if ($reviewCount): ?>
                <span class="pd-reviews__count"><?= $reviewCount ?> review<?= $reviewCount > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>

        <?php if ($reviewCount): ?>
        <!-- Rating Summary Bar -->
        <div class="pd-reviews__summary">
            <div class="pd-reviews__avg">
                <span class="pd-reviews__avg-num"><?= $avgRating ?></span>
                <div>
                    <div class="pd-stars">
                        <?php for ($i=1;$i<=5;$i++): ?>
                            <span class="<?= $i <= round($avgRating) ? 'pd-star--filled' : 'pd-star--empty' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="pd-reviews__avg-sub">Based on <?= $reviewCount ?> review<?= $reviewCount > 1 ? 's' : '' ?></span>
                </div>
            </div>
            <div class="pd-reviews__bars">
                <?php for ($s=5; $s>=1; $s--): ?>
                    <?php $pct = $reviewCount ? round(($ratingDist[$s] / $reviewCount) * 100) : 0; ?>
                    <div class="pd-reviews__bar-row">
                        <span class="pd-reviews__bar-label"><?= $s ?> <span class="pd-star--filled">★</span></span>
                        <div class="pd-reviews__bar-track">
                            <div class="pd-reviews__bar-fill" style="width:<?= $pct ?>%"></div>
                        </div>
                        <span class="pd-reviews__bar-count"><?= $ratingDist[$s] ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Review Cards -->
        <div class="pd-reviews__list">
            <?php if (empty($reviews)): ?>
                <div class="pd-reviews__empty">
                    <p>No reviews yet. Be the first to review this product!</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $rv): ?>
                    <div class="pd-review-card">
                        <div class="pd-review-card__top">
                            <div class="pd-review-card__author">
                                <div class="pd-review-card__avatar">
                                    <?= htmlspecialchars(mb_substr($rv['first_name'] ?? '',0,1) . mb_substr($rv['last_name'] ?? '',0,1)) ?>
                                </div>
                                <div>
                                    <div class="pd-review-card__name"><?= htmlspecialchars(mask_name($rv['first_name'] ?? '', $rv['last_name'] ?? '')) ?></div>
                                    <div class="pd-review-card__date"><?= date('M d, Y', strtotime($rv['created_at'])) ?></div>
                                </div>
                            </div>
                            <div class="pd-stars">
                                <?php for ($i=1;$i<=5;$i++): ?>
                                    <span class="<?= $i <= intval($rv['rating']) ? 'pd-star--filled' : 'pd-star--empty' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if (!empty($rv['comment'])): ?>
                            <p class="pd-review-card__comment"><?= nl2br(htmlspecialchars($rv['comment'])) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
