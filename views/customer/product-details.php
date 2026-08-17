<?php
/* $pageTitle, $images, $specs, $sizeOptions, $relatedProducts, $relatedRatings set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$inStock = ($product['stock'] ?? 0) > 0;
$images = $images ?? [];
$specs = $specs ?? [];
$sizeOptions = $sizeOptions ?? [];
$relatedProducts = $relatedProducts ?? [];
$relatedRatings = $relatedRatings ?? [];

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
                $parts[] = mb_substr($p, 0, 1) . str_repeat('*', max(0, $len - 1));
            } else {
                $firstChar = mb_substr($p, 0, 1);
                $lastChar  = mb_substr($p, -1);
                $midLen    = max(1, $len - 2);
                $parts[]   = $firstChar . str_repeat('*', $midLen) . $lastChar;
            }
        }
        return implode(' ', $parts);
    }
}

$ratingDist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $r) { $ratingDist[intval($r['rating'])]++; }

$categoryName = $product['category_name'] ?? '';
$skuValue = trim((string)($product['sku'] ?? ''));
$skuDisplay = $skuValue !== '' ? $skuValue : 'N/A';

if (empty($images) && !empty($product['image'])) {
    $images = [['id' => 0, 'filename' => $product['image']]];
}
$mainImage = !empty($images) ? $images[0]['filename'] : ($product['image'] ?: 'placeholder.svg');
$isNew = !empty($product['created_at']) && (time() - strtotime($product['created_at'])) < (30 * 86400);

// Specs table also includes SKU if not already present
$specsTable = $specs;
if ($skuValue !== '' && !isset($specsTable['SKU'])) {
    $specsTable['SKU'] = $skuDisplay;
}
?>
<style>
body:has(.pd-page) .site-header .main-nav { margin-bottom: 0 !important; }
</style>

<div class="pd-page">
    <div class="container">
        <nav class="pd-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= APP_URL ?>/index.php">Home</a>
            <span class="pd-breadcrumb__sep" aria-hidden="true">/</span>
            <a href="<?= APP_URL ?>/index.php?url=products">Products</a>
            <?php if ($categoryName !== ''): ?>
                <span class="pd-breadcrumb__sep" aria-hidden="true">/</span>
                <a href="<?= APP_URL ?>/index.php?url=products&category=<?= (int)$product['category_id'] ?>"><?= htmlspecialchars($categoryName) ?></a>
            <?php endif; ?>
            <span class="pd-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="pd-breadcrumb__current" aria-current="page"><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="pd-grid">
            <!-- Gallery -->
            <div class="pd-gallery<?= $inStock ? '' : ' pd-gallery--oos' ?>" data-pd-gallery>
                <div class="pd-gallery__stage">
                    <?php if ($isNew && $inStock): ?>
                        <span class="pd-gallery__badge">NEW</span>
                    <?php endif; ?>
                    <button type="button" class="pd-gallery__zoom" data-pd-zoom aria-label="Expand image" title="Expand">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/><path d="M11 8v6M8 11h6"/></svg>
                    </button>
                    <?php if ($inStock): ?>
                        <img class="pd-gallery__img" data-pd-main-img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php else: ?>
                        <img class="pd-gallery__img pd-gallery__img--oos" data-pd-main-img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="pd-gallery__oos-overlay"><span>Not Available</span></div>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                <div class="pd-thumbs" role="list">
                    <?php foreach ($images as $idx => $img): ?>
                        <button type="button"
                            class="pd-thumb<?= $idx === 0 ? ' is-active' : '' ?>"
                            data-pd-thumb
                            data-src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($img['filename']) ?>"
                            aria-label="View image <?= $idx + 1 ?>">
                            <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($img['filename']) ?>" alt="">
                            <span class="pd-thumb__num"><?= $idx + 1 ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="pd-info">
                <?php if ($categoryName !== ''): ?>
                    <span class="pd-info__chip"><?= htmlspecialchars($categoryName) ?></span>
                <?php endif; ?>

                <div class="pd-info__header">
                    <h1 class="pd-info__title"><?= htmlspecialchars($product['name']) ?></h1>
                    <?php if ($reviewCount): ?>
                        <a href="#pd-reviews-summary" class="pd-info__rating-link" data-pd-open-reviews>
                            <span class="pd-stars pd-stars--sm">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?= $i <= round($avgRating) ? 'pd-star--filled' : 'pd-star--empty' ?>">★</span>
                                <?php endfor; ?>
                            </span>
                            <span class="pd-info__rating-text"><?= $avgRating ?> · <?= $reviewCount ?> review<?= $reviewCount > 1 ? 's' : '' ?></span>
                        </a>
                    <?php else: ?>
                        <span class="pd-info__rating-text pd-info__rating-text--none">No reviews yet</span>
                    <?php endif; ?>
                </div>

                <div class="pd-price-row">
                    <div class="pd-price">
                        <span class="pd-price__currency">₱</span><span class="pd-price__amount" data-pd-price><?= number_format(!empty($sizeOptions[0]['price']) ? $sizeOptions[0]['price'] : $product['price'], 2) ?></span>
                        <span class="pd-price__unit">/ piece</span>
                    </div>
                    <?php
                        $defaultSize = !empty($sizeOptions) ? $sizeOptions[0] : null;
                        $displayStock = $defaultSize ? (int)$defaultSize['stock'] : (int)$product['stock'];
                        $displayInStock = $displayStock > 0;
                    ?>
                    <?php if ($displayInStock): ?>
                        <span class="pd-stock-badge pd-stock-badge--in" data-pd-stock-badge>
                            <span class="pd-stock-badge__dot" aria-hidden="true"></span>
                            <span data-pd-stock-text><?= $displayStock ?> pcs</span>
                        </span>
                    <?php else: ?>
                        <span class="pd-stock-badge pd-stock-badge--out" data-pd-stock-badge>
                            <span class="pd-stock-badge__dot" aria-hidden="true"></span>
                            <span data-pd-stock-text>Out of stock</span>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($sizeOptions)): ?>
                <div class="pd-sizes" data-pd-sizes>
                    <h3 class="pd-section-label">Choose Your Size</h3>
                    <div class="pd-sizes__grid">
                        <?php foreach ($sizeOptions as $idx => $opt): ?>
                            <?php
                                $optStock = (int)($opt['stock'] ?? 0);
                                $isCurrent = $idx === 0;
                                $label = trim((string)($opt['size_label'] ?? '')) ?: 'Size';
                                $linkedId = !empty($opt['linked_product_id']) ? (int)$opt['linked_product_id'] : null;
                                $optId = !empty($opt['id']) ? (int)$opt['id'] : 0;
                            ?>
                            <?php if ($linkedId && $linkedId !== (int)$product['id']): ?>
                            <a href="<?= APP_URL ?>/index.php?url=products/<?= $linkedId ?>"
                               class="pd-size<?= $optStock <= 0 ? ' is-oos' : '' ?>">
                                <span class="pd-size__label"><?= htmlspecialchars($label) ?></span>
                                <span class="pd-size__price">₱<?= number_format((float)$opt['price'], 2) ?></span>
                                <span class="pd-size__stock"><?= $optStock > 0 ? ($optStock . ' pcs') : 'Out of stock' ?></span>
                            </a>
                            <?php else: ?>
                            <button type="button"
                               class="pd-size<?= $isCurrent ? ' is-selected' : '' ?><?= $optStock <= 0 ? ' is-oos' : '' ?>"
                               data-pd-size
                               data-size-id="<?= $optId ?>"
                               data-price="<?= htmlspecialchars(number_format((float)$opt['price'], 2, '.', '')) ?>"
                               data-stock="<?= $optStock ?>"
                               data-label="<?= htmlspecialchars($label, ENT_QUOTES) ?>">
                                <span class="pd-size__label"><?= htmlspecialchars($label) ?></span>
                                <span class="pd-size__price">₱<?= number_format((float)$opt['price'], 2) ?></span>
                                <span class="pd-size__stock"><?= $optStock > 0 ? ($optStock . ' pcs') : 'Out of stock' ?></span>
                            </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                    $canBuy = isset($_SESSION['user_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER && $displayInStock;
                    $initialMax = $defaultSize ? (int)$defaultSize['stock'] : (int)$product['stock'];
                    $initialSizeId = $defaultSize && !empty($defaultSize['id']) ? (int)$defaultSize['id'] : 0;
                ?>
                <?php if ($canBuy): ?>
                    <div class="pd-actions">
                        <div class="qty-stepper" aria-label="Quantity">
                            <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown(); this.nextElementSibling.dispatchEvent(new Event('change'))" aria-label="Decrease quantity">−</button>
                            <input type="number" id="quantity" value="1" min="1" max="<?= max(1, $initialMax) ?>" class="qty-input" aria-label="Quantity" data-pd-qty>
                            <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp(); this.previousElementSibling.dispatchEvent(new Event('change'))" aria-label="Increase quantity">+</button>
                        </div>
                        <button type="button"
                            class="btn btn-accent btn-lg pd-btn-cart"
                            data-pd-add-cart
                            data-product-id="<?= (int)$product['id'] ?>"
                            data-size-id="<?= $initialSizeId ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            Add to Cart
                        </button>
                    </div>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <div class="pd-actions">
                        <a href="<?= APP_URL ?>/index.php?url=login" class="btn btn-accent btn-lg pd-btn-cart">Sign in to purchase</a>
                    </div>
                <?php endif; ?>

                <div class="pd-trust">
                    <div class="pd-trust__item">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        <span>Fast Delivery</span>
                    </div>
                    <div class="pd-trust__item">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Secure Payment</span>
                    </div>
                    <div class="pd-trust__item">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9"/><polyline points="3 4 3 12 11 12"/></svg>
                        <span>7 Days Return</span>
                    </div>
                </div>

                <a href="<?= APP_URL ?>/index.php?url=products" class="pd-back-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Back to Products
                </a>
            </div>
        </div>

        <!-- Detail band: specs | tabs | reviews summary -->
        <section class="pd-band">
            <div class="pd-card pd-card--specs">
                <h3 class="pd-card__title">Product Specifications</h3>
                    <?php
                      $specSizeLabel = !empty($sizeOptions[0]['size_label'])
                          ? $sizeOptions[0]['size_label']
                          : ($product['size_label'] ?? '');
                      if ($specSizeLabel !== '' && !isset($specsTable['Size'])) {
                          // show in fallback block below when specs empty; if specs exist, inject Size
                      }
                    ?>
                <?php if (!empty($specsTable)): ?>
                <dl class="pd-specs-table">
                    <?php if ($specSizeLabel !== '' && !isset($specsTable['Size'])): ?>
                        <div class="pd-specs-table__row"><dt>Size</dt><dd data-pd-spec-size><?= htmlspecialchars($specSizeLabel) ?></dd></div>
                    <?php endif; ?>
                    <?php foreach ($specsTable as $label => $value): ?>
                        <div class="pd-specs-table__row">
                            <dt><?= htmlspecialchars($label) ?></dt>
                            <dd<?= strtolower($label) === 'size' ? ' data-pd-spec-size' : '' ?>><?= htmlspecialchars($value) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
                <?php else: ?>
                <dl class="pd-specs-table">
                    <div class="pd-specs-table__row"><dt>SKU</dt><dd><?= htmlspecialchars($skuDisplay) ?></dd></div>
                    <div class="pd-specs-table__row"><dt>Category</dt><dd><?= htmlspecialchars($categoryName !== '' ? $categoryName : 'Uncategorized') ?></dd></div>
                    <div class="pd-specs-table__row"><dt>Availability</dt><dd><?= $displayInStock ?? $inStock ? 'Available' : 'Unavailable' ?></dd></div>
                    <?php if ($specSizeLabel !== ''): ?>
                    <div class="pd-specs-table__row"><dt>Size</dt><dd data-pd-spec-size><?= htmlspecialchars($specSizeLabel) ?></dd></div>
                    <?php endif; ?>
                </dl>
                <?php endif; ?>
            </div>

            <div class="pd-card pd-card--tabs" data-pd-tabs>
                <div class="pd-tabs" role="tablist">
                    <button type="button" class="pd-tabs__btn is-active" role="tab" aria-selected="true" data-pd-tab="description">Description</button>
                    <button type="button" class="pd-tabs__btn" role="tab" aria-selected="false" data-pd-tab="specifications">Specifications</button>
                    <button type="button" class="pd-tabs__btn" role="tab" aria-selected="false" data-pd-tab="reviews">Reviews (<?= $reviewCount ?>)</button>
                </div>
                <div class="pd-tab-panel is-active" data-pd-panel="description">
                    <?php if (!empty($product['description'])): ?>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    <?php else: ?>
                        <p class="pd-muted">No description provided for this product yet.</p>
                    <?php endif; ?>
                </div>
                <div class="pd-tab-panel" data-pd-panel="specifications" hidden>
                    <?php if (!empty($specsTable)): ?>
                    <ul class="pd-spec-list">
                        <?php foreach ($specsTable as $label => $value): ?>
                            <li><strong><?= htmlspecialchars($label) ?>:</strong> <?= htmlspecialchars($value) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                        <p class="pd-muted">No detailed specifications yet.</p>
                    <?php endif; ?>
                </div>
                <div class="pd-tab-panel" data-pd-panel="reviews" hidden>
                    <?php if (empty($reviews)): ?>
                        <p class="pd-muted">No reviews yet. Be the first to review this product!</p>
                    <?php else: ?>
                        <div class="pd-tab-reviews" data-pd-review-list>
                            <?php foreach ($reviews as $idx => $rv): ?>
                                <article class="pd-mini-review<?= $idx >= 3 ? ' is-extra' : '' ?>"<?= $idx >= 3 ? ' hidden' : '' ?>>
                                    <div class="pd-mini-review__top">
                                        <strong><?= htmlspecialchars(mask_name($rv['first_name'] ?? '', $rv['last_name'] ?? '')) ?></strong>
                                        <span class="pd-stars pd-stars--sm">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="<?= $i <= intval($rv['rating']) ? 'pd-star--filled' : 'pd-star--empty' ?>">★</span>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <div class="pd-mini-review__date"><?= date('M d, Y', strtotime($rv['created_at'])) ?></div>
                                    <?php if (!empty($rv['comment'])): ?>
                                        <p><?= nl2br(htmlspecialchars($rv['comment'])) ?></p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                            <?php if ($reviewCount > 3): ?>
                                <button type="button" class="btn btn-outline btn-sm pd-reviews-viewall" data-pd-view-all-reviews>
                                    View all <?= $reviewCount ?> reviews
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pd-card pd-card--rating" id="pd-reviews-summary">
                <h3 class="pd-card__title">Customer Rating</h3>
                <?php if ($reviewCount): ?>
                    <div class="pd-rating-hero">
                        <span class="pd-rating-hero__num"><?= $avgRating ?></span>
                        <div>
                            <div class="pd-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?= $i <= round($avgRating) ? 'pd-star--filled' : 'pd-star--empty' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="pd-rating-hero__sub">Based on <?= $reviewCount ?> review<?= $reviewCount > 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                    <div class="pd-reviews__bars">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                            <?php $pct = $reviewCount ? round(($ratingDist[$s] / $reviewCount) * 100) : 0; ?>
                            <div class="pd-reviews__bar-row">
                                <span class="pd-reviews__bar-label"><?= $s ?> ★</span>
                                <div class="pd-reviews__bar-track">
                                    <div class="pd-reviews__bar-fill" style="width:<?= $pct ?>%"></div>
                                </div>
                                <span class="pd-reviews__bar-count"><?= $ratingDist[$s] ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm pd-rating-cta" data-pd-open-reviews>
                        View all reviews
                    </button>
                <?php else: ?>
                    <p class="pd-muted">No ratings yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- You May Also Like -->
        <section class="pd-related" data-pd-related>
            <div class="pd-related__head">
                <h2 class="pd-related__title">You May Also Like</h2>
                <div class="pd-related__actions">
                    <?php if (!empty($relatedProducts)): ?>
                    <div class="pd-related__navs" data-pd-related-navs>
                        <button type="button" class="pd-related__nav pd-related__nav--prev" data-pd-related-prev aria-label="Previous">‹</button>
                        <button type="button" class="pd-related__nav pd-related__nav--next" data-pd-related-next aria-label="Next">›</button>
                    </div>
                    <a class="pd-related__all" href="<?= APP_URL ?>/index.php?url=products&category=<?= (int)$product['category_id'] ?>">View all</a>
                    <?php else: ?>
                    <a class="pd-related__all" href="<?= APP_URL ?>/index.php?url=products">Browse products</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($relatedProducts)): ?>
            <div class="pd-related__track-wrap">
                <div class="pd-related__track" data-pd-related-track>
                    <?php foreach ($relatedProducts as $rel): ?>
                        <?php
                            $relRating = $relatedRatings[$rel['id']] ?? null;
                            $relAvg = $relRating['avg_rating'] ?? null;
                            $relCount = $relRating['review_count'] ?? 0;
                        ?>
                        <a class="pd-related__card" href="<?= APP_URL ?>/index.php?url=products/<?= (int)$rel['id'] ?>">
                            <div class="pd-related__img">
                                <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($rel['image'] ?: 'placeholder.svg') ?>" alt="<?= htmlspecialchars($rel['name']) ?>">
                            </div>
                            <div class="pd-related__body">
                                <h3><?= htmlspecialchars($rel['name']) ?></h3>
                                <div class="pd-related__price">₱<?= number_format((float)$rel['price'], 2) ?></div>
                                <?php if ($relAvg): ?>
                                    <div class="pd-related__rating">
                                        <span class="pd-stars pd-stars--sm">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="<?= $i <= round((float)$relAvg) ? 'pd-star--filled' : 'pd-star--empty' ?>">★</span>
                                            <?php endfor; ?>
                                        </span>
                                        <span>(<?= (int)$relCount ?>)</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="pd-related__empty">
                <p>No other products to suggest yet. Add more products in Admin and they’ll show up here.</p>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<div class="pd-lightbox" data-pd-lightbox hidden>
    <button type="button" class="pd-lightbox__close" data-pd-lightbox-close aria-label="Close">&times;</button>
    <img src="" alt="" data-pd-lightbox-img>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
