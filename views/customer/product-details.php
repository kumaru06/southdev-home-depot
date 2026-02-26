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
            <img src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="product-detail-info">
            <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-detail-price">₱<?= number_format($product['price'], 2) ?></div>

            <p class="product-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <div class="product-meta">
                <div class="meta-item">
                    <i data-lucide="hash"></i>
                    <span>SKU: <?= htmlspecialchars($product['sku'] ?? 'N/A') ?></span>
                </div>
                <div class="meta-item">
                    <i data-lucide="package"></i>
                    <?php if ($inStock): ?>
                        <span class="text-success"><?= $product['stock'] ?> in stock</span>
                    <?php else: ?>
                        <span class="text-danger">Out of stock</span>
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
