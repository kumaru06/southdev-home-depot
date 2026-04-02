<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

// Load reviewed item IDs so we can show/hide the review button
$reviewedItemIds = [];
if ($order['status'] === 'delivered' && isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../models/Review.php';
    $reviewModel = new Review($pdo);
    $reviewedItemIds = $reviewModel->getReviewedOrderItemIds($_SESSION['user_id'], $order['id']);
}

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
                <?php if (!empty($returnRequest) && $returnRequest['status'] !== 'rejected'): ?>
                    <?php
                        $returnStatusLabels = [
                            'pending'   => ['label' => 'Return Pending',  'icon' => 'clock',        'class' => 'return-badge--pending'],
                            'approved'  => ['label' => 'Return Approved', 'icon' => 'check-circle', 'class' => 'return-badge--approved'],
                            'completed' => ['label' => 'Refunded',        'icon' => 'badge-check',  'class' => 'return-badge--refunded'],
                        ];
                        $rs = $returnStatusLabels[$returnRequest['status']] ?? $returnStatusLabels['pending'];
                    ?>
                    <span class="return-badge <?= $rs['class'] ?>">
                        <i data-lucide="<?= $rs['icon'] ?>" style="width:13px;height:13px"></i>
                        <?= $rs['label'] ?>
                    </span>
                <?php endif; ?>
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

    <!-- ===== Return / Refund Status Banner ===== -->
    <?php if (!empty($returnRequest) && $returnRequest['status'] !== 'rejected'): ?>
    <?php
        $rrClass = match($returnRequest['status']) {
            'pending'   => 'refund-banner--pending',
            'approved'  => 'refund-banner--approved',
            'completed' => 'refund-banner--completed',
            default     => 'refund-banner--pending',
        };
        $rrIcon = match($returnRequest['status']) {
            'pending'   => 'clock',
            'approved'  => 'check-circle',
            'completed' => 'badge-check',
            default     => 'clock',
        };
        $rrTitle = match($returnRequest['status']) {
            'pending'   => 'Return Request Pending',
            'approved'  => 'Return Approved — Refund Processing',
            'completed' => 'Successfully Refunded',
            default     => 'Return Request Submitted',
        };
        $rrDesc = match($returnRequest['status']) {
            'pending'   => 'Your return request is under review. We\'ll notify you once a decision is made.',
            'approved'  => 'Your return has been approved. The refund is being processed and will reflect in your account shortly.',
            'completed' => 'Your refund has been completed. The amount has been returned to your original payment method.',
            default     => 'Your return request has been submitted.',
        };
    ?>
    <div class="refund-banner <?= $rrClass ?>">
        <div class="refund-banner-icon">
            <i data-lucide="<?= $rrIcon ?>"></i>
        </div>
        <div class="refund-banner-content">
            <h4><?= $rrTitle ?></h4>
            <p><?= $rrDesc ?></p>
            <div class="refund-banner-meta">
                <span><i data-lucide="calendar" style="width:13px;height:13px"></i> Requested <?= date('M d, Y', strtotime($returnRequest['created_at'])) ?></span>
                <span><i data-lucide="tag" style="width:13px;height:13px"></i> <?= htmlspecialchars($returnRequest['reason']) ?></span>
            </div>
            <?php if (!empty($returnRequest['admin_notes'])): ?>
            <div class="refund-banner-notes">
                <i data-lucide="message-circle" style="width:13px;height:13px"></i>
                <span><strong>Staff note:</strong> <?= htmlspecialchars($returnRequest['admin_notes']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="refund-banner-badge">
            <span class="return-badge <?= $rs['class'] ?? 'return-badge--pending' ?>">
                <?= ucfirst($returnRequest['status']) ?>
            </span>
        </div>
    </div>
    <?php endif; ?>

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
                    <span class="od-detail-value">
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
                                    $pmLogo  = APP_URL . '/assets/uploads/images/logo/COD2.png';
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
                            <?= htmlspecialchars($pmLabel) ?>
                        </span>
                    </span>
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
                <?php if (!empty($returnRequest) && $returnRequest['status'] !== 'rejected'): ?>
                    <div class="od-return-status-note">
                        <i data-lucide="rotate-ccw" style="width:15px;height:15px;color:var(--accent);"></i>
                        <span>Return request is <strong><?= $returnRequest['status'] === 'completed' ? 'refunded' : $returnRequest['status'] ?></strong></span>
                    </div>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/index.php?url=returns/request/<?= $order['id'] ?>" class="btn btn-outline btn-block">
                        <i data-lucide="rotate-ccw"></i> Request Return
                    </a>
                <?php endif; ?>
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
                <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
                    <span class="od-item-subtotal">₱<?= number_format($item['subtotal'], 2) ?></span>
                    <?php if ($order['status'] === 'delivered'): ?>
                        <?php if (in_array($item['id'], $reviewedItemIds)): ?>
                            <span class="badge badge-success" style="font-size:11px;"><i data-lucide="check" style="width:12px;height:12px"></i> Reviewed</span>
                        <?php else: ?>
                            <button class="btn btn-outline btn-sm js-open-review" data-product-id="<?= $item['product_id'] ?>" data-order-id="<?= $order['id'] ?>" data-order-item-id="<?= $item['id'] ?>">Write a Review</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
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

<!-- Review Modal (simple inline modal) -->
<?php if ($order['status'] === 'delivered'): ?>
<div id="reviewModal" class="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);align-items:center;justify-content:center;z-index:1200">
    <div class="modal" style="width:640px;max-width:96%;background:#fff;padding:18px;border-radius:8px;box-shadow:0 12px 40px rgba(0,0,0,.28);">
        <h3 style="margin-bottom:8px">Write a Review</h3>
        <form id="reviewForm" action="<?= APP_URL ?>/index.php?url=reviews/submit" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" id="rv_product_id" value="">
            <input type="hidden" name="order_id" id="rv_order_id" value="">
            <input type="hidden" name="order_item_id" id="rv_order_item_id" value="">

            <div class="form-group">
                <label>Rating</label>
                <input type="hidden" name="rating" id="rating" value="" required>
                <div class="star-rating-input" id="starRatingInput">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" class="star-btn" data-value="<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </button>
                    <?php endfor; ?>
                    <span class="star-rating-label" id="starRatingLabel"></span>
                </div>
            </div>
            <div class="form-group">
                <label for="comment">Comment (optional)</label>
                <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Share your experience..."></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
                <button type="button" class="btn btn-outline js-close-review">Cancel</button>
                <button type="submit" class="btn btn-accent">Submit Review</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var ratingLabels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    var stars = document.querySelectorAll('#starRatingInput .star-btn');
    var ratingInput = document.getElementById('rating');
    var ratingLabel = document.getElementById('starRatingLabel');
    var currentRating = 0;

    function setStars(value, permanent) {
        stars.forEach(function(s, i) {
            if (i < value) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
        if (permanent) {
            currentRating = value;
            ratingInput.value = value;
            ratingLabel.textContent = ratingLabels[value] || '';
        }
    }

    stars.forEach(function(btn) {
        btn.addEventListener('click', function() {
            setStars(parseInt(this.dataset.value), true);
        });
        btn.addEventListener('mouseenter', function() {
            setStars(parseInt(this.dataset.value), false);
        });
    });

    document.getElementById('starRatingInput').addEventListener('mouseleave', function() {
        setStars(currentRating, false);
        if (currentRating > 0) ratingLabel.textContent = ratingLabels[currentRating];
    });

    // Form validation — require rating
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        if (!ratingInput.value) {
            e.preventDefault();
            ratingLabel.textContent = 'Please select a rating';
            ratingLabel.style.color = '#EF4444';
            setTimeout(function(){ ratingLabel.style.color = ''; }, 2000);
        }
    });

    function resetStars() {
        currentRating = 0;
        ratingInput.value = '';
        ratingLabel.textContent = '';
        stars.forEach(function(s){ s.classList.remove('active'); });
    }

    function openModal(productId, orderId, orderItemId){
        document.getElementById('rv_product_id').value = productId;
        document.getElementById('rv_order_id').value = orderId;
        document.getElementById('rv_order_item_id').value = orderItemId;
        resetStars();
        document.getElementById('comment').value = '';
        document.getElementById('reviewModal').style.display = 'flex';
    }
    function closeModal(){ document.getElementById('reviewModal').style.display = 'none'; }

    document.querySelectorAll('.js-open-review').forEach(function(btn){
        btn.addEventListener('click', function(){
            openModal(this.dataset.productId, this.dataset.orderId, this.dataset.orderItemId);
        });
    });
    document.querySelectorAll('.js-close-review').forEach(function(btn){ btn.addEventListener('click', closeModal); });
    document.getElementById('reviewModal').addEventListener('click', function(e){ if(e.target === this) closeModal(); });
});
</script>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
