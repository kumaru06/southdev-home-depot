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
    $paymentModel = new Payment($pdo);
    $payment = $paymentModel->getByOrderId($orderId);
    if ($payment) {
        $paymentModel->updateStatus($payment['id'], PAYMENT_FAILED);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --charcoal:#1C1C1C; --accent:#C62828; --neutral:#F4F4F4; --white:#FFFFFF; --steel:#4A4A4A; }
        body { font-family:'Inter',sans-serif; background:var(--neutral); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .result-card { background:var(--white); border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.08); max-width:480px; width:100%; padding:3rem 2.5rem; text-align:center; }
        .error-icon { width:80px; height:80px; border-radius:50%; background:#fdecea; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; }
        .error-icon i { color:var(--accent); }
        .result-card h2 { font-size:1.5rem; font-weight:700; color:var(--charcoal); margin-bottom:.5rem; }
        .result-card .subtitle { color:var(--steel); font-size:.9rem; line-height:1.5; margin-bottom:1.5rem; }
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
        <div class="error-icon">
            <i data-lucide="x-circle" style="width:40px;height:40px;"></i>
        </div>
        <h2>Payment Failed</h2>
        <p class="subtitle">
            <?php
            switch ($reason) {
                case 'invalid_order':  echo 'The order could not be found. Please try again.'; break;
                case 'invalid_token':  echo 'Security verification failed. Please try again.'; break;
                case 'cancelled':      echo 'Your payment was cancelled. You can retry from your orders page.'; break;
                default:               echo 'Something went wrong. Please try again later.';
            }
            ?>
        </p>

        <div class="btn-group">
            <?php if ($orderId): ?>
                <a href="<?= APP_URL ?>/index.php?url=orders/<?= $orderId ?>" class="btn btn-accent btn-block">
                    <i data-lucide="eye" style="width:16px;height:16px;"></i> View Order
                </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/index.php?url=orders" class="btn btn-outline btn-block">
                <i data-lucide="list" style="width:16px;height:16px;"></i> My Orders
            </a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
