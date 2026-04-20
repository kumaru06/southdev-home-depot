<?php
/**
 * Payment Success
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/PayMongoGateway.php';

// Auth check (session already started in config.php)
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/index.php?url=login');
    exit;
}

$orderId       = intval($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
$transactionId = $_POST['transaction_id'] ?? null;

// PayMongo card / 3DS return parameters
$intentId  = $_GET['intent_id']  ?? null;
$clientKey = $_GET['client_key'] ?? null;

// CSRF check for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        header('Location: ' . APP_URL . '/payment/payment-failed.php?reason=invalid_token');
        exit;
    }
}

if ($orderId) {
    $paymentModel = new Payment($pdo);
    $orderModel   = new Order($pdo);

    // Verify ownership
    $order = $orderModel->findById($orderId);
    if (!$order || $order['user_id'] != $_SESSION['user_id']) {
        header('Location: ' . APP_URL . '/payment/payment-failed.php?reason=invalid_order');
        exit;
    }

    // ── Card 3DS return: verify intent status before marking complete ──────
    $isTestMode = isset($_GET['test_mode']) && $_GET['test_mode'] === '1';
    if ($isTestMode) {
        // Test bypass – just mark complete
        $payment = $paymentModel->getByOrderId($orderId);
        if ($payment && $payment['status'] !== PAYMENT_COMPLETED) {
            $paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED, 'test_' . uniqid());
        }
        // Order stays pending — staff will advance to processing
    } elseif ($intentId && $clientKey && defined('PAYMONGO_ENABLED') && PAYMONGO_ENABLED) {
        try {
            $gateway = new PayMongoGateway();
            $intentData = $gateway->getPaymentIntent($intentId, $clientKey);
            $intentStatus = $intentData['data']['attributes']['status'] ?? '';

            if ($intentStatus === 'succeeded') {
                $payment = $paymentModel->getByOrderId($orderId);
                if ($payment && $payment['status'] !== PAYMENT_COMPLETED) {
                    $paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED, $intentId);
                    // Order stays pending — staff will advance to processing
                }
            } elseif ($intentStatus === 'awaiting_payment_method' || $intentStatus === 'payment_error') {
                // 3DS failed / payment was declined
                $payment = $paymentModel->getByOrderId($orderId);
                if ($payment) {
                    $paymentModel->updateStatus($payment['id'], PAYMENT_FAILED);
                }
                header('Location: ' . APP_URL . '/payment/payment-failed.php?order_id=' . $orderId . '&reason=card_declined');
                exit;
            }
            // else: still processing (unlikely at return URL) — fall through to success page
        } catch (Exception $e) {
            // If we can't verify, fail safe
            header('Location: ' . APP_URL . '/payment/payment-failed.php?order_id=' . $orderId . '&reason=verification_error');
            exit;
        }
    } else {
        // COD / bank / GCash (non-3DS) — mark complete as before
        $payment = $paymentModel->getByOrderId($orderId);
        if ($payment && $payment['status'] !== PAYMENT_COMPLETED) {
            $paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED, $transactionId);
        }
        // Order stays pending — staff will advance to processing
    }
}

$order = (new Order($pdo))->findById($orderId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        :root{--charcoal:#1C1C1C;--steel:#6B7280;--accent:#F97316;--accent-dark:#EA580C;--neutral:#F5F5F5;--white:#FFFFFF;--border:#E5E7EB;--success:#16A34A;}
        body{font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
        .result-card{background:var(--white);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06),0 8px 24px rgba(0,0,0,.06);max-width:460px;width:100%;padding:2.5rem;text-align:center;}
        .result-check{width:64px;height:64px;border-radius:50%;background:#DCFCE7;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.75rem;color:var(--success);}
        .result-card h2{font-size:1.35rem;font-weight:800;color:var(--charcoal);margin-bottom:.35rem;}
        .result-card .subtitle{color:var(--steel);font-size:.875rem;margin-bottom:1.25rem;}
        .order-summary{background:var(--neutral);border-radius:8px;padding:.85rem 1.15rem;margin-bottom:1.25rem;text-align:left;}
        .order-summary .row{display:flex;justify-content:space-between;padding:.3rem 0;font-size:.875rem;}
        .order-summary .row .label{color:var(--steel);}
        .order-summary .row .value{font-weight:700;color:var(--charcoal);}
        .btn{display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.7rem 1.25rem;border-radius:8px;font-weight:700;font-size:.875rem;cursor:pointer;border:none;text-decoration:none;transition:all .15s;font-family:inherit;width:100%;}
        .btn-accent{background:var(--accent);color:var(--white);}
        .btn-accent:hover{background:var(--accent-dark);}
        .btn-outline{background:transparent;color:var(--steel);border:1.5px solid var(--border);}
        .btn-outline:hover{border-color:var(--charcoal);color:var(--charcoal);}
        .btn-group{display:flex;flex-direction:column;gap:.6rem;}
    </style>
</head>
<body>
    <div class="result-card">
        <div class="result-check">&check;</div>
        <h2>Payment Successful!</h2>
        <p class="subtitle">Thank you for your purchase.</p>

        <?php if ($order): ?>
            <div class="order-summary">
                <div class="row">
                    <span class="label">Order</span>
                    <span class="value">#<?= htmlspecialchars($order['order_number']) ?></span>
                </div>
                <div class="row">
                    <span class="label">Total</span>
                    <span class="value">₱<?= number_format($order['total_amount'], 2) ?></span>
                </div>
                <div class="row">
                    <span class="label">Status</span>
                    <span class="value" style="color:var(--accent);">Pending</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="<?= APP_URL ?>/index.php?url=orders/<?= $orderId ?>" class="btn btn-accent">View Order</a>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-outline">Continue Shopping</a>
        </div>
    </div>
</body>
</html>
