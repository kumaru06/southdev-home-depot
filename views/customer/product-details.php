<?php
/* $pageTitle, $images, $specs, $sizeOptions, $relatedProducts, $relatedRatings set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$images = $images ?? [];
$specs = $specs ?? [];
$sizeOptions = $sizeOptions ?? [];
$relatedProducts = $relatedProducts ?? [];
$relatedRatings = $relatedRatings ?? [];

$hasSizes = !empty($sizeOptions);
$displayStock = $hasSizes
    ? (int) max(array_map(static function ($o) { return (int)($o['stock'] ?? 0); }, $sizeOptions))
    : (int)($product['stock'] ?? 0);
$inStock = $displayStock > 0;

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
@supports not (-webkit-background-clip: text) {
    .pd-info__title { background: none; color: var(--charcoal); }
}

/* Critical mobile fixes — inline so cache cannot serve stale external CSS */
@media (max-width: 900px) {
    html, body {
        overflow-x: hidden !important;
        max-width: 100% !important;
        overscroll-behavior-x: none;
    }
    .pd-page,
    .pd-page .container,
    .pd-grid,
    .pd-info,
    .pd-band,
    .pd-related {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
        box-sizing: border-box;
    }
    .pd-grid {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) !important;
        gap: 18px !important;
    }
    .pd-gallery {
        max-width: min(100%, 480px) !important;
        margin-inline: auto;
    }
    .pd-gallery__stage {
        width: 100% !important;
        max-width: 100% !important;
        min-height: 0 !important;
        height: auto !important;
        max-height: none !important;
        box-shadow: 0 4px 16px rgba(27,42,74,.08) !important;
    }
    .pd-gallery__img {
        position: relative !important;
        inset: auto !important;
        width: 100% !important;
        max-width: none !important;
        height: auto !important;
        max-height: none !important;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        object-position: center;
    }
    .pd-thumbs-wrap {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        overflow: hidden !important;
    }
    .pd-thumbs-viewport {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-x: contain;
        scrollbar-width: none;
        touch-action: pan-x;
        min-width: 0 !important;
    }
    .pd-thumbs-viewport::-webkit-scrollbar { display: none; }
    .pd-thumb.is-active,
    .pd-thumb:hover {
        transform: none !important;
    }
    .pd-info__title {
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
        hyphens: auto;
    }
    .pd-price-row {
        flex-direction: column !important;
        align-items: stretch !important;
        padding: 14px !important;
    }
    .pd-stock-badge {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        justify-content: center;
        white-space: normal !important;
    }
    .pd-sizes__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 8px !important;
    }
    .pd-size {
        padding: 9px 10px !important;
        gap: 2px !important;
    }
    .pd-size__label { font-size: 11px !important; }
    .pd-size__price { font-size: 12px !important; }
    .pd-size__stock { font-size: 9px !important; }
    .pd-sizes__grid:has(.pd-size:nth-child(7)) {
        max-height: min(260px, 38vh);
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior-y: contain;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
}
@media (hover: none) {
    .pd-size:hover,
    .pd-size.is-selected {
        transform: none !important;
    }
    .pd-gallery:hover .pd-gallery__img:not(.pd-gallery__img--oos) {
        transform: none !important;
    }
}
</style>

<div class="pd-page">
    <div class="container">
        <div class="pd-topbar">
            <a href="<?= APP_URL ?>/index.php?url=products" class="pd-back-link pd-back-link--breadcrumb">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Back to Products
            </a>
        </div>

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
                <?php $thumbCount = count($images); ?>
                <div class="pd-thumbs-wrap" data-pd-thumbs-wrap>
                    <?php if ($thumbCount > 4): ?>
                    <button type="button" class="pd-thumbs-nav" data-pd-thumbs-prev aria-label="Previous images" disabled>&lsaquo;</button>
                    <?php endif; ?>
                    <div class="pd-thumbs-viewport">
                        <div class="pd-thumbs-track" data-pd-thumbs-track role="list">
                            <?php foreach ($images as $idx => $img): ?>
                                <button type="button"
                                    class="pd-thumb<?= $idx === 0 ? ' is-active' : '' ?>"
                                    data-pd-thumb
                                    data-index="<?= $idx ?>"
                                    data-src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($img['filename']) ?>"
                                    aria-label="View image <?= $idx + 1 ?>">
                                    <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($img['filename']) ?>" alt="">
                                    <span class="pd-thumb__num"><?= $idx + 1 ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if ($thumbCount > 4): ?>
                    <button type="button" class="pd-thumbs-nav" data-pd-thumbs-next aria-label="Next images">&rsaquo;</button>
                    <?php endif; ?>
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
                    <div class="pd-info__meta">
                    <?php if ($reviewCount): ?>
                        <a href="<?= APP_URL ?>/index.php?url=products/<?= (int)$product['id'] ?>/reviews" class="pd-info__rating-link">
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
                    <span class="pd-info__sold" title="<?= (int) ($productSoldCount ?? 0) ?> sold"><?= htmlspecialchars(format_sold_count((int) ($productSoldCount ?? 0))) ?> sold</span>
                    </div>
                </div>

                <div class="pd-price-row">
                    <div class="pd-price">
                        <span class="pd-price__currency">₱</span><span class="pd-price__amount" data-pd-price><?= number_format((float) $product['price'], 2) ?></span>
                        <span class="pd-price__unit">/ piece</span>
                    </div>
                    <?php $displayInStock = $inStock; ?>
                    <?php if ($displayInStock): ?>
                        <span class="pd-stock-badge pd-stock-badge--in" data-pd-stock-badge>
                            <span class="pd-stock-badge__dot" aria-hidden="true"></span>
                            <span data-pd-stock-text><?= $hasSizes ? 'Select a size to view stock' : ($displayStock . ' stock') ?></span>
                        </span>
                    <?php else: ?>
                        <span class="pd-stock-badge pd-stock-badge--out" data-pd-stock-badge>
                            <span class="pd-stock-badge__dot" aria-hidden="true"></span>
                            <span data-pd-stock-text>Out of stock</span>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($hasSizes): ?>
                <div class="pd-sizes" data-pd-sizes>
                    <h3 class="pd-section-label">Choose Your Size</h3>
                    <div class="pd-sizes__grid">
                        <?php foreach ($sizeOptions as $opt): ?>
                            <?php
                                $optStock = (int)($opt['stock'] ?? 0);
                                $label = trim((string)($opt['size_label'] ?? '')) ?: 'Size';
                                $linkedId = !empty($opt['linked_product_id']) ? (int)$opt['linked_product_id'] : null;
                                $optId = !empty($opt['id']) ? (int)$opt['id'] : 0;
                            ?>
                            <?php if ($linkedId && $linkedId !== (int)$product['id']): ?>
                            <a href="<?= APP_URL ?>/index.php?url=products/<?= $linkedId ?>"
                               class="pd-size<?= $optStock <= 0 ? ' is-oos' : '' ?>">
                                <span class="pd-size__label"><?= htmlspecialchars($label) ?></span>
                                <span class="pd-size__price">₱<?= number_format((float)$opt['price'], 2) ?></span>
                                <span class="pd-size__stock"><?= $optStock > 0 ? ($optStock . ' stock') : 'Out of stock' ?></span>
                            </a>
                            <?php else: ?>
                            <button type="button"
                               class="pd-size<?= $optStock <= 0 ? ' is-oos' : '' ?>"
                               data-pd-size
                               data-size-id="<?= $optId ?>"
                               data-price="<?= htmlspecialchars(number_format((float)$opt['price'], 2, '.', '')) ?>"
                               data-stock="<?= $optStock ?>"
                               data-label="<?= htmlspecialchars($label, ENT_QUOTES) ?>">
                                <span class="pd-size__label"><?= htmlspecialchars($label) ?></span>
                                <span class="pd-size__price">₱<?= number_format((float)$opt['price'], 2) ?></span>
                                <span class="pd-size__stock"><?= $optStock > 0 ? ($optStock . ' stock') : 'Out of stock' ?></span>
                            </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                    $canBuy = isset($_SESSION['user_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER && $displayInStock;
                    $initialMax = $hasSizes ? 1 : max(1, (int)$product['stock']);
                ?>
                <?php if ($canBuy): ?>
                    <div class="pd-actions">
                        <div class="qty-stepper" aria-label="Quantity">
                            <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown(); this.nextElementSibling.dispatchEvent(new Event('change'))" aria-label="Decrease quantity">−</button>
                            <input type="number" id="quantity" value="1" min="1" max="<?= $initialMax ?>" class="qty-input" aria-label="Quantity" data-pd-qty>
                            <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp(); this.previousElementSibling.dispatchEvent(new Event('change'))" aria-label="Increase quantity">+</button>
                        </div>
                        <button type="button"
                            class="btn btn-accent btn-lg pd-btn-cart"
                            data-pd-add-cart
                            data-product-id="<?= (int)$product['id'] ?>"
                            data-size-id=""
                            <?= $hasSizes ? 'data-requires-size="1"' : '' ?>>
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
                        <span class="pd-trust__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                        </span>
                        <span>Fast Delivery</span>
                    </div>
                    <div class="pd-trust__item">
                        <span class="pd-trust__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                        </span>
                        <span>Secure Payment</span>
                    </div>
                    <div class="pd-trust__item">
                        <span class="pd-trust__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </span>
                        <span>7 Days Return</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Detail band: specs | tabs | reviews summary -->
        <section class="pd-band">
            <div class="pd-card pd-card--specs">
                <h3 class="pd-card__title">Product Specifications</h3>
                    <?php
                      $specSizeLabel = $product['size_label'] ?? '';
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
                    <button type="button" class="pd-tabs__btn" role="tab" aria-selected="false" data-pd-tab="reviews">Reviews (<?= $reviewCount ?>)</button>
                </div>
                <div class="pd-tab-panel is-active" data-pd-panel="description">
                    <?php if (!empty($product['description'])): ?>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    <?php else: ?>
                        <p class="pd-muted">No description provided for this product yet.</p>
                    <?php endif; ?>
                </div>
                <div class="pd-tab-panel" data-pd-panel="reviews" hidden>
                    <?php if (empty($reviews)): ?>
                        <p class="pd-muted">No reviews yet. Be the first to review this product!</p>
                    <?php else: ?>
                        <div class="pd-tab-reviews pd-reviews-scroll pd-reviews-scroll--preview" data-pd-review-list>
                            <?php foreach ($reviews as $idx => $rv): ?>
                                <?php if ($idx >= 3) break; ?>
                                <article class="pd-mini-review">
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
                        </div>
                        <?php if ($reviewCount > 3): ?>
                            <a class="btn btn-outline btn-sm pd-reviews-viewall" href="<?= APP_URL ?>/index.php?url=products/<?= (int)$product['id'] ?>/reviews">
                                View all <?= $reviewCount ?> reviews
                            </a>
                        <?php endif; ?>
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
                    <a class="btn btn-outline btn-sm pd-rating-cta" href="<?= APP_URL ?>/index.php?url=products/<?= (int)$product['id'] ?>/reviews">
                        View all reviews
                    </a>
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
