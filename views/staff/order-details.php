<?php
/* $pageTitle, $extraCss, $isAdmin set by OrderController::show() */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>Order <?= htmlspecialchars($order['order_number']) ?></h2>
        </div>
        <div class="top-bar-right">
            <span class="badge badge-<?= $order['status'] ?> badge-lg"><?= ucfirst($order['status']) ?></span>
        </div>
    </div>

    <div class="page-content">
        <div class="order-detail-grid">
            <!-- Order Info -->
            <div class="card order-info-card">
                <h3><i data-lucide="info"></i> Order Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><strong><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?= htmlspecialchars($order['email']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total:</span>
                    <span class="detail-value"><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value"><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment:</span>
                    <span class="detail-value">
                        <?php
                            $pmLabel = 'N/A';
                            $pmLogo  = APP_URL . '/assets/uploads/images/logo/creditcard.png';
                            if (!empty($payment['payment_method'])) {
                                $pmRaw = strtolower($payment['payment_method']);
                                if (str_contains($pmRaw, 'gcash')) {
                                    $pmLabel = 'GCash';
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/gcashlogo.png';
                                } elseif (str_contains($pmRaw, 'cod') || str_contains($pmRaw, 'cash')) {
                                    $pmLabel = 'Cash on Delivery';
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/COD.png';
                                } elseif (str_contains($pmRaw, 'card') || str_contains($pmRaw, 'paymongo')) {
                                    $pmLabel = 'Credit / Debit Card';
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/creditcard.png';
                                } elseif (str_contains($pmRaw, 'ewallet') || str_contains($pmRaw, 'e-wallet')) {
                                    $pmLabel = 'E-Wallet';
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/gcashlogo.png';
                                } else {
                                    $pmLabel = ucfirst($payment['payment_method']);
                                }
                            }
                        ?>
                        <span style="display:inline-flex;align-items:center;gap:5px;">
                            <img src="<?= $pmLogo ?>" alt="<?= htmlspecialchars($pmLabel) ?>" class="payment-logo-icon">
                            <strong><?= htmlspecialchars($pmLabel) ?></strong>
                        </span>
                    </span>
                </div>
                <?php if (!empty($payment['status'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value">
                        <span class="badge badge-<?= $payment['status'] === 'completed' ? 'delivered' : ($payment['status'] === 'failed' ? 'cancelled' : 'pending') ?>">
                            <?= ucfirst($payment['status']) ?>
                        </span>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (($order['status'] ?? '') === 'cancelled' && !empty($order['cancel_reason'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Cancel for Reason:</span>
                        <span class="detail-value"><?= htmlspecialchars($order['cancel_reason']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Update Status -->
            <div class="card">
                <h3><i data-lucide="settings"></i> Update Status</h3>
                <form action="<?= APP_URL ?>/index.php?url=staff/orders/<?= $order['id'] ?>/status" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="status">New Status</label>
                        <select name="status" id="status" class="form-control">
                            <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $order['status'] == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-accent btn-block"><i data-lucide="check"></i> Update Status</button>
                </form>

                <h3 style="margin-top: 24px;"><i data-lucide="map-pin"></i> Shipping Address</h3>
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
                <table class="data-table">
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
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
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

        <a href="<?= APP_URL ?>/index.php?url=staff/orders" class="btn btn-outline"><i data-lucide="arrow-left"></i> Back to Orders</a>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
