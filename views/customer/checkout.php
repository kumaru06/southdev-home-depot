<?php
/* $pageTitle, $extraCss set by controller */
$extraJs = ['checkout.js'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container checkout-page">
    <!-- Checkout Progress Header -->
    <div class="co-header reveal-on-scroll reveal-left">
        <div class="co-title-wrap">
            <span class="co-kicker">Fast checkout • Secure payment • Davao delivery</span>
            <h1 class="co-title">Secure Checkout</h1>
            <p class="co-intro">Review your delivery details, choose a payment option, and place your order with confidence.</p>
        </div>
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
                <div class="co-card co-card--address reveal-on-scroll reveal-left">
                    <div class="co-card-header">
                        <div class="co-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 21s-6-4.35-6-10a6 6 0 1 1 12 0c0 5.65-6 10-6 10Z"></path>
                                <circle cx="12" cy="11" r="2.5"></circle>
                            </svg>
                        </div>
                        <div>
                            <h3 class="co-card-title">Delivery Address</h3>
                            <p class="co-card-sub">Davao City delivery area only</p>
                        </div>
                    </div>
                    <div class="co-card-body">
                        <?php
                        $hasSavedAddress = !empty($savedUser['address']);
                        $hasSavedPhone   = !empty($savedUser['phone']);
                        ?>
                        <?php if ($hasSavedAddress || $hasSavedPhone): ?>
                        <div class="co-saved-addr-bar" id="savedAddrBar">
                            <div class="co-saved-addr-info">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;flex-shrink:0;">
                                    <path d="M12 21s-6-4.35-6-10a6 6 0 1 1 12 0c0 5.65-6 10-6 10Z"></path>
                                    <circle cx="12" cy="11" r="2.5"></circle>
                                </svg>
                                <span>
                                    <?php
                                    $parts = array_filter([
                                        htmlspecialchars($savedUser['address'] ?? ''),
                                        htmlspecialchars($savedUser['zip_code'] ?? ''),
                                    ]);
                                    echo implode(' &bull; ', $parts) ?: 'Saved address on file';
                                    ?>
                                </span>
                            </div>
                            <button type="button" class="co-saved-addr-btn" id="useSavedAddr"
                                data-address="<?= htmlspecialchars($savedUser['address'] ?? '') ?>"
                                data-zip="<?= htmlspecialchars($savedUser['zip_code'] ?? '8000') ?>"
                                data-phone="<?= htmlspecialchars($savedUser['phone'] ?? '') ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Use saved address
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="co-saved-addr-bar co-saved-addr-bar--empty" id="savedAddrBar">
                            <div class="co-saved-addr-info">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;flex-shrink:0;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <span>No saved address &mdash; save one in your profile to skip this next time.</span>
                            </div>
                            <a href="<?= APP_URL ?>/index.php?url=profile&amp;return=checkout#address-section" class="co-saved-addr-btn co-saved-addr-btn--outline">
                                Save address
                            </a>
                        </div>
                        <?php endif; ?>
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
                                <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                                    value="<?= htmlspecialchars($savedUser['phone'] ?? '') ?>"
                                    placeholder="09XX XXX XXXX" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label for="notes">Delivery Notes <small>(optional)</small></label>
                            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Gate code, preferred delivery time, landmarks…"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="co-card co-card--payment reveal-on-scroll reveal-left">
                    <div class="co-card-header">
                        <div class="co-card-icon co-card-icon--blue" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z"></path>
                                <path d="M3 9h18"></path>
                                <path d="M7 14h3"></path>
                            </svg>
                        </div>
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
                            <label class="co-pay-opt">
                                <input type="radio" name="payment_method" value="qrph">
                                <div class="co-pay-inner">
                                    <span class="co-pay-radio"></span>
                                    <span class="co-pay-icon" style="background:#fff;border:1.5px solid #e5e7eb;border-radius:8px;display:flex;align-items:center;justify-content:center;width:40px;height:40px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1C1C1C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="5" y="5" width="3" height="3" fill="#1C1C1C"/><rect x="16" y="5" width="3" height="3" fill="#1C1C1C"/><rect x="5" y="16" width="3" height="3" fill="#1C1C1C"/><path d="M14 14h3v3h-3zM17 17h3v3h-3zM14 20h3"/></svg>
                                    </span>
                                    <span class="co-pay-text">
                                        <strong>QRPh — Scan to Pay</strong>
                                        <small>Any PH bank or e-wallet (InstaPay / PESONet)</small>
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="checkout-sidebar reveal-on-scroll reveal-right">
                <div class="co-summary">
                    <div class="co-summary-head">
                        <h3>Order Summary</h3>
                        <span class="co-badge"><?= count($cartItems) ?></span>
                    </div>
                    <div class="co-summary-meta">
                        <span>Ready to dispatch</span>
                        <span>Davao City only</span>
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
                    <div class="co-summary-note">Your order details will be confirmed before final processing.</div>
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
