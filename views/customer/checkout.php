<?php
/* $pageTitle, $extraCss set by controller */
$extraJs = ['checkout.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <h1 class="page-heading"><i data-lucide="credit-card"></i> Checkout</h1>

    <form action="<?= APP_URL ?>/index.php?url=orders/create" method="POST" id="checkout-form">
        <?= csrf_field() ?>

        <div class="checkout-grid">
            <!-- Shipping Information -->
            <div class="checkout-main">
                <div class="card">
                    <h3><i data-lucide="truck"></i> Shipping Information</h3>
                    <div class="form-group">
                        <label for="shipping_address">Address <span class="required">*</span></label>
                        <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" placeholder="House/Unit No., Street, Barangay" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group form-col">
                            <label for="shipping_state">Province <span class="required">*</span></label>
                            <select id="shipping_state" name="shipping_state" class="form-control" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="form-group form-col">
                            <label for="shipping_city">City / Municipality <span class="required">*</span></label>
                            <select id="shipping_city" name="shipping_city" class="form-control" required disabled>
                                <option value="">Select City</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group form-col">
                            <label for="shipping_zip">Zip Code</label>
                            <input type="text" id="shipping_zip" name="shipping_zip" class="form-control" placeholder="e.g. 1000">
                        </div>
                        <div class="form-group form-col">
                            <label for="contact_phone">Contact Phone <span class="required">*</span></label>
                            <input type="text" id="contact_phone" name="contact_phone" class="form-control" placeholder="09XX XXX XXXX" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Order Notes <small>(optional)</small></label>
                        <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Special delivery instructions…"></textarea>
                    </div>
                </div>

                <div class="card">
                    <h3><i data-lucide="wallet"></i> Payment Method</h3>
                    <div class="payment-options">
                        <label class="payment-option active">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <div class="payment-option-content">
                                <i data-lucide="banknote"></i>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <small>Pay when you receive</small>
                                </div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="gcash">
                            <div class="payment-option-content">
                                <i data-lucide="smartphone"></i>
                                <div>
                                    <strong>GCash</strong>
                                    <small>Pay via GCash (PayMongo)</small>
                                </div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="card">
                            <div class="payment-option-content">
                                <i data-lucide="credit-card"></i>
                                <div>
                                    <strong>Card</strong>
                                    <small>Pay with debit or credit card</small>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Card details (hidden until card selected) -->
                    <div id="card-details" class="payment-details" style="display:none; margin-top:16px;">
                        <div class="card card--padded">
                            <h4>Pay with Card</h4>
                            <div class="card-icons" style="margin-bottom:8px;">
                                <img src="<?= APP_URL ?>/assets/images/visa.png" alt="Visa" style="height:20px;margin-right:6px;"> 
                                <img src="<?= APP_URL ?>/assets/images/mastercard.png" alt="Mastercard" style="height:20px;margin-right:6px;"> 
                                <img src="<?= APP_URL ?>/assets/images/jcb.png" alt="JCB" style="height:20px;margin-right:6px;"> 
                                <img src="<?= APP_URL ?>/assets/images/amex.png" alt="Amex" style="height:20px;"> 
                            </div>

                            <div class="form-group">
                                <label for="card_number">Card number</label>
                                <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                            </div>

                            <div class="form-group">
                                <label for="card_name">Name on card</label>
                                <input type="text" id="card_name" name="card_name" class="form-control" placeholder="Ex. Mark Perez">
                            </div>

                            <div class="form-row">
                                <div class="form-group form-col">
                                    <label for="card_expiry">Expiry date</label>
                                    <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM / YY">
                                </div>
                                <div class="form-group form-col">
                                    <label for="card_cvc">Security code</label>
                                    <input type="text" id="card_cvc" name="card_cvc" class="form-control" placeholder="CVC">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="checkout-sidebar">
                <div class="card cart-summary">
                    <h3>Order Summary</h3>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <div class="summary-item-info">
                                <span class="summary-item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                                <span class="summary-item-qty">×<?= $item['quantity'] ?></span>
                            </div>
                            <span class="summary-item-price">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>₱<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <button type="submit" class="btn btn-accent btn-block btn-lg">
                        <i data-lucide="check-circle"></i> Place Order
                    </button>
                    <a href="<?= APP_URL ?>/index.php?url=cart" class="btn btn-outline btn-block">
                        <i data-lucide="arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
