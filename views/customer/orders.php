<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <h1 class="page-heading"><i data-lucide="package"></i> My Orders</h1>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-card-header">
                    <div>
                        <h3 class="order-number"><?= htmlspecialchars($order['order_number']) ?></h3>
                        <span class="order-date"><i data-lucide="calendar"></i> <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="order-card-right">
                        <span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                        <span class="order-total">₱<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>
                <div class="order-card-actions">
                    <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>" class="btn btn-outline btn-sm">
                        <i data-lucide="eye"></i> View Details
                    </a>
                    <?php if ($order['status'] === 'pending'): ?>
                        <form action="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>/cancel" method="POST" class="inline-form" onsubmit="return confirm('Cancel this order?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-danger btn-sm"><i data-lucide="x-circle"></i> Cancel</button>
                        </form>
                    <?php elseif ($order['status'] === 'processing'): ?>
                        <a href="<?= APP_URL ?>/index.php?url=orders/request-cancel/<?= $order['id'] ?>" class="btn btn-warning btn-sm">
                            <i data-lucide="alert-triangle"></i> Request Cancellation
                        </a>
                    <?php elseif ($order['status'] === 'delivered'): ?>
                        <a href="<?= APP_URL ?>/index.php?url=returns/request/<?= $order['id'] ?>" class="btn btn-outline btn-sm">
                            <i data-lucide="rotate-ccw"></i> Request Return
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i data-lucide="clipboard-list" class="empty-icon"></i>
            <h3>No orders yet</h3>
            <p>Start shopping to see your orders here.</p>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
