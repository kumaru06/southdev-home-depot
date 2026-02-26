<?php
/**
 * Payment Success
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

$orderId = intval($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
$transactionId = $_POST['transaction_id'] ?? null;

// CSRF check for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        header('Location: ' . APP_URL . '/payment/payment-failed.php?reason=invalid_token');
        exit;
    }
}

if ($orderId) {
    $paymentModel = new Payment($pdo);
    $orderModel = new Order($pdo);

    // Verify ownership
    $order = $orderModel->findById($orderId);
    if (!$order || $order['user_id'] != $_SESSION['user_id']) {
        header('Location: ' . APP_URL . '/payment/payment-failed.php?reason=invalid_order');
        exit;
    }

    // Update payment status
    $payment = $paymentModel->getByOrderId($orderId);
    if ($payment) {
        $paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED, $transactionId);
    }

    // Update order status to processing
    $orderModel->updateStatus($orderId, ORDER_PROCESSING);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --charcoal:#1C1C1C; --accent:#C62828; --neutral:#F4F4F4; --white:#FFFFFF; --steel:#4A4A4A; }
        body { font-family:'Inter',sans-serif; background:var(--neutral); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .result-card { background:var(--white); border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.08); max-width:480px; width:100%; padding:3rem 2.5rem; text-align:center; }
        .success-icon { width:80px; height:80px; border-radius:50%; background:#e8f5e9; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; }
        .success-icon i { color:#2e7d32; }
        .result-card h2 { font-size:1.5rem; font-weight:700; color:var(--charcoal); margin-bottom:.5rem; }
        .result-card .subtitle { color:var(--steel); font-size:.9rem; margin-bottom:1.5rem; }
        .order-summary { background:var(--neutral); border-radius:8px; padding:1rem 1.25rem; margin-bottom:1.5rem; text-align:left; }
        .order-summary .row { display:flex; justify-content:space-between; padding:.35rem 0; font-size:.9rem; }
        .order-summary .row .label { color:var(--steel); }
        .order-summary .row .value { font-weight:600; color:var(--charcoal); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; padding:.7rem 1.5rem; border-radius:6px; font-weight:600; font-size:.9rem; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-accent { background:var(--accent); color:var(--white); }
        .btn-accent:hover { background:#a52222; }
        .btn-outline { background:transparent; color:var(--steel); border:1px solid #ddd; }
        .btn-outline:hover { border-color:var(--charcoal); color:var(--charcoal); }
        .btn-group { display:flex; flex-direction:column; gap:.75rem; }
        .btn-block { width:100%; }
    </style>
</head>
<body>
    <div class="result-card">
        <div class="success-icon">
            <i data-lucide="check-circle" style="width:40px;height:40px;"></i>
        </div>
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
                    <span class="value" style="color:#2e7d32;">Processing</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="<?= APP_URL ?>/index.php?url=orders/<?= $orderId ?>" class="btn btn-accent btn-block">
                <i data-lucide="eye" style="width:16px;height:16px;"></i> View Order
            </a>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-outline btn-block">
                <i data-lucide="shopping-bag" style="width:16px;height:16px;"></i> Continue Shopping
            </a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
