<?php
/* $pageTitle, $extraCss set by controller */
$extraJs = ['checkout.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <!-- Checkout Progress Header -->
    <div class="co-header">
        <h1 class="co-title">Secure Checkout</h1>
        <div class="co-steps">
            <div class="co-step done">
                <span class="co-step-num">&check;</span>
                <span class="co-step-label">Cart</span>
            </div>
            <div class="co-step-line done"></div>
            <div class="co-step active">
                <span class="co-step-num">2</span>
                <span class="co-step-label">Details</span>
            </div>
            <div class="co-step-line"></div>
            <div class="co-step">
                <span class="co-step-num">3</span>
                <span class="co-step-label">Done</span>
            </div>
        </div>
    </div>

    <form action="<?= APP_URL ?>/index.php?url=orders/create" method="POST" id="checkout-form">
        <?= csrf_field() ?>
        <input type="hidden" name="shipping_state" value="Davao del Sur">
        <input type="hidden" name="shipping_city" value="Davao City">
        <input type="hidden" id="shipping_address_hidden" name="shipping_address" value="">

        <div class="checkout-grid">
            <!-- Main Column -->
            <div class="checkout-main">
                <!-- Delivery Address -->
                <div class="co-card">
                    <div class="co-card-header">
                        <div class="co-card-icon"></div>
                        <div>
                            <h3 class="co-card-title">Delivery Address</h3>
                            <p class="co-card-sub">Davao City delivery area only</p>
                        </div>
                    </div>
                    <div class="co-card-body">
                        <div class="co-location-tag">Davao City, Davao del Sur &bull; 8000</div>
                        <div class="form-group">
                            <label for="shipping_barangay">Barangay <span class="required">*</span></label>
                            <select id="shipping_barangay" class="form-control" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="street_address">Street Address <span class="required">*</span></label>
                            <textarea id="street_address" class="form-control" rows="2" placeholder="House/Unit No., Street, Building, Landmark" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group form-col">
                                <label for="shipping_zip">Zip Code</label>
                                <input type="text" id="shipping_zip" name="shipping_zip" class="form-control" value="8000" placeholder="8000">
                            </div>
                            <div class="form-group form-col">
                                <label for="contact_phone">Contact Phone <span class="required">*</span></label>
                                <input type="text" id="contact_phone" name="contact_phone" class="form-control" placeholder="09XX XXX XXXX" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label for="notes">Delivery Notes <small>(optional)</small></label>
                            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Gate code, preferred delivery time, landmarks…"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="co-card">
                    <div class="co-card-header">
                        <div class="co-card-icon co-card-icon--blue"></div>
                        <div>
                            <h3 class="co-card-title">Payment Method</h3>
                            <p class="co-card-sub">Choose how you'd like to pay</p>
                        </div>
                    </div>
                    <div class="co-card-body">
                        <div class="co-pay-options">
                            <label class="co-pay-opt active">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <div class="co-pay-inner">
                                    <span class="co-pay-radio"></span>
                                    <span class="co-pay-icon"><img src="<?= APP_URL ?>/assets/uploads/images/logo/COD2.png" alt="COD" class="co-pay-logo"></span>
                                    <span class="co-pay-text">
                                        <strong>Cash on Delivery</strong>
                                        <small>Pay when you receive your order</small>
                                    </span>
                                </div>
                            </label>
                            <label class="co-pay-opt">
                                <input type="radio" name="payment_method" value="gcash">
                                <div class="co-pay-inner">
                                    <span class="co-pay-radio"></span>
                                    <span class="co-pay-icon"><img src="<?= APP_URL ?>/assets/uploads/images/logo/gcashlogo.png" alt="GCash" class="co-pay-logo"></span>
                                    <span class="co-pay-text">
                                        <strong>GCash</strong>
                                        <small>Pay via GCash (PayMongo)</small>
                                    </span>
                                </div>
                            </label>
                            <label class="co-pay-opt">
                                <input type="radio" name="payment_method" value="card">
                                <div class="co-pay-inner">
                                    <span class="co-pay-radio"></span>
                                    <span class="co-pay-icon"><img src="<?= APP_URL ?>/assets/uploads/images/logo/creditcard.png" alt="Credit Card" class="co-pay-logo"></span>
                                    <span class="co-pay-text">
                                        <strong>Credit / Debit Card</strong>
                                        <small>Visa, Mastercard, JCB, Amex</small>
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="checkout-sidebar">
                <div class="co-summary">
                    <div class="co-summary-head">
                        <h3>Order Summary</h3>
                        <span class="co-badge"><?= count($cartItems) ?></span>
                    </div>
                    <div class="co-summary-items">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="co-sum-item">
                            <span class="co-sum-qty"><?= $item['quantity'] ?>×</span>
                            <span class="co-sum-name"><?= htmlspecialchars($item['product_name']) ?></span>
                            <span class="co-sum-price">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="co-summary-totals">
                        <div class="co-sum-row">
                            <span>Subtotal</span>
                            <span>₱<?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <div class="co-sum-row">
                            <span>Delivery</span>
                            <span class="co-free">FREE</span>
                        </div>
                        <div class="co-sum-grand">
                            <span>Total</span>
                            <span>₱<?= number_format($cartTotal, 2) ?></span>
                        </div>
                    </div>
                    <div class="co-summary-actions">
                        <button type="submit" class="btn btn-accent btn-block btn-lg">Place Order</button>
                        <a href="<?= APP_URL ?>/index.php?url=cart" class="btn btn-outline btn-block btn-lg">&larr; Back to Cart</a>
                    </div>
                    <div class="co-trust">
                        <span>Secure</span>
                        <span>Davao Delivery</span>
                        <span>Easy Returns</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
