<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$ordersForStats = is_array($ordersForStats ?? null) ? $ordersForStats : ($orders ?? []);
$orderCount = count($ordersForStats);
$activeOrderCount = 0;
$deliveredOrderCount = 0;
$cancelledOrderCount = 0;

foreach ($ordersForStats as $orderSummary) {
    $status = strtolower((string) ($orderSummary['status'] ?? ''));
    if (in_array($status, ['pending', 'processing', 'shipped'], true)) {
        $activeOrderCount++;
    }
    if ($status === 'delivered') {
        $deliveredOrderCount++;
    }
    if ($status === 'cancelled') {
        $cancelledOrderCount++;
    }
}

$visibleOrderCount = is_array($orders ?? null) ? count($orders) : 0;
?>

<div class="container orders-page">
    <section class="orders-hero-panel">
        <div class="orders-hero-copy">
            <div class="page-heading-row orders-heading-row">
                <h1 class="page-heading">My Orders</h1>
                <?php if ($orderCount > 0): ?>
                    <span class="page-heading-badge"><?= $orderCount ?> order<?= $orderCount > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>
            <p class="orders-hero-subtitle">Track every purchase, review payment methods, and open any order to see its full item and delivery details.</p>
        </div>

        <?php if ($orderCount > 0): ?>
            <div class="orders-hero-stats" aria-label="Order overview">
                <div class="orders-stat-card">
                    <strong><?= $orderCount ?></strong>
                    <span>Total orders</span>
                </div>
                <div class="orders-stat-card">
                    <strong><?= $activeOrderCount ?></strong>
                    <span>Active</span>
                </div>
                <div class="orders-stat-card">
                    <strong><?= $deliveredOrderCount ?></strong>
                    <span>Delivered</span>
                </div>
                <div class="orders-stat-card">
                    <strong><?= $cancelledOrderCount ?></strong>
                    <span>Cancelled</span>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="orders-toolbar" aria-label="Order filters">
        <form method="GET" action="<?= APP_URL ?>/index.php" class="orders-filter-form">
            <input type="hidden" name="url" value="orders">
            <label for="orderDate" class="orders-filter-label">Find by date</label>
            <input type="date" id="orderDate" name="order_date" class="form-control orders-filter-input" value="<?= htmlspecialchars($selectedOrderDate ?? '') ?>">
            <button type="submit" class="btn btn-accent btn-sm">Apply</button>
            <?php if (!empty($hasOrderDateFilter)): ?>
                <a href="<?= APP_URL ?>/index.php?url=orders" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <div class="orders-toolbar-summary">
            <?php if (!empty($hasOrderDateFilter)): ?>
                <span>Showing <?= $orderCount ?> order<?= $orderCount !== 1 ? 's' : '' ?> for <?= date('M d, Y', strtotime($selectedOrderDate)) ?></span>
            <?php else: ?>
                <span>Showing <?= $visibleOrderCount ?> of <?= $orderCount ?> order<?= $orderCount !== 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($orders)): ?>
        <div class="orders-list">
        <?php foreach ($orders as $order): ?>
            <?php
                $pmLabel = '';
                $pmLogo  = '';
                if (!empty($order['payment_method'])) {
                    $pmRaw = strtolower((string) $order['payment_method']);
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
                        $pmLabel = ucfirst((string) $order['payment_method']);
                        $pmLogo  = APP_URL . '/assets/uploads/images/logo/creditcard.png';
                    }
                }
            ?>
            <div class="order-card order-card--enhanced">
                <div class="order-card-status-stripe order-card-status-stripe--<?= htmlspecialchars($order['status']) ?>"></div>
                <div class="order-card-content">
                    <div class="order-card-header">
                        <div class="order-card-main">
                            <div class="order-card-top-row">
                                <h3 class="order-number"><?= htmlspecialchars($order['order_number']) ?></h3>
                                <div class="order-card-badges">
                                    <span class="badge badge-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars(ucfirst($order['status'])) ?></span>
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
                                <span class="order-meta-item"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                <span class="order-meta-item"><?= date('h:i A', strtotime($order['created_at'])) ?></span>
                                <?php if (!empty($order['payment_method'])): ?>
                                    <span class="order-meta-item">Method <?= htmlspecialchars(ucfirst((string) $order['payment_method'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="order-card-amount">
                            <div class="order-card-amount-box">
                                <span class="order-total-label">Total</span>
                                <span class="order-total">₱<?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="order-card-actions">
                        <div class="order-card-utility">
                            <?php if ($pmLabel): ?>
                            <span class="order-payment-badge">
                                <img src="<?= $pmLogo ?>" alt="<?= $pmLabel ?>" class="payment-logo-icon"> <?= $pmLabel ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <div class="order-card-buttons">
                            <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>" class="btn btn-outline btn-sm order-card-btn">View Details</a>
                            <?php if ($order['status'] === 'pending'): ?>
                                <form action="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>/cancel" method="POST" class="inline-form cancel-order-form">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn btn-danger btn-sm btn-cancel-trigger order-card-btn" data-order="<?= htmlspecialchars($order['order_number']) ?>">Cancel</button>
                                </form>
                            <?php elseif ($order['status'] === 'processing'): ?>
                                <?php
                                    $cr = $cancelsByOrder[$order['id']] ?? null;
                                    $hasActiveCancel = $cr && in_array($cr['status'], ['pending', 'approved']);
                                ?>
                                <?php if ($hasActiveCancel): ?>
                                    <span class="badge badge-<?= $cr['status'] === 'approved' ? 'cancelled' : 'pending' ?> order-card-inline-badge" style="font-size:11px;">
                                        <?= $cr['status'] === 'pending' ? 'Cancel Pending' : 'Cancel Approved' ?>
                                    </span>
                                <?php else: ?>
                                <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>" class="btn btn-warning btn-sm order-card-btn">Request Cancellation</a>
                                <?php endif; ?>
                            <?php elseif ($order['status'] === 'delivered'): ?>
                                <?php
                                    // Only show "Request Return" if no active return request exists
                                    $hasActiveReturn = isset($rr) && $rr && $rr['status'] !== 'rejected';
                                ?>
                                <?php if (!$hasActiveReturn): ?>
                                <a href="<?= APP_URL ?>/index.php?url=returns/request/<?= $order['id'] ?>" class="btn btn-outline btn-sm order-card-btn">Request Return</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="orders-pagination" aria-label="Orders pagination">
                <?php if (($page ?? 1) > 1): ?>
                    <a href="<?= APP_URL ?>/index.php?url=orders&page=<?= $page - 1 ?><?= !empty($selectedOrderDate) ? '&order_date=' . urlencode($selectedOrderDate) : '' ?>" class="btn btn-outline orders-pagination-btn">&laquo; Prev</a>
                <?php else: ?>
                    <span class="btn btn-outline orders-pagination-btn is-disabled" aria-disabled="true">&laquo; Prev</span>
                <?php endif; ?>

                <span class="orders-pagination-status">Page <?= $page ?> of <?= $totalPages ?></span>

                <?php if (($page ?? 1) < $totalPages): ?>
                    <a href="<?= APP_URL ?>/index.php?url=orders&page=<?= $page + 1 ?><?= !empty($selectedOrderDate) ? '&order_date=' . urlencode($selectedOrderDate) : '' ?>" class="btn btn-outline orders-pagination-btn">Next &raquo;</a>
                <?php else: ?>
                    <span class="btn btn-outline orders-pagination-btn is-disabled" aria-disabled="true">Next &raquo;</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state empty-state--orders orders-empty-state">
            <?php if (!empty($hasOrderDateFilter)): ?>
                <h3>No orders found for that date</h3>
                <p>Try another date or clear the filter to see your full order history.</p>
                <a href="<?= APP_URL ?>/index.php?url=orders" class="btn btn-accent btn-lg">Show All Orders</a>
            <?php else: ?>
                <h3>No orders yet</h3>
                <p>Once you place an order, it will appear here. Start shopping to see your orders!</p>
                <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent btn-lg">Browse Products</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Cancel Order Confirmation Modal -->
<div class="cancel-modal-overlay" id="cancelModal">
    <div class="cancel-modal">
        <div class="cancel-modal-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <h3 class="cancel-modal-title">Cancel Order</h3>
        <p class="cancel-modal-text">Are you sure you want to cancel order <strong id="cancelOrderNum"></strong>? This action cannot be undone.</p>
        <div class="cancel-modal-actions">
            <button type="button" class="cancel-modal-btn cancel-modal-btn--no" id="cancelModalNo">Keep Order</button>
            <button type="button" class="cancel-modal-btn cancel-modal-btn--yes" id="cancelModalYes">Yes, Cancel</button>
        </div>
    </div>
</div>

<style>
.cancel-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    animation: cmFadeIn .2s ease;
}
.cancel-modal-overlay.active { display: flex; }

@keyframes cmFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes cmSlideUp { from { opacity: 0; transform: translateY(24px) scale(.96); } to { opacity: 1; transform: translateY(0) scale(1); } }

.cancel-modal {
    background: #fff;
    border-radius: 16px;
    padding: 36px 32px 28px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: cmSlideUp .25s ease forwards;
}

.cancel-modal-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: #FEE2E2;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cancel-modal-title {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 8px;
}

.cancel-modal-text {
    font-size: .9rem;
    color: #64748b;
    line-height: 1.5;
    margin: 0 0 24px;
}
.cancel-modal-text strong {
    color: #1a1a2e;
    font-weight: 600;
}

.cancel-modal-actions {
    display: flex;
    gap: 12px;
}

.cancel-modal-btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s ease;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.cancel-modal-btn--no {
    background: #f1f5f9;
    color: #475569;
}
.cancel-modal-btn--no:hover {
    background: #e2e8f0;
}

.cancel-modal-btn--yes {
    background: linear-gradient(135deg, #DC2626, #B91C1C);
    color: #fff;
    box-shadow: 0 4px 12px rgba(220,38,38,.3);
}
.cancel-modal-btn--yes:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(220,38,38,.4);
}
</style>

<script>
(function() {
    const overlay = document.getElementById('cancelModal');
    const orderNum = document.getElementById('cancelOrderNum');
    const btnNo = document.getElementById('cancelModalNo');
    const btnYes = document.getElementById('cancelModalYes');
    let activeForm = null;

    // Open modal when any cancel button is clicked
    document.querySelectorAll('.btn-cancel-trigger').forEach(function(btn) {
        btn.addEventListener('click', function() {
            activeForm = this.closest('.cancel-order-form');
            orderNum.textContent = this.getAttribute('data-order');
            overlay.classList.add('active');
        });
    });

    // Close modal — keep order
    btnNo.addEventListener('click', function() {
        overlay.classList.remove('active');
        activeForm = null;
    });

    // Confirm cancel — submit form
    btnYes.addEventListener('click', function() {
        if (activeForm) activeForm.submit();
    });

    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('active');
            activeForm = null;
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            overlay.classList.remove('active');
            activeForm = null;
        }
    });
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
