<?php
/* $pageTitle set by controller */
$extraJs = ['cart.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
$inStock = ($product['stock'] ?? 0) > 0;
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="<?= APP_URL ?>/index.php?url=products">Products</a>
        <i class="lucide-chevron-right"></i>
        <span><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
        <i class="lucide-chevron-right"></i>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <div class="product-detail">
        <div class="product-detail-img">
            <?php if ($inStock): ?>
            <img src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php else: ?>
            <div style="position:relative;">
                <img src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="opacity:.4;">
                <div class="product-detail-unavailable-badge">
                    <i data-lucide="x-circle" style="width:32px;height:32px;margin-bottom:6px;"></i>
                    <span>Not Available</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="product-detail-info">
            <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-detail-price">₱<?= number_format($product['price'], 2) ?></div>

            <p class="product-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <?php
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
                        $lastChar = mb_substr($p,-1);
                        $midLen = max(1, $len - 2);
                        // keep at most 3 visible stars pattern by showing some letters if short
                        $parts[] = $firstChar . str_repeat('*', $midLen) . $lastChar;
                    }
                }
                return implode(' ', $parts);
            }
            ?>

            <div class="product-reviews" style="margin-top:18px;padding-top:12px;border-top:1px dashed var(--border);">
                <h3 style="margin:0 0 8px 0;">Reviews <?php if($reviewCount): ?> — <?= htmlspecialchars($avgRating) ?>/5 (<?= $reviewCount ?>)<?php endif; ?></h3>
                <?php if (empty($reviews)): ?>
                    <p class="text-muted">Be the first to review this product.</p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <?php foreach ($reviews as $rv): ?>
                            <div style="background:var(--white);padding:12px;border-radius:8px;box-shadow:var(--shadow-sm);">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <div style="width:40px;height:40px;border-radius:999px;background:#eef2f7;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--text-primary);">
                                            <?= htmlspecialchars(mb_substr($rv['first_name'] ?? '',0,1) . mb_substr($rv['last_name'] ?? '',0,1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:700;"><?= htmlspecialchars(mask_name($rv['first_name'] ?? '', $rv['last_name'] ?? '')) ?></div>
                                            <div style="font-size:13px;color:var(--text-muted)"><?= date('M d, Y', strtotime($rv['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    <div style="font-size:16px;color:var(--accent);font-weight:700">
                                        <?php for ($i=1;$i<=5;$i++): ?>
                                            <?php if ($i <= intval($rv['rating'])): ?><span>★</span><?php else: ?><span style="color:#e6e6e6">★</span><?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php if (!empty($rv['comment'])): ?>
                                    <p style="margin-top:8px;color:var(--text-primary)"><?= nl2br(htmlspecialchars($rv['comment'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-meta">
                <div class="meta-item">
                    <i data-lucide="hash"></i>
                    <span>SKU: <?= htmlspecialchars($product['sku'] ?? 'N/A') ?></span>
                </div>
                <div class="meta-item">
                    <i data-lucide="layers"></i>
                    <span>Category: <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></span>
                </div>
                <div class="meta-item">
                    <i data-lucide="package"></i>
                    <?php if ($inStock): ?>
                        <span class="text-success"><?= $product['stock'] ?> in stock</span>
                    <?php else: ?>
                        <span class="text-danger">Out of stock — Not Available</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['role_id'] == ROLE_CUSTOMER && $inStock): ?>
                <div class="product-actions">
                    <div class="qty-stepper">
                        <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown(); this.nextElementSibling.dispatchEvent(new Event('change'))">−</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
                        <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp(); this.previousElementSibling.dispatchEvent(new Event('change'))">+</button>
                    </div>
                    <button onclick="addToCart(<?= $product['id'] ?>, document.getElementById('quantity').value)" class="btn btn-accent btn-lg">
                        <i data-lucide="shopping-cart"></i> Add to Cart
                    </button>
                </div>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
                <a href="<?= APP_URL ?>/index.php?url=login" class="btn btn-accent btn-lg">
                    <i data-lucide="log-in"></i> Sign in to purchase
                </a>
            <?php endif; ?>

            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-outline" style="margin-top: 12px;">
                <i data-lucide="arrow-left"></i> Back to Products
            </a>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
