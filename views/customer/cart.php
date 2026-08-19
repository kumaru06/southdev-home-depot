<?php
/* $pageTitle, $extraCss set by controller */
$extraJs = ['cart.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$cartQuantityTotal = 0;
foreach (($cartItems ?? []) as $cartItemSummary) {
    $cartQuantityTotal += (int) ($cartItemSummary['quantity'] ?? 0);
}
$itemCount = is_array($cartItems ?? null) ? count($cartItems) : 0;
?>

<div class="container cart-page">
    <?php if (!empty($cartItems)): ?>
        <section class="cart-hero-panel">
            <div class="cart-hero-copy">
                <div class="page-heading-row cart-heading-row">
                    <h1 class="page-heading">Shopping Cart</h1>
                    <span class="cart-heading-badge"><?= $cartQuantityTotal ?> unit<?= $cartQuantityTotal !== 1 ? 's' : '' ?></span>
                </div>
                <p class="cart-hero-subtitle">Review your items, adjust quantities, and proceed to checkout when you're ready.</p>
            </div>
            <div class="cart-hero-stats" aria-label="Cart overview">
                <div class="cart-stat-card cart-stat-card--items">
                    <div class="cart-stat-top">
                        <span class="cart-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        </span>
                        <strong><?= $itemCount ?></strong>
                    </div>
                    <span class="cart-stat-label">Products</span>
                </div>
                <div class="cart-stat-card cart-stat-card--units">
                    <div class="cart-stat-top">
                        <span class="cart-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        </span>
                        <strong><?= $cartQuantityTotal ?></strong>
                    </div>
                    <span class="cart-stat-label">Total units</span>
                </div>
                <div class="cart-stat-card cart-stat-card--total">
                    <div class="cart-stat-top">
                        <span class="cart-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </span>
                        <strong>₱<?= number_format($cartTotal, 0) ?></strong>
                    </div>
                    <span class="cart-stat-label">Subtotal</span>
                </div>
            </div>
        </section>

        <div class="cart-layout">
            <div class="cart-items-wrap">
                <div class="cart-table-topbar">
                    <div class="cart-table-topbar-copy">
                        <span class="cart-table-eyebrow">Your items</span>
                        <h2>Selected products</h2>
                        <p>Update quantities or remove items before checkout.</p>
                    </div>
                </div>
                <div class="cart-table-head">
                    <table class="cart-table cart-table--head">
                        <colgroup>
                            <col class="cart-col-product">
                            <col class="cart-col-price">
                            <col class="cart-col-qty">
                            <col class="cart-col-subtotal">
                            <col class="cart-col-remove">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Price</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Subtotal</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="cart-items-scroll">
                    <table class="cart-table cart-table--body">
                        <colgroup>
                            <col class="cart-col-product">
                            <col class="cart-col-price">
                            <col class="cart-col-qty">
                            <col class="cart-col-subtotal">
                            <col class="cart-col-remove">
                        </colgroup>
                        <tbody>
                            <?php foreach ($cartItems as $idx => $item): ?>
                                <tr class="cart-row" style="animation-delay: <?= $idx * 0.04 ?>s">
                                    <td data-label="Product">
                                        <div class="cart-product">
                                            <?php if ($item['image']): ?>
                                                <img src="<?= APP_URL ?>/assets/uploads/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="cart-thumb">
                                            <?php else: ?>
                                                <div class="cart-thumb cart-thumb-placeholder"><span>N/A</span></div>
                                            <?php endif; ?>
                                            <div class="cart-product-info">
                                                <span class="cart-product-name"><?= htmlspecialchars($item['product_name']) ?></span>
                                                <?php if (!empty($item['size_label'])): ?>
                                                    <span class="cart-product-unit">Size: <?= htmlspecialchars($item['size_label']) ?></span>
                                                <?php endif; ?>
                                                <span class="cart-product-unit">₱<?= number_format($item['price'], 2) ?> each</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Price"><span class="cart-price">₱<?= number_format($item['price'], 2) ?></span></td>
                                    <td data-label="Quantity">
                                        <div class="qty-stepper" data-cart-id="<?= $item['id'] ?>">
                                            <button class="qty-btn qty-minus" type="button" aria-label="Decrease quantity">−</button>
                                            <input type="number" value="<?= $item['quantity'] ?>" min="1" class="qty-input" aria-label="Quantity">
                                            <button class="qty-btn qty-plus" type="button" aria-label="Increase quantity">+</button>
                                        </div>
                                    </td>
                                    <td data-label="Subtotal"><strong class="cart-subtotal">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                                    <td data-label="Remove">
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
            </div>

            <aside class="cart-summary">
                <div class="cart-summary-header">
                    <span class="cart-summary-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </span>
                    <div>
                        <h3>Order Summary</h3>
                        <p>Ready for checkout · Davao delivery</p>
                    </div>
                </div>
                <div class="cart-summary-body">
                    <div class="summary-row">
                        <span>Subtotal (<?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?>)</span>
                        <span>₱<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <?php if ($cartTotal >= 10000): ?>
                            <span class="cart-summary-free">Free</span>
                        <?php else: ?>
                            <span>From ₱300</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($cartTotal < 10000): ?>
                    <div class="cart-summary-progress">
                        <div class="cart-summary-progress-copy">
                            <span>Free delivery at ₱10,000+</span>
                            <span>₱<?= number_format(max(0, 10000 - $cartTotal), 2) ?> to go</span>
                        </div>
                        <div class="cart-summary-progress-bar" role="progressbar" aria-valuenow="<?= min(100, (int) round(($cartTotal / 10000) * 100)) ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="cart-summary-progress-fill" style="width: <?= min(100, ($cartTotal / 10000) * 100) ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row summary-total">
                        <span><?= $cartTotal >= 10000 ? 'Total' : 'Subtotal' ?></span>
                        <span>₱<?= number_format($cartTotal, 2) ?></span>
                    </div>
                </div>
                <div class="cart-summary-actions">
                    <a href="<?= APP_URL ?>/index.php?url=checkout" class="btn btn-accent btn-lg btn-block">Proceed to Checkout</a>
                    <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-outline btn-lg btn-block">&larr; Continue Shopping</a>
                </div>
                <div class="cart-summary-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    <span>Prices and quantities stay editable here until you place the order.</span>
                </div>
                <div class="cart-summary-secure">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Secure checkout guaranteed</span>
                </div>
            </aside>
        </div>
    <?php else: ?>
        <div class="empty-state empty-state--cart">
            <div class="cart-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything yet. Browse our products and find something you'll love!</p>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent btn-lg">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
