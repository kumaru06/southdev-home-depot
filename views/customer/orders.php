<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="page-heading-row">
        <h1 class="page-heading"><i data-lucide="package"></i> My Orders</h1>
        <?php if (!empty($orders)): ?>
            <span class="page-heading-badge"><?= count($orders) ?> order<?= count($orders) > 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="orders-list">
        <?php foreach ($orders as $idx => $order): ?>
            <div class="order-card order-card--enhanced" style="animation-delay: <?= $idx * 0.05 ?>s">
                <div class="order-card-status-stripe order-card-status-stripe--<?= $order['status'] ?>"></div>
                <div class="order-card-content">
                    <div class="order-card-header">
                        <div class="order-card-main">
                            <div class="order-card-top-row">
                                <h3 class="order-number"><?= htmlspecialchars($order['order_number']) ?></h3>
                                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                    <span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                                    <?php
                                        $rr = $returnsByOrder[$order['id']] ?? null;
                                        // Only show return badges on delivered orders (returns don't apply to other statuses)
                                        if ($rr && $rr['status'] !== 'rejected' && $order['status'] === 'delivered'):
                                            $rrBadgeCls = match($rr['status']) {
                                                'pending'   => 'return-badge--pending',
                                                'approved'  => 'return-badge--approved',
                                                'completed' => 'return-badge--refunded',
                                                default     => 'return-badge--pending',
                                            };
                                            $rrBadgeLbl = match($rr['status']) {
                                                'pending'   => 'Return Pending',
                                                'approved'  => 'Return Approved',
                                                'completed' => 'Refunded',
                                                default     => 'Return Pending',
                                            };
                                    ?>
                                        <span class="return-badge <?= $rrBadgeCls ?>" style="font-size:11px;">
                                            <?= $rrBadgeLbl ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="order-card-meta">
                                <span class="order-meta-item"><i data-lucide="calendar"></i> <?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                <span class="order-meta-item"><i data-lucide="clock"></i> <?= date('h:i A', strtotime($order['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="order-card-amount">
                            <span class="order-total-label">Total</span>
                            <span class="order-total">₱<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>
                    <div class="order-card-actions">
                        <?php
                            $pmLabel = '';
                            $pmLogo  = '';
                            if (!empty($order['payment_method'])) {
                                $pmRaw = strtolower($order['payment_method']);
                                if (str_contains($pmRaw, 'gcash')) {
                                    $pmLabel = 'GCash';
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/gcashlogo.png';
                                } elseif (str_contains($pmRaw, 'cod') || str_contains($pmRaw, 'cash')) {
                                    $pmLabel = 'COD';
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/COD2.png';
                                } elseif (str_contains($pmRaw, 'card') || str_contains($pmRaw, 'paymongo')) {
                                    $pmLabel = 'Card';
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/creditcard.png';
                                } elseif (str_contains($pmRaw, 'ewallet') || str_contains($pmRaw, 'e-wallet')) {
                                    $pmLabel = 'E-Wallet';
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/gcashlogo.png';
                                } else {
                                    $pmLabel = ucfirst($order['payment_method']);
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/creditcard.png';
                                }
                            }
                        ?>
                        <?php if ($pmLabel): ?>
                        <span class="order-payment-badge">
                            <img src="<?= $pmLogo ?>" alt="<?= $pmLabel ?>" class="payment-logo-icon"> <?= $pmLabel ?>
                        </span>
                        <?php endif; ?>
                        <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>" class="btn btn-outline btn-sm">
                            <i data-lucide="eye"></i> View Details
                        </a>
                        <?php if ($order['status'] === 'pending'): ?>
                            <form action="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>/cancel" method="POST" class="inline-form" onsubmit="return confirm('Cancel this order?');">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger btn-sm"><i data-lucide="x-circle"></i> Cancel</button>
                            </form>
                        <?php elseif ($order['status'] === 'processing'): ?>
                            <?php
                                $cr = $cancelsByOrder[$order['id']] ?? null;
                                $hasActiveCancel = $cr && in_array($cr['status'], ['pending', 'approved']);
                            ?>
                            <?php if ($hasActiveCancel): ?>
                                <span class="badge badge-<?= $cr['status'] === 'approved' ? 'cancelled' : 'pending' ?>" style="font-size:11px;">
                                    <?= $cr['status'] === 'pending' ? 'Cancel Pending' : 'Cancel Approved' ?>
                                </span>
                            <?php else: ?>
                            <a href="<?= APP_URL ?>/index.php?url=orders/request-cancel/<?= $order['id'] ?>" class="btn btn-warning btn-sm">
                                <i data-lucide="alert-triangle"></i> Request Cancellation
                            </a>
                            <?php endif; ?>
                        <?php elseif ($order['status'] === 'delivered'): ?>
                            <?php
                                // Only show "Request Return" if no active return request exists
                                $hasActiveReturn = isset($rr) && $rr && $rr['status'] !== 'rejected';
                            ?>
                            <?php if (!$hasActiveReturn): ?>
                            <a href="<?= APP_URL ?>/index.php?url=returns/request/<?= $order['id'] ?>" class="btn btn-outline btn-sm">
                                <i data-lucide="rotate-ccw"></i> Request Return
                            </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state empty-state--orders">
            <div class="empty-state-icon-wrap">
                <i data-lucide="clipboard-list"></i>
            </div>
            <h3>No orders yet</h3>
            <p>Once you place an order, it will appear here. Start shopping to see your orders!</p>
            <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent btn-lg">
                <i data-lucide="store"></i> Browse Products
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
