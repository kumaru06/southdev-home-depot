<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="<?= APP_URL ?>/index.php?url=orders">My Orders</a>
        <i class="lucide-chevron-right"></i>
        <span><?= htmlspecialchars($order['order_number']) ?></span>
    </nav>

    <div class="order-detail-header">
        <div>
            <h1>Order <?= htmlspecialchars($order['order_number']) ?></h1>
            <span class="order-date"><i data-lucide="calendar"></i> <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?></span>
        </div>
        <span class="badge badge-<?= $order['status'] ?> badge-lg"><?= ucfirst($order['status']) ?></span>
    </div>

    <!-- Order Timeline -->
    <div class="order-timeline">
        <?php
        $steps = ['pending', 'processing', 'shipped', 'delivered'];
        $currentIdx = array_search($order['status'], $steps);
        if ($order['status'] === 'cancelled') $currentIdx = -1;
        foreach ($steps as $i => $step):
            $done = $currentIdx !== false && $i <= $currentIdx;
        ?>
            <div class="timeline-step <?= $done ? 'completed' : '' ?> <?= ($i === $currentIdx) ? 'current' : '' ?>">
                <div class="step-dot"></div>
                <span class="step-label"><?= ucfirst($step) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="order-detail-grid">
        <!-- Order Info -->
        <div class="card">
            <h3><i data-lucide="info"></i> Order Information</h3>
            <div class="detail-row"><span>Status</span><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></div>
            <div class="detail-row"><span>Total Amount</span><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></div>
            <?php if (!empty($order['notes'])): ?>
                <div class="detail-row"><span>Notes</span><span><?= htmlspecialchars($order['notes']) ?></span></div>
            <?php endif; ?>

            <?php if ($order['status'] === 'pending'): ?>
                <form action="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>/cancel" method="POST" class="js-cancel-order-form" style="margin-top: 16px;">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="cancel_reason">Reason for cancellation</label>
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
                        <label for="cancel_reason_other">Please specify</label>
                        <textarea name="cancel_reason_other" id="cancel_reason_other" class="form-control" rows="3" placeholder="Type your reason..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block btn-cancel-order"><i data-lucide="x-circle"></i> Cancel Order</button>
                </form>
            <?php elseif ($order['status'] === 'processing'): ?>
                <form action="<?= APP_URL ?>/index.php?url=orders/request-cancel/<?= $order['id'] ?>" method="POST" class="js-cancel-request-form" style="margin-top: 16px;">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="cancel_reason">Reason for cancellation</label>
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
                        <label for="cancel_reason_other">Please specify</label>
                        <textarea name="cancel_reason_other" id="cancel_reason_other" class="form-control" rows="3" placeholder="Type your reason..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning btn-block"><i data-lucide="alert-triangle"></i> Request Cancellation</button>
                </form>
            <?php elseif ($order['status'] === 'delivered'): ?>
                <a href="<?= APP_URL ?>/index.php?url=returns/request/<?= $order['id'] ?>" class="btn btn-outline btn-block" style="margin-top: 16px;">
                    <i data-lucide="rotate-ccw"></i> Request Return
                </a>
            <?php endif; ?>
        </div>

        <!-- Shipping Address -->
        <div class="card">
            <h3><i data-lucide="map-pin"></i> Shipping Address</h3>
            <p><?= htmlspecialchars($order['shipping_address']) ?></p>
            <p><?= htmlspecialchars(implode(', ', array_filter([
                $order['shipping_city'] ?? '',
                $order['shipping_state'] ?? '',
                $order['shipping_zip'] ?? ''
            ]))) ?></p>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card">
        <h3><i data-lucide="list"></i> Order Items</h3>
        <div class="data-table-wrap">
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <img src="<?= APP_URL ?>/assets/uploads/<?= $item['image'] ?: 'placeholder.svg' ?>" class="cart-thumb" alt="">
                                    <span><?= htmlspecialchars($item['product_name']) ?></span>
                                </div>
                            </td>
                            <td>₱<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><strong>₱<?= number_format($item['subtotal'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                        <td><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <a href="<?= APP_URL ?>/index.php?url=orders" class="btn btn-outline"><i data-lucide="arrow-left"></i> Back to Orders</a>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
