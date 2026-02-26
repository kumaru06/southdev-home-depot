<?php
/* $pageTitle, $extraCss set by controller */
$extraJs = ['cart.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <h1 class="page-heading"><i data-lucide="shopping-cart"></i> Shopping Cart</h1>

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
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td data-label="Product">
                                    <div class="cart-product">
                                        <img src="<?= APP_URL ?>/assets/uploads/<?= $item['image'] ?: 'placeholder.svg' ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="cart-thumb">
                                        <span><?= htmlspecialchars($item['product_name']) ?></span>
                                    </div>
                                </td>
                                <td data-label="Price">₱<?= number_format($item['price'], 2) ?></td>
                                <td data-label="Quantity">
                                    <div class="qty-stepper" data-cart-id="<?= $item['id'] ?>">
                                        <button class="qty-btn qty-minus">−</button>
                                        <input type="number" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                        <button class="qty-btn qty-plus">+</button>
                                    </div>
                                </td>
                                <td data-label="Subtotal"><strong>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="removeFromCart(<?= $item['id'] ?>)" title="Remove">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary card">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Items (<?= count($cartItems) ?>)</span>
                    <span>₱<?= number_format($cartTotal, 2) ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span>₱<?= number_format($cartTotal, 2) ?></span>
                </div>
                <a href="<?= APP_URL ?>/index.php?url=checkout" class="btn btn-accent btn-block">
                    <i data-lucide="credit-card"></i> Proceed to Checkout
                </a>
                <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-outline btn-block">
                    <i data-lucide="arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i data-lucide="shopping-bag" class="empty-icon"></i>
            <h3>Your cart is empty</h3>
            <p>Browse our products and add items to get started.</p>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
