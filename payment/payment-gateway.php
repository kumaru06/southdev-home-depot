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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --charcoal:#1C1C1C; --graphite:#2E2E2E; --steel:#4A4A4A; --accent:#C62828; --neutral:#F4F4F4; --white:#FFFFFF; }
        body { font-family:'Inter',sans-serif; background:var(--neutral); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .payment-card { background:var(--white); border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.08); max-width:480px; width:100%; padding:2.5rem; text-align:center; }
        .payment-brand { font-size:1.5rem; font-weight:800; color:var(--charcoal); margin-bottom:1.5rem; }
        .payment-brand span { color:var(--accent); }
        .payment-amount { font-size:2.25rem; font-weight:800; color:var(--charcoal); margin:.5rem 0; }
        .payment-order { color:var(--steel); font-size:.9rem; margin-bottom:1.25rem; }
        .payment-method { display:inline-block; background:var(--neutral); color:var(--charcoal); padding:.35rem 1rem; border-radius:20px; font-size:.85rem; font-weight:600; margin-bottom:1.5rem; }
        .divider { border:none; border-top:1px solid var(--neutral); margin:1.5rem 0; }
        .payment-info { background:var(--neutral); border-radius:8px; padding:1.25rem; margin-bottom:1.5rem; text-align:left; }
        .payment-info p { margin:.25rem 0; color:var(--charcoal); font-size:.9rem; }
        .payment-info .highlight { font-size:1.35rem; font-weight:700; color:var(--charcoal); text-align:center; letter-spacing:1px; margin:.75rem 0; }
        .form-group { text-align:left; margin-bottom:1rem; }
        .form-label { display:block; font-weight:600; font-size:.85rem; color:var(--charcoal); margin-bottom:.35rem; }
        .form-control { width:100%; padding:.65rem .85rem; border:1px solid #ddd; border-radius:6px; font-size:.9rem; font-family:inherit; box-sizing:border-box; }
        .form-control:focus { border-color:var(--accent); outline:none; box-shadow:0 0 0 3px rgba(198,40,40,.1); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; padding:.7rem 1.5rem; border-radius:6px; font-weight:600; font-size:.9rem; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-accent { background:var(--accent); color:var(--white); }
        .btn-accent:hover { background:#a52222; }
        .btn-outline { background:transparent; color:var(--steel); border:1px solid #ddd; }
        .btn-outline:hover { border-color:var(--charcoal); color:var(--charcoal); }
        .btn-block { width:100%; }
        .btn-group { display:flex; flex-direction:column; gap:.75rem; margin-top:1rem; }
    </style>
</head>
<body>
    <div class="payment-card">
        <div class="payment-brand">South<span>Dev</span> Home Depot</div>
        <p class="payment-order">Order #<?= htmlspecialchars($order['order_number']) ?></p>
        <div class="payment-amount">₱<?= number_format($order['total_amount'], 2) ?></div>
        <div class="payment-method"><?= ucfirst($method) ?></div>

        <hr class="divider">

        <?php if ($method === 'cod'): ?>
            <p style="color:var(--steel); line-height:1.6; margin-bottom:1.5rem;">
                Your order will be prepared and payment will be collected upon delivery. Please have the exact amount ready.
            </p>
            <div class="btn-group">
                <a href="<?= APP_URL ?>/payment/payment-success.php?order_id=<?= $orderId ?>&method=cod&csrf_token=<?= urlencode(csrf_token()) ?>" class="btn btn-accent btn-block">
                    <i data-lucide="check-circle" style="width:18px;height:18px;"></i> Confirm Order
                </a>
                <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline btn-block">Cancel</a>
            </div>

        <?php elseif ($method === 'gcash'): ?>
            <?php if (defined('PAYMONGO_ENABLED') && PAYMONGO_ENABLED): ?>
                <!-- PayMongo GCash Integration -->
                <div id="gcash-loading" style="text-align:center; padding:2rem;">
                    <i data-lucide="loader" style="width:40px;height:40px;animation:spin 1s linear infinite;margin-bottom:1rem;"></i>
                    <p style="color:var(--steel); margin-bottom:.5rem;">Preparing payment...</p>
                    <p style="color:var(--steel); font-size:.85rem;">Redirecting to GCash via PayMongo...</p>
                </div>
                <div id="gcash-error" style="display:none; text-align:center; padding:1rem;">
                    <p style="color:var(--accent); font-weight:600; margin-bottom:1rem;">Payment initialization failed</p>
                    <p id="gcash-error-msg" style="color:var(--steel); font-size:.9rem; margin-bottom:1.5rem;"></p>
                    <div class="btn-group">
                        <button onclick="initGcashPayment()" class="btn btn-accent btn-block">Try Again</button>
                        <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline btn-block">Cancel</a>
                    </div>
                </div>

                <script>
                    function initGcashPayment() {
                        document.getElementById('gcash-loading').style.display = 'block';
                        document.getElementById('gcash-error').style.display = 'none';

                        fetch('<?= APP_URL ?>/index.php?url=payment/create-source', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                order_id: <?= $orderId ?>,
                                method: 'gcash'
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

                    document.addEventListener('DOMContentLoaded', initGcashPayment);
                </script>
                <style>@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>
            <?php else: ?>
                <p style="color:var(--accent); text-align:center; padding:2rem;">GCash payments require PayMongo to be enabled. Please contact the administrator.</p>
                <div class="btn-group">
                    <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline btn-block">Go Back</a>
                </div>
            <?php endif; ?>

        <?php elseif ($method === 'bank'): ?>
            <div class="payment-info">
                <p><strong>Bank:</strong> BDO</p>
                <p><strong>Account:</strong> 1234-5678-9012</p>
                <p><strong>Name:</strong> SouthDev Home Depot</p>
                <p style="text-align:center; font-size:.8rem; color:var(--steel); margin-top:.5rem;">Ref: <?= htmlspecialchars($order['order_number']) ?></p>
            </div>
            <form action="<?= APP_URL ?>/payment/payment-success.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                <input type="hidden" name="method" value="bank">
                <div class="form-group">
                    <label class="form-label">Transaction Reference Number</label>
                    <input type="text" name="transaction_id" class="form-control" placeholder="Enter your bank ref #" required>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-accent btn-block">
                        <i data-lucide="check-circle" style="width:18px;height:18px;"></i> I've Made the Transfer
                    </button>
                    <a href="<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>" class="btn btn-outline btn-block">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
