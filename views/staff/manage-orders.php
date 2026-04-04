<?php
/* $pageTitle, $extraCss, $isAdmin set by OrderController::manage() */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="page-content">
        <!-- Filter Bar -->
        <div class="card filter-bar">
            <form method="GET" class="filter-form">
                <input type="hidden" name="url" value="staff/orders">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= (isset($_GET['status']) && $_GET['status'] == $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-accent"><i data-lucide="filter"></i> Filter</button>
            </form>
        </div>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Cancel Reason</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <?php
                                        $pmText = 'N/A';
                                        $pmLogo = APP_URL . '/assets/uploads/images/logo/creditcard.png';
                                        if (!empty($order['payment_method'])) {
                                            $pmRaw = strtolower($order['payment_method']);
                                            if (str_contains($pmRaw, 'gcash')) {
                                                $pmText = 'GCash';
                                                $pmLogo = APP_URL . '/assets/uploads/images/logo/gcashlogo.png';
                                            } elseif (str_contains($pmRaw, 'cod') || str_contains($pmRaw, 'cash')) {
                                                $pmText = 'COD';
                                                $pmLogo = APP_URL . '/assets/uploads/images/logo/COD2.png';
                                            } elseif (str_contains($pmRaw, 'card') || str_contains($pmRaw, 'paymongo')) {
                                                $pmText = 'Card';
                                                $pmLogo = APP_URL . '/assets/uploads/images/logo/creditcard.png';
                                            } elseif (str_contains($pmRaw, 'ewallet') || str_contains($pmRaw, 'e-wallet')) {
                                                $pmText = 'E-Wallet';
                                                $pmLogo = APP_URL . '/assets/uploads/images/logo/gcashlogo.png';
                                            } else {
                                                $pmText = ucfirst($order['payment_method']);
                                            }
                                        }
                                    ?>
                                    <span class="payment-method-badge">
                                        <img src="<?= $pmLogo ?>" alt="<?= $pmText ?>" class="payment-logo-icon">
                                        <?= $pmText ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                                    <?php
                                        // Show return request badge for delivered orders
                                        $rr = $returnsByOrder[$order['id']] ?? null;
                                        if ($rr && $order['status'] === 'delivered'):
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
                                                default     => 'Return ' . ucfirst($rr['status']),
                                            };
                                    ?>
                                        <span class="return-badge <?= $rrBadgeCls ?>" style="font-size:11px;"><?= $rrBadgeLbl ?></span>
                                    <?php endif; ?>
                                    <?php
                                        // Show cancel request badge for processing orders
                                        $cr = $cancelsByOrder[$order['id']] ?? null;
                                        if ($cr && $order['status'] === 'processing'):
                                            $crBadgeCls = match($cr['status']) {
                                                'pending'  => 'return-badge--pending',
                                                'approved' => 'return-badge--approved',
                                                'rejected' => 'return-badge--rejected',
                                                default    => 'return-badge--pending',
                                            };
                                            $crBadgeLbl = match($cr['status']) {
                                                'pending'  => 'Cancel Pending',
                                                'approved' => 'Cancel Approved',
                                                'rejected' => 'Cancel Rejected',
                                                default    => 'Cancel ' . ucfirst($cr['status']),
                                            };
                                    ?>
                                        <span class="return-badge <?= $crBadgeCls ?>" style="font-size:11px;"><?= $crBadgeLbl ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($order['status'] ?? '') === 'cancelled' && !empty($order['cancel_reason'])): ?>
                                        <span title="<?= htmlspecialchars($order['cancel_reason']) ?>">
                                            <?= htmlspecialchars(strlen($order['cancel_reason']) > 50 ? substr($order['cancel_reason'], 0, 50) . '…' : $order['cancel_reason']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="<?= APP_URL ?>/index.php?url=staff/orders/<?= $order['id'] ?>" class="action-btn view"><i data-lucide="eye"></i> View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
