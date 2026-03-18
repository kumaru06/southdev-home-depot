<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="<?= APP_URL ?>/index.php?url=orders">Profile</a>
        <i class="lucide-chevron-right"></i>
        <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>"><?= htmlspecialchars($order['order_number']) ?></a>
        <i class="lucide-chevron-right"></i>
        <span>Request Return</span>
    </nav>

    <h1 class="page-heading"><i data-lucide="rotate-ccw"></i> Request Return</h1>

    <div class="card">
        <div class="detail-row"><span>Order</span><strong><?= htmlspecialchars($order['order_number']) ?></strong></div>
        <div class="detail-row"><span>Order Date</span><span><?= date('M d, Y', strtotime($order['created_at'])) ?></span></div>
        <div class="detail-row"><span>Total</span><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></div>

        <form action="<?= APP_URL ?>/index.php?url=returns/submit" method="POST" style="margin-top: 24px;">
            <?= csrf_field() ?>
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

            <div class="form-group">
                <label for="reason">Reason for Return <span class="required">*</span></label>
                <textarea id="reason" name="reason" class="form-control" rows="5" required placeholder="Please describe the reason for your return request…"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-accent"><i data-lucide="send"></i> Submit Request</button>
                <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
