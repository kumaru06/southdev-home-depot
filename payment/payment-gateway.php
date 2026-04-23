<?php
/**
 * Payment Gateway Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Order.php';

// Auth check (session already started in config.php)
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/index.php?url=login');
    exit;
}

$orderId = intval($_GET['order_id'] ?? 0);
$method = $_GET['method'] ?? 'cod';

$orderModel = new Order($pdo);
$order = $orderModel->findById($orderId);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: ' . APP_URL . '/payment/payment-failed.php?reason=invalid_order');
    exit;
}

$pageTitle = 'Payment';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        :root{--charcoal:#1C1C1C;--steel:#6B7280;--accent:#F97316;--accent-dark:#EA580C;--neutral:#F5F5F5;--white:#FFFFFF;--border:#E5E7EB;--success:#16A34A;--danger:#DC2626;--radius:12px;}
        body{font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}

        /* Card */
        .pay-card{background:var(--white);border-radius:var(--radius);box-shadow:0 1px 3px rgba(0,0,0,.06),0 8px 24px rgba(0,0,0,.06);max-width:460px;width:100%;overflow:hidden;}
        .pay-header{padding:1.75rem 2rem 1.25rem;text-align:center;border-bottom:1px solid var(--border);}
        .pay-logo{font-size:1.1rem;font-weight:800;color:var(--charcoal);letter-spacing:-.02em;margin-bottom:1rem;}
        .pay-logo span{color:var(--accent);}
        .pay-order{font-size:.8rem;color:var(--steel);margin-bottom:.35rem;}
        .pay-amount{font-size:2rem;font-weight:800;color:var(--charcoal);line-height:1.2;}
        .pay-method-badge{display:inline-block;background:var(--neutral);color:var(--charcoal);padding:.25rem .85rem;border-radius:99px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-top:.75rem;}
        .pay-body{padding:1.75rem 2rem 2rem;}

        /* Form elements */
        .form-group{margin-bottom:.85rem;text-align:left;}
        .form-label{display:block;font-weight:600;font-size:.78rem;color:var(--charcoal);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.03em;}
        .form-control{width:100%;padding:.6rem .75rem;border:1.5px solid var(--border);border-radius:8px;font-size:.875rem;font-family:inherit;background:var(--white);transition:border-color .15s,box-shadow .15s;}
        .form-control:focus{border-color:var(--accent);outline:none;box-shadow:0 0 0 3px rgba(249,115,22,.12);}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}

        /* Buttons */
        .btn{display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.7rem 1.25rem;border-radius:8px;font-weight:700;font-size:.875rem;cursor:pointer;border:none;text-decoration:none;transition:all .15s;font-family:inherit;width:100%;}
        .btn-accent{background:var(--accent);color:var(--white);}
        .btn-accent:hover{background:var(--accent-dark);}
        .btn-accent:active{transform:translateY(1px);}
        .btn-outline{background:transparent;color:var(--steel);border:1.5px solid var(--border);}
        .btn-outline:hover{border-color:var(--charcoal);color:var(--charcoal);}
        .btn-group{display:flex;flex-direction:column;gap:.6rem;margin-top:1.25rem;}
        .btn:disabled{opacity:.55;pointer-events:none;}

        /* Misc */
        .pay-info-box{background:var(--neutral);border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.25rem;text-align:left;}
        .pay-info-box p{margin:.2rem 0;color:var(--charcoal);font-size:.875rem;}
        .pay-info-box .highlight{font-size:1.25rem;font-weight:700;color:var(--charcoal);text-align:center;letter-spacing:1px;margin:.5rem 0;}
        .pay-secure{font-size:.75rem;color:var(--steel);text-align:center;margin-top:.85rem;}
        .pay-spinner{width:32px;height:32px;border:3px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 1rem;}
        .pay-loading{text-align:center;padding:2rem 0;}
        .pay-loading p{color:var(--steel);font-size:.875rem;margin-top:.35rem;}
        .pay-error-box{background:#FEF2F2;color:var(--danger);border-radius:8px;padding:.65rem .85rem;font-size:.825rem;margin-bottom:.85rem;display:none;}
        .pay-test-banner{background:#FFF7ED;color:#92400E;border:1px solid #FED7AA;border-radius:8px;padding:.5rem .85rem;font-size:.78rem;text-align:center;margin-bottom:1rem;}
        .pay-cards-note{font-size:.72rem;color:var(--steel);text-align:center;margin-top:.75rem;}
        .card-number-wrap{position:relative;}
        .card-number-wrap .form-control{padding-right:3.85rem;}
        .card-brand-indicator{display:flex;justify-content:flex-end;margin-top:.45rem;min-height:1.1rem;}
        .card-brand-logo{display:none;position:absolute;top:50%;right:.75rem;transform:translateY(-50%);width:34px;height:22px;object-fit:contain;border-radius:4px;background:var(--white);padding:2px;box-shadow:inset 0 0 0 1px rgba(0,0,0,.06);pointer-events:none;}
        .card-brand-logo.is-visible{display:block;}
        .card-brand-hint{font-size:.72rem;color:var(--steel);text-align:right;flex:1;}
        @keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
        @media(max-width:520px){.pay-body,.pay-header{padding-left:1.25rem;padding-right:1.25rem;}.pay-amount{font-size:1.6rem;}}
    </style>
</head>
<body>
    <div class="pay-card">
        <div class="pay-header">
            <div class="pay-logo">South<span>Dev</span> Home Depot</div>
            <p class="pay-order">Order #<?= htmlspecialchars($order['order_number']) ?></p>
            <div class="pay-amount">₱<?= number_format($order['total_amount'], 2) ?></div>
            <div class="pay-method-badge"><?= ucfirst($method) ?></div>
        </div>
        <div class="pay-body">

        <?php if ($method === 'cod'): ?>
            <p style="color:var(--steel); line-height:1.6; margin-bottom:1.25rem; font-size:.9rem;">
                Your order will be prepared and payment will be collected upon delivery. Please have the exact amount ready.
            </p>
            <div class="form-group">
                <label class="form-label">Email (optional, for receipt)</label>
                <input type="email" id="cod-email" class="form-control" placeholder="your@email.com" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
            </div>
            <div class="btn-group">
                <a href="#" id="cod-confirm-btn" onclick="confirmCod()" class="btn btn-accent">Confirm Order</a>
                <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline">Cancel</a>
            </div>
            <script>
            function confirmCod() {
                var email = document.getElementById('cod-email').value.trim();
                var url = '<?= APP_URL ?>/payment/payment-success.php?order_id=<?= $orderId ?>&method=cod&csrf_token=<?= urlencode(csrf_token()) ?>';
                if (email) url += '&receipt_email=' + encodeURIComponent(email);
                window.location.href = url;
            }
            </script>

        <?php elseif ($method === 'gcash'): ?>
            <?php if (defined('PAYMONGO_ENABLED') && PAYMONGO_ENABLED): ?>
                <!-- PayMongo GCash Integration -->
                <div id="gcash-form-section">
                    <div class="form-group" style="margin-bottom:1.25rem;">
                        <label class="form-label">Email (optional, for receipt)</label>
                        <input type="email" id="gcash-email" class="form-control" placeholder="your@email.com" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                    </div>
                    <div class="btn-group">
                        <button onclick="startGcash()" class="btn btn-accent">Continue to GCash</button>
                        <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline">Cancel</a>
                    </div>
                </div>
                <div id="gcash-loading" class="pay-loading" style="display:none;">
                    <div class="pay-spinner"></div>
                    <p>Preparing payment...</p>
                    <p style="font-size:.78rem;margin-top:.25rem;">Redirecting to GCash via PayMongo</p>
                </div>
                <div id="gcash-error" style="display:none; text-align:center;">
                    <p style="color:var(--danger); font-weight:700; margin-bottom:.75rem;">Payment initialization failed</p>
                    <p id="gcash-error-msg" style="color:var(--steel); font-size:.875rem; margin-bottom:1.25rem;"></p>
                    <div class="btn-group">
                        <button onclick="initGcashPayment()" class="btn btn-accent">Try Again</button>
                        <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline">Cancel</a>
                    </div>
                </div>

                <script>
                    var CSRF_TOKEN = '<?= addslashes(csrf_token()) ?>';

                    function startGcash() {
                        document.getElementById('gcash-form-section').style.display = 'none';
                        initGcashPayment();
                    }

                    function initGcashPayment() {
                        document.getElementById('gcash-loading').style.display = 'block';
                        document.getElementById('gcash-error').style.display = 'none';

                        var gcashEmail = document.getElementById('gcash-email').value.trim();
                        fetch('<?= APP_URL ?>/index.php?url=payment/create-source', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                            body: JSON.stringify({
                                order_id: <?= $orderId ?>,
                                method: 'gcash',
                                receipt_email: gcashEmail || undefined
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && data.checkout_url) {
                                window.location.href = data.checkout_url;
                            } else {
                                document.getElementById('gcash-loading').style.display = 'none';
                                document.getElementById('gcash-error').style.display = 'block';
                                document.getElementById('gcash-error-msg').textContent = data.error || 'Failed to initialize payment. Please try again.';
                            }
                        })
                        .catch(err => {
                            document.getElementById('gcash-loading').style.display = 'none';
                            document.getElementById('gcash-error').style.display = 'block';
                            document.getElementById('gcash-error-msg').textContent = 'Network error: ' + err.message;
                        });
                    }

                    // GCash payment now starts on button click, not auto-load
                </script>
            <?php else: ?>
                <p style="color:var(--danger); text-align:center; padding:1.5rem 0; font-size:.9rem;">GCash payments require PayMongo to be enabled. Please contact the administrator.</p>
                <div class="btn-group">
                    <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline">Go Back</a>
                </div>
            <?php endif; ?>

        <?php elseif ($method === 'card'): ?>
            <?php if (defined('PAYMONGO_ENABLED') && PAYMONGO_ENABLED): ?>
                <!-- PayMongo Credit / Debit Card Integration -->
                <?php if (strpos(PAYMONGO_SECRET_KEY, 'xxxxxxxxxxxx') !== false): ?>
                <div class="pay-test-banner">
                    <strong>Test Mode</strong> — No real charge will be made.
                </div>
                <?php endif; ?>

                <!-- Step 1: Loading while creating Payment Intent -->
                <div id="card-loading" class="pay-loading">
                    <div class="pay-spinner"></div>
                    <p>Preparing secure card form…</p>
                </div>

                <!-- Step 2: Card entry form -->
                <div id="card-form-section" style="display:none;">
                    <p class="pay-secure">Secured by PayMongo</p>
                    <div style="margin-top:1rem;">
                        <div class="form-group">
                            <label class="form-label">Cardholder Name</label>
                            <input type="text" id="card-name" class="form-control" placeholder="Name on card" autocomplete="cc-name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Card Number</label>
                            <div class="card-number-wrap">
                                <input type="text" id="card-number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="23" autocomplete="cc-number" inputmode="numeric">
                                <img id="card-brand-logo" class="card-brand-logo" alt="">
                            </div>
                            <div class="card-brand-indicator">
                                <span id="card-brand-hint" class="card-brand-hint">Card type will appear automatically.</span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Expiry (MM / YY)</label>
                                <input type="text" id="card-expiry" class="form-control" placeholder="MM / YY" maxlength="7" autocomplete="cc-exp" inputmode="numeric">
                            </div>
                            <div class="form-group">
                                <label class="form-label">CVC</label>
                                <input type="text" id="card-cvc" class="form-control" placeholder="CVC" maxlength="4" autocomplete="cc-csc" inputmode="numeric">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email (optional, for receipt)</label>
                            <input type="email" id="card-email" class="form-control" placeholder="your@email.com" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div id="card-error" class="pay-error-box"></div>

                    <div class="btn-group">
                        <button id="card-pay-btn" onclick="submitCardPayment()" class="btn btn-accent">
                            Pay ₱<?= number_format($order['total_amount'], 2) ?>
                        </button>
                        <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline">Cancel</a>
                    </div>
                    <p class="pay-cards-note">Visa, Mastercard, JCB accepted · 3D Secure supported</p>
                </div>

                <!-- Step 3: Processing overlay -->
                <div id="card-processing" style="display:none;" class="pay-loading">
                    <div class="pay-spinner"></div>
                    <p>Processing payment…</p>
                    <p style="font-size:.78rem;margin-top:.25rem;">Please do not close this window.</p>
                </div>

                <script>
                var PAYMONGO_PUBLIC_KEY = '<?= addslashes(PAYMONGO_PUBLIC_KEY) ?>';
                var CARD_BRAND_ASSET_BASE = '<?= APP_URL ?>/assets/uploads/images/cardlogo';
                var CSRF_TOKEN = '<?= addslashes(csrf_token()) ?>';
                var ORDER_ID = <?= $orderId ?>;
                var paymentIntentId = null;
                var clientKey = null;

                function detectCardBrand(number) {
                    if (!number) return null;

                    const brands = [
                        { key: 'visa', label: 'Visa', assetFile: 'visa.png', pattern: /^4/ },
                        { key: 'mastercard', label: 'Mastercard', assetFile: 'mastercard.png', pattern: /^(5[1-5]|2(?:2[2-9]|[3-6]\d|7[01]|720))/ },
                        { key: 'amex', label: 'American Express', assetFile: 'americanexpress.png', pattern: /^3[47]/ },
                        { key: 'discover', label: 'Discover', assetFile: 'discover.png', pattern: /^(6011|65|64[4-9])/ },
                        { key: 'jcb', label: 'JCB', assetFile: 'jcb.png', pattern: /^(2131|1800|35)/ },
                        { key: 'diners', label: 'Diners Club', assetFile: 'dinersclub.png', pattern: /^3(?:0[0-5]|[68])/ },
                        { key: 'unionpay', label: 'UnionPay', assetFile: 'unionpay.png', pattern: /^(62|81)/ },
                        { key: 'maestro', label: 'Maestro', assetFile: 'maestro.png', pattern: /^(5[06789]|6\d)/ }
                    ];

                    for (const brand of brands) {
                        if (brand.pattern.test(number)) {
                            return brand;
                        }
                    }

                    return number.length >= 4 ? { key: 'unknown', label: 'Unknown card' } : null;
                }

                function formatCardNumber(rawNumber, brand) {
                    if (brand && brand.key === 'amex') {
                        const trimmed = rawNumber.substring(0, 15);
                        const p1 = trimmed.substring(0, 4);
                        const p2 = trimmed.substring(4, 10);
                        const p3 = trimmed.substring(10, 15);
                        return [p1, p2, p3].filter(Boolean).join(' ');
                    }

                    const trimmed = rawNumber.substring(0, 19);
                    return trimmed.replace(/(.{4})/g, '$1 ').trim();
                }

                function updateCardBrandUI(brand) {
                    const logo = document.getElementById('card-brand-logo');
                    const hint = document.getElementById('card-brand-hint');
                    const cvcInput = document.getElementById('card-cvc');

                    if (!logo || !hint || !cvcInput) return;

                    logo.classList.remove('is-visible');
                    logo.removeAttribute('src');
                    logo.alt = '';

                    if (!brand) {
                        hint.textContent = 'Card type will appear automatically.';
                        cvcInput.maxLength = 4;
                        cvcInput.placeholder = 'CVC';
                        return;
                    }

                    if (brand.key === 'unknown') {
                        hint.textContent = 'We could not identify this card yet.';
                    } else {
                        hint.textContent = 'Detected card type: ' + brand.label + '.';
                        logo.src = CARD_BRAND_ASSET_BASE + '/' + (brand.assetFile || (brand.key + '.png'));
                        logo.alt = brand.label + ' logo';
                        logo.onerror = function() {
                            logo.classList.remove('is-visible');
                        };
                        logo.onload = function() {
                            logo.classList.add('is-visible');
                        };
                    }

                    cvcInput.maxLength = brand.key === 'amex' ? 4 : 3;
                    cvcInput.placeholder = brand.key === 'amex' ? '4-digit CID' : 'CVC';
                    cvcInput.value = (cvcInput.value || '').replace(/\D/g, '').substring(0, cvcInput.maxLength);
                }

                // ── Initialise: create payment intent on page load ──────────────
                async function initCardPayment() {
                    try {
                        const res = await fetch('<?= APP_URL ?>/index.php?url=payment/create-intent', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                            body: JSON.stringify({ order_id: ORDER_ID })
                        });
                        const data = await res.json();
                        if (!data.success) throw new Error(data.error || 'Could not start payment');

                        paymentIntentId = data.payment_intent_id;
                        clientKey       = data.client_key;

                        document.getElementById('card-loading').style.display = 'none';
                        document.getElementById('card-form-section').style.display = 'block';
                    } catch (err) {
                        document.getElementById('card-loading').innerHTML =
                            '<p style="color:var(--danger);font-weight:700;margin-bottom:.5rem;">Failed to initialise payment form</p>' +
                            '<p style="color:var(--steel);font-size:.875rem;margin-bottom:1.25rem;">' + err.message + '</p>' +
                            '<div class="btn-group">' +
                            '<button onclick="initCardPayment()" class="btn btn-accent">Retry</button>' +
                            '<a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline">Cancel</a>' +
                            '</div>';
                    }
                }

                // ── Card number / expiry formatting helpers ─────────────────────
                document.addEventListener('DOMContentLoaded', function() {
                    initCardPayment();

                    document.getElementById('card-number').addEventListener('input', function(e) {
                        let rawValue = e.target.value.replace(/\D/g, '');
                        const brand = detectCardBrand(rawValue);
                        e.target.value = formatCardNumber(rawValue, brand);
                        updateCardBrandUI(brand);
                    });
                    document.getElementById('card-expiry').addEventListener('input', function(e) {
                        let v = e.target.value.replace(/\D/g,'').substring(0,4);
                        if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
                        e.target.value = v;
                    });
                    document.getElementById('card-cvc').addEventListener('input', function(e) {
                        e.target.value = e.target.value.replace(/\D/g,'').substring(0, e.target.maxLength || 4);
                    });
                });

                // ── Submit card: tokenise → attach ──────────────────────────────
                async function submitCardPayment() {
                    const cardEl   = document.getElementById('card-pay-btn');
                    const errorEl  = document.getElementById('card-error');
                    errorEl.style.display = 'none';

                    const name     = document.getElementById('card-name').value.trim();
                    const rawNum   = document.getElementById('card-number').value.replace(/\s/g,'');
                    const exp      = document.getElementById('card-expiry').value.replace(/\s/g,'');
                    const cvc      = document.getElementById('card-cvc').value.trim();
                    const email    = document.getElementById('card-email').value.trim();

                    if (!name)             { showCardError('Please enter the cardholder name.'); return; }
                    if (rawNum.length < 13){ showCardError('Please enter a valid card number.');  return; }
                    const expParts = exp.split('/');
                    if (expParts.length !== 2 || !expParts[0] || !expParts[1]) {
                        showCardError('Please enter a valid expiry date (MM/YY).'); return;
                    }
                    if (cvc.length < 3)    { showCardError('Please enter a valid CVC.'); return; }

                    const expMonth = parseInt(expParts[0], 10);
                    const expYear  = parseInt('20' + expParts[1].trim(), 10);

                    // Show processing state
                    document.getElementById('card-form-section').style.display = 'none';
                    document.getElementById('card-processing').style.display = 'block';

                    try {
                        // Step A: Create PaymentMethod via PayMongo API (public key)
                        const pmRes = await fetch('https://api.paymongo.com/v1/payment_methods', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Basic ' + btoa(PAYMONGO_PUBLIC_KEY + ':')
                            },
                            body: JSON.stringify({
                                data: {
                                    attributes: {
                                        type: 'card',
                                        details: {
                                            card_number: rawNum,
                                            exp_month:   expMonth,
                                            exp_year:    expYear,
                                            cvc:         cvc
                                        },
                                        billing: {
                                            name:  name,
                                            email: email || undefined,
                                            phone: ''
                                        }
                                    }
                                }
                            })
                        });

                        const pmData = await pmRes.json();
                        if (!pmRes.ok) {
                            const errMsg = pmData.errors?.[0]?.detail || 'Card tokenisation failed.';
                            throw new Error(errMsg);
                        }

                        const paymentMethodId = pmData.data.id;

                        // Step B: Attach PaymentMethod to Payment Intent (our backend)
                        const attachRes = await fetch('<?= APP_URL ?>/index.php?url=payment/attach-card', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                            body: JSON.stringify({
                                order_id:          ORDER_ID,
                                payment_method_id: paymentMethodId,
                                payment_intent_id: paymentIntentId,
                                client_key:        clientKey,
                                receipt_email:     email || undefined
                            })
                        });

                        const attachData = await attachRes.json();
                        if (!attachData.success) throw new Error(attachData.error || 'Payment attachment failed.');

                        // Redirect (3DS or success page)
                        window.location.href = attachData.redirect_url;

                    } catch (err) {
                        document.getElementById('card-processing').style.display = 'none';
                        document.getElementById('card-form-section').style.display = 'block';
                        showCardError(err.message);
                    }
                }

                function showCardError(msg) {
                    const el = document.getElementById('card-error');
                    el.textContent = msg;
                    el.style.display = 'block';
                }
                </script>
            <?php else: ?>
                <p style="color:var(--danger); text-align:center; padding:1.5rem 0; font-size:.9rem;">Card payments require PayMongo to be enabled. Please contact the administrator.</p>
                <div class="btn-group">
                    <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline">Go Back</a>
                </div>
            <?php endif; ?>

        <?php elseif ($method === 'bank'): ?>
            <div class="pay-info-box">
                <p><strong>Bank:</strong> BDO</p>
                <p><strong>Account:</strong> 1234-5678-9012</p>
                <p><strong>Name:</strong> SouthDev Home Depot</p>
                <p style="text-align:center; font-size:.78rem; color:var(--steel); margin-top:.5rem;">Ref: <?= htmlspecialchars($order['order_number']) ?></p>
            </div>
            <form action="<?= APP_URL ?>/payment/payment-success.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                <input type="hidden" name="method" value="bank">
                <div class="form-group">
                    <label class="form-label">Transaction Reference Number</label>
                    <input type="text" name="transaction_id" class="form-control" placeholder="Enter your bank ref #" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email (optional, for receipt)</label>
                    <input type="email" name="receipt_email" class="form-control" placeholder="your@email.com" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-accent">I've Made the Transfer</button>
                    <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
        </div>
    </div>
</body>
</html>
