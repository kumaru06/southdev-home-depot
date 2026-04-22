<?php
/* $pageTitle, $extraCss set by controller */
$extraJs = ['cart.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="page-heading-row">
        <h1 class="page-heading">Shopping Cart</h1>
        <?php if (!empty($cartItems)): ?>
            <span class="page-heading-badge"><?= count($cartItems) ?> item<?= count($cartItems) > 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($cartItems)): ?>
        <div class="cart-layout">
            <div class="cart-items-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $idx => $item): ?>
                            <tr class="cart-row" style="animation-delay: <?= $idx * 0.04 ?>s">
                                <td data-label="Product">
                                    <div class="cart-product">
                                        <?php if ($item['image']): ?>
                                            <img src="<?= APP_URL ?>/assets/uploads/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="cart-thumb">
                                        <?php else: ?>
                                            <div class="cart-thumb cart-thumb-placeholder"><span style="font-size:11px;font-weight:700;color:var(--text-muted);">N/A</span></div>
                                        <?php endif; ?>
                                        <div class="cart-product-info">
                                            <span class="cart-product-name"><?= htmlspecialchars($item['product_name']) ?></span>
                                            <span class="cart-product-unit">₱<?= number_format($item['price'], 2) ?> each</span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Price"><span class="cart-price">₱<?= number_format($item['price'], 2) ?></span></td>
                                <td data-label="Quantity">
                                    <div class="qty-stepper" data-cart-id="<?= $item['id'] ?>">
                                        <button class="qty-btn qty-minus">−</button>
                                        <input type="number" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                        <button class="qty-btn qty-plus">+</button>
                                    </div>
                                </td>
                                <td data-label="Subtotal"><strong class="cart-subtotal">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm cart-remove-btn" onclick="removeFromCart(<?= $item['id'] ?>)" title="Remove item" aria-label="Remove <?= htmlspecialchars($item['product_name']) ?> from cart">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 14H6L5 6"/>
                                            <path d="M10 11v6"/>
                                            <path d="M14 11v6"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <div class="cart-summary-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="cart-summary-body">
                    <div class="summary-row">
                        <span>Subtotal (<?= count($cartItems) ?> item<?= count($cartItems) > 1 ? 's' : '' ?>)</span>
                        <span>₱<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>₱<?= number_format($cartTotal, 2) ?></span>
                    </div>
                </div>
                <div class="cart-summary-actions">
                    <a href="<?= APP_URL ?>/index.php?url=checkout" class="btn btn-accent btn-lg btn-block">Proceed to Checkout</a>
                    <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-outline btn-lg btn-block">&larr; Continue Shopping</a>
                </div>
                <div class="cart-summary-secure">
                    <span>Secure checkout guaranteed</span>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state empty-state--cart">
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything to your cart yet. Browse our products and find something you'll love!</p>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent btn-lg">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
