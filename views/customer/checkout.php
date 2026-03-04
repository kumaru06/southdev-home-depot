<?php
/* $pageTitle, $extraCss set by controller */
$extraJs = ['checkout.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <!-- Checkout Progress Header -->
    <div class="co-header">
        <h1 class="co-title"><i data-lucide="shield-check"></i> Secure Checkout</h1>
        <div class="co-steps">
            <div class="co-step done">
                <span class="co-step-num"><i data-lucide="check"></i></span>
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
                        <div class="co-card-icon"><i data-lucide="map-pin"></i></div>
                        <div>
                            <h3 class="co-card-title">Delivery Address</h3>
                            <p class="co-card-sub">Davao City delivery area only</p>
                        </div>
                    </div>
                    <div class="co-card-body">
                        <div class="co-location-tag">
                            <i data-lucide="navigation"></i> Davao City, Davao del Sur &bull; 8000
                        </div>
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
                        <div class="co-card-icon co-card-icon--blue"><i data-lucide="wallet"></i></div>
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
                                    <span class="co-pay-icon"><i data-lucide="banknote"></i></span>
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
                                    <span class="co-pay-icon co-pay-icon--blue"><i data-lucide="smartphone"></i></span>
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
                                    <span class="co-pay-icon co-pay-icon--purple"><i data-lucide="credit-card"></i></span>
                                    <span class="co-pay-text">
                                        <strong>Credit / Debit Card</strong>
                                        <small>Visa, Mastercard, JCB, Amex</small>
                                    </span>
                                </div>
                            </label>
                        </div>

                        <!-- Card details (hidden until card selected) -->
                        <div id="card-details" class="co-card-expand" style="display:none">
                            <div class="co-card-expand-inner">
                                <div class="co-card-expand-head">
                                    <i data-lucide="lock" style="width:14px;height:14px"></i> Card Information
                                </div>
                                <div class="form-group">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                                </div>
                                <div class="form-group">
                                    <label for="card_name">Name on Card</label>
                                    <input type="text" id="card_name" name="card_name" class="form-control" placeholder="e.g. Juan Dela Cruz">
                                </div>
                                <div class="form-row">
                                    <div class="form-group form-col">
                                        <label for="card_expiry">Expiry</label>
                                        <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM / YY">
                                    </div>
                                    <div class="form-group form-col">
                                        <label for="card_cvc">CVC</label>
                                        <input type="text" id="card_cvc" name="card_cvc" class="form-control" placeholder="123">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="checkout-sidebar">
                <div class="co-summary">
                    <div class="co-summary-head">
                        <i data-lucide="shopping-bag"></i>
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
                        <button type="submit" class="btn btn-accent btn-block btn-lg">
                            <i data-lucide="check-circle"></i> Place Order
                        </button>
                        <a href="<?= APP_URL ?>/index.php?url=cart" class="btn btn-outline btn-block btn-lg">
                            <i data-lucide="arrow-left"></i> Back to Cart
                        </a>
                    </div>
                    <div class="co-trust">
                        <span><i data-lucide="shield-check"></i> Secure</span>
                        <span><i data-lucide="truck"></i> Davao Delivery</span>
                        <span><i data-lucide="rotate-ccw"></i> Easy Returns</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
