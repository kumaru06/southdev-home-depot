<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$steps      = ['pending', 'processing', 'shipped', 'delivered'];
$stepIcons  = ['clock', 'settings', 'truck', 'check-circle'];
$currentIdx = array_search($order['status'], $steps);
if ($order['status'] === 'cancelled') $currentIdx = -1;

$statusColors = [
    'pending'    => '#f59e0b',
    'processing' => '#3b82f6',
    'shipped'    => '#8b5cf6',
    'delivered'  => '#10b981',
    'cancelled'  => '#ef4444',
];
$statusColor = $statusColors[$order['status']] ?? '#6b7280';
?>

<div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="<?= APP_URL ?>/index.php?url=orders">Profile</a>
        <i data-lucide="chevron-right"></i>
        <span><?= htmlspecialchars($order['order_number']) ?></span>
    </nav>

    <!-- Hero card with order header -->
    <div class="od-hero" style="--status-clr: <?= $statusColor ?>">
        <div class="od-hero-top">
            <div class="od-hero-info">
                <span class="od-order-number"><?= htmlspecialchars($order['order_number']) ?></span>
                <div class="od-hero-meta">
                    <span><i data-lucide="calendar" style="width:14px;height:14px"></i> <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?></span>
                    <span><i data-lucide="package" style="width:14px;height:14px"></i> <?= count($orderItems) ?> item<?= count($orderItems) !== 1 ? 's' : '' ?></span>
                </div>
            </div>
            <div class="od-hero-status">
                <span class="badge badge-<?= $order['status'] ?> badge-lg"><?= ucfirst($order['status']) ?></span>
                <span class="od-hero-total">₱<?= number_format($order['total_amount'], 2) ?></span>
            </div>
        </div>

        <!-- Horizontal Timeline -->
        <?php if ($order['status'] !== 'cancelled'): ?>
        <div class="od-timeline">
            <div class="od-timeline-track">
                <div class="od-timeline-fill" style="width: <?= $currentIdx !== false ? ($currentIdx / (count($steps)-1) * 100) : 0 ?>%"></div>
            </div>
            <?php foreach ($steps as $i => $step):
                $done    = $currentIdx !== false && $i <= $currentIdx;
                $active  = $i === $currentIdx;
            ?>
            <div class="od-timeline-step <?= $done ? 'done' : '' ?> <?= $active ? 'active' : '' ?>" style="left: <?= ($i / (count($steps)-1)) * 100 ?>%">
                <div class="od-step-dot">
                    <i data-lucide="<?= $stepIcons[$i] ?>" style="width:14px;height:14px"></i>
                </div>
                <span class="od-step-label"><?= ucfirst($step) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="od-cancelled-banner">
            <i data-lucide="x-octagon" style="width:18px;height:18px"></i>
            <span>This order has been cancelled</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info Grid -->
    <div class="od-grid">
        <!-- Order Info -->
        <div class="od-card od-card--info">
            <div class="od-card-header">
                <div class="od-card-icon"><i data-lucide="file-text"></i></div>
                <div>
                    <h3>Order Information</h3>
                    <p>Details about this order</p>
                </div>
            </div>
            <div class="od-card-body">
                <div class="od-detail-row">
                    <span class="od-detail-label">Status</span>
                    <span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                </div>
                <div class="od-detail-row">
                    <span class="od-detail-label">Total Amount</span>
                    <span class="od-detail-value od-detail-value--bold">₱<?= number_format($order['total_amount'], 2) ?></span>
                </div>
                <div class="od-detail-row">
                    <span class="od-detail-label">Payment</span>
                    <span class="od-detail-value"><?= ucfirst($order['payment_method'] ?? 'N/A') ?></span>
                </div>
                <?php if (!empty($order['notes'])): ?>
                <div class="od-detail-row">
                    <span class="od-detail-label">Notes</span>
                    <span class="od-detail-value"><?= htmlspecialchars($order['notes']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($order['status'] === 'pending'): ?>
            <div class="od-card-action">
                <div class="od-cancel-section">
                    <h4><i data-lucide="alert-circle" style="width:15px;height:15px"></i> Cancel this order?</h4>
                    <form action="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>/cancel" method="POST" class="js-cancel-order-form">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <select name="cancel_reason" id="cancel_reason" class="form-control js-cancel-reason-select" required>
                                <option value="" selected disabled>Select a reason…</option>
                                <option value="Need to change delivery address">Need to change delivery address</option>
                                <option value="Wrong delivery address">Wrong delivery address</option>
                                <option value="Wrong products ordered">Wrong products ordered</option>
                                <option value="Order placed by mistake">Order placed by mistake</option>
                                <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                                <option value="other">Other (please specify)</option>
                            </select>
                        </div>
                        <div class="form-group js-cancel-reason-other" style="display:none;">
                            <textarea name="cancel_reason_other" id="cancel_reason_other" class="form-control" rows="3" placeholder="Type your reason..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-cancel-order"><i data-lucide="x-circle"></i> Cancel Order</button>
                    </form>
                </div>
            </div>
            <?php elseif ($order['status'] === 'processing'): ?>
            <div class="od-card-action">
                <div class="od-cancel-section">
                    <h4><i data-lucide="alert-triangle" style="width:15px;height:15px"></i> Need to cancel?</h4>
                    <p class="od-cancel-note">Since this order is being processed, cancellation requires approval.</p>
                    <form action="<?= APP_URL ?>/index.php?url=orders/request-cancel/<?= $order['id'] ?>" method="POST" class="js-cancel-request-form">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <select name="cancel_reason" id="cancel_reason" class="form-control js-cancel-reason-select" required>
                                <option value="" selected disabled>Select a reason…</option>
                                <option value="Need to change delivery address">Need to change delivery address</option>
                                <option value="Wrong delivery address">Wrong delivery address</option>
                                <option value="Wrong products ordered">Wrong products ordered</option>
                                <option value="Order placed by mistake">Order placed by mistake</option>
                                <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                                <option value="other">Other (please specify)</option>
                            </select>
                        </div>
                        <div class="form-group js-cancel-reason-other" style="display:none;">
                            <textarea name="cancel_reason_other" id="cancel_reason_other" class="form-control" rows="3" placeholder="Type your reason..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning"><i data-lucide="alert-triangle"></i> Request Cancellation</button>
                    </form>
                </div>
            </div>
            <?php elseif ($order['status'] === 'delivered'): ?>
            <div class="od-card-action">
                <a href="<?= APP_URL ?>/index.php?url=returns/request/<?= $order['id'] ?>" class="btn btn-outline btn-block">
                    <i data-lucide="rotate-ccw"></i> Request Return
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Shipping Address -->
        <div class="od-card od-card--shipping">
            <div class="od-card-header">
                <div class="od-card-icon od-card-icon--purple"><i data-lucide="map-pin"></i></div>
                <div>
                    <h3>Shipping Address</h3>
                    <p>Delivery destination</p>
                </div>
            </div>
            <div class="od-card-body">
                <div class="od-address-block">
                    <i data-lucide="home" style="width:16px;height:16px;flex-shrink:0;color:var(--text-muted);margin-top:2px"></i>
                    <div>
                        <p class="od-address-line"><?= htmlspecialchars($order['shipping_address']) ?></p>
                        <p class="od-address-sub"><?= htmlspecialchars(implode(', ', array_filter([
                            $order['shipping_city'] ?? '',
                            $order['shipping_state'] ?? '',
                            $order['shipping_zip'] ?? ''
                        ]))) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="od-card od-card--items">
        <div class="od-card-header">
            <div class="od-card-icon od-card-icon--green"><i data-lucide="shopping-bag"></i></div>
            <div>
                <h3>Order Items</h3>
                <p><?= count($orderItems) ?> product<?= count($orderItems) !== 1 ? 's' : '' ?> in this order</p>
            </div>
        </div>
        <div class="od-items-list">
            <?php foreach ($orderItems as $idx => $item): ?>
            <div class="od-item-row" style="animation-delay: <?= $idx * 0.04 ?>s">
                <div class="od-item-product">
                    <?php if (!empty($item['image'])): ?>
                    <img src="<?= APP_URL ?>/assets/uploads/<?= $item['image'] ?>" class="od-item-thumb" alt="">
                    <?php else: ?>
                    <div class="od-item-thumb-placeholder"><i data-lucide="image" style="width:20px;height:20px"></i></div>
                    <?php endif; ?>
                    <div class="od-item-info">
                        <span class="od-item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                        <span class="od-item-unit">₱<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></span>
                    </div>
                </div>
                <span class="od-item-subtotal">₱<?= number_format($item['subtotal'], 2) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="od-items-footer">
            <div class="od-total-row">
                <span>Subtotal</span>
                <span>₱<?= number_format($order['total_amount'], 2) ?></span>
            </div>
            <div class="od-total-row od-total-row--grand">
                <span>Total</span>
                <span>₱<?= number_format($order['total_amount'], 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Back button -->
    <div class="od-back-row">
        <a href="<?= APP_URL ?>/index.php?url=orders" class="btn btn-outline">
            <i data-lucide="arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
