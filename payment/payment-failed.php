<?php
/**
 * Payment Failed
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

// Auth check (session already started in config.php)
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/index.php?url=login');
    exit;
}

$orderId = intval($_GET['order_id'] ?? 0);
$reason = $_GET['reason'] ?? 'cancelled';

if ($orderId) {
    require_once __DIR__ . '/../models/Payment.php';
    require_once __DIR__ . '/../models/Order.php';
    require_once __DIR__ . '/../models/Log.php';

    $paymentModel = new Payment($pdo);
    $orderModel = new Order($pdo);

    // Mark payment as failed
    $payment = $paymentModel->getByOrderId($orderId);
    if ($payment) {
        $paymentModel->updateStatus($payment['id'], PAYMENT_FAILED);
    }

    // Auto-cancel the order and restore stock (only for online payment failures)
    $order = $orderModel->findById($orderId);
    if ($order && $order['status'] === 'pending' && $order['user_id'] == $_SESSION['user_id']) {
        $cancelReason = 'Payment failed or cancelled by customer';
        $orderModel->cancelOrder($orderId, $_SESSION['user_id'], $cancelReason);

        // Log the auto-cancellation
        $logModel = new Log($pdo);
        $logModel->create(LOG_ORDER_CANCEL, "Order #{$orderId} ({$order['order_number']}) auto-cancelled: payment failed/cancelled.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        :root{--charcoal:#1C1C1C;--steel:#6B7280;--accent:#F97316;--accent-dark:#EA580C;--neutral:#F5F5F5;--white:#FFFFFF;--border:#E5E7EB;--danger:#DC2626;}
        body{font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
        .result-card{background:var(--white);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06),0 8px 24px rgba(0,0,0,.06);max-width:460px;width:100%;padding:2.5rem;text-align:center;}
        .result-x{width:64px;height:64px;border-radius:50%;background:#FEE2E2;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.75rem;font-weight:800;color:var(--danger);}
        .result-card h2{font-size:1.35rem;font-weight:800;color:var(--charcoal);margin-bottom:.35rem;}
        .result-card .subtitle{color:var(--steel);font-size:.875rem;line-height:1.5;margin-bottom:1.25rem;}
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
        <div class="result-x">&times;</div>
        <h2>Payment Failed</h2>
        <p class="subtitle">
            <?php
            switch ($reason) {
                case 'invalid_order':     echo 'The order could not be found. Please try again.'; break;
                case 'invalid_token':     echo 'Security verification failed. Please try again.'; break;
                case 'cancelled':         echo 'Your payment was cancelled and the order has been automatically cancelled. No charges were made.'; break;
                case 'card_declined':     echo 'Your card was declined. The order has been cancelled. Please try placing a new order with a different payment method.'; break;
                case 'verification_error':echo 'We couldn\'t verify your payment. The order has been cancelled for your safety.'; break;
                default:                  echo 'Something went wrong. The order has been cancelled. Please try again.';
            }
            ?>
        </p>

        <div class="btn-group">
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">Continue Shopping</a>
            <a href="<?= APP_URL ?>/index.php?url=orders" class="btn btn-outline">My Orders</a>
        </div>
    </div>
</body>
</html>
