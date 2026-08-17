<?php
/* Dedicated product reviews page — locked height + scroll */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

require_once __DIR__ . '/../../models/Review.php';
$reviewModel = new Review($pdo);
$reviews = $reviewModel->getByProductId($product['id'] ?? 0, 100);
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
$productUrl = APP_URL . '/index.php?url=products/' . (int)$product['id'];
$thumb = !empty($product['image']) ? $product['image'] : 'placeholder.svg';
?>
<style>
body:has(.pd-page) .site-header .main-nav { margin-bottom: 0 !important; }
</style>

<div class="pd-page pd-reviews-page">
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
            <a href="<?= $productUrl ?>"><?= htmlspecialchars($product['name']) ?></a>
            <span class="pd-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="pd-breadcrumb__current" aria-current="page">Reviews</span>
        </nav>

        <div class="pd-reviews-page__head">
            <a class="pd-reviews-page__back" href="<?= $productUrl ?>">← Back to product</a>
            <div class="pd-reviews-page__product">
                <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($thumb) ?>" alt="">
                <div>
                    <h1 class="pd-reviews-page__title">Customer Reviews</h1>
                    <p class="pd-reviews-page__product-name"><?= htmlspecialchars($product['name']) ?></p>
                </div>
            </div>
        </div>

        <div class="pd-reviews-page__layout">
            <aside class="pd-card pd-reviews-page__summary">
                <h2 class="pd-card__title">Customer Rating</h2>
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
                <?php else: ?>
                    <p class="pd-muted">No ratings yet.</p>
                <?php endif; ?>
            </aside>

            <section class="pd-card pd-reviews-page__list-card">
                <div class="pd-reviews-page__list-head">
                    <h2 class="pd-card__title">All Reviews (<?= $reviewCount ?>)</h2>
                </div>

                <?php if (empty($reviews)): ?>
                    <p class="pd-muted">No reviews yet. Be the first to review this product!</p>
                <?php else: ?>
                    <div class="pd-reviews-scroll" tabindex="0" aria-label="Product reviews list">
                        <?php foreach ($reviews as $rv): ?>
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
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
