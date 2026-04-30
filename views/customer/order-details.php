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

$returnedItemIds = [];
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
        <span>/</span>
        <span><?= htmlspecialchars($order['order_number']) ?></span>
    </nav>

    <!-- Hero card with order header -->
    <div class="od-hero" style="--status-clr: <?= $statusColor ?>">
        <div class="od-hero-top">
            <div class="od-hero-info">
                <span class="od-order-number"><?= htmlspecialchars($order['order_number']) ?></span>
                <div class="od-hero-meta">
                    <span><?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?></span>
                    <span><?= count($orderItems) ?> item<?= count($orderItems) !== 1 ? 's' : '' ?></span>
                </div>
            </div>
            <div class="od-hero-status">
                <span class="badge badge-<?= htmlspecialchars($order['status']) ?> badge-lg"><?= htmlspecialchars(ucfirst($order['status'])) ?></span>
                <?php if (!empty($returnRequest) && $returnRequest['status'] !== 'rejected' && $order['status'] === 'delivered'): ?>
                    <?php
                        $returnStatusLabels = [
                            'pending'   => ['label' => 'Return Pending',  'icon' => 'clock',        'class' => 'return-badge--pending'],
                            'approved'  => ['label' => 'Return Approved', 'icon' => 'check-circle', 'class' => 'return-badge--approved'],
                            'completed' => ['label' => 'Refunded',        'icon' => 'badge-check',  'class' => 'return-badge--refunded'],
                        ];
                        $rs = $returnStatusLabels[$returnRequest['status']] ?? $returnStatusLabels['pending'];
                    ?>
                    <span class="return-badge <?= $rs['class'] ?>">
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
                <div class="od-step-dot"></div>
                <span class="od-step-label"><?= ucfirst($step) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="od-cancelled-banner">
            <span>This order has been cancelled</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== Return / Refund Status Banner ===== -->
    <?php if (!empty($returnRequest) && $returnRequest['status'] !== 'rejected' && $order['status'] === 'delivered'): ?>
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
        <?php
            $returnedItemIds = [];
            if (!empty($returnRequest['selected_items'])) {
                $decoded = json_decode($returnRequest['selected_items'], true);
                if (is_array($decoded)) $returnedItemIds = array_map('intval', $decoded);
            }
            $returnedItems = array_filter($orderItems, fn($it) => in_array((int)$it['id'], $returnedItemIds));
        ?>
        <div class="refund-banner-icon"></div>
        <div class="refund-banner-content">
            <h4><?= $rrTitle ?></h4>
            <p><?= $rrDesc ?></p>
            <div class="refund-banner-meta">
                <span>Requested <?= date('M d, Y', strtotime($returnRequest['created_at'])) ?></span>
                <span><?= htmlspecialchars($returnRequest['reason']) ?></span>
            </div>
            <?php if (!empty($returnedItems)): ?>
            <div class="refund-returned-items">
                <span class="refund-returned-label">Returned item<?= count($returnedItems) !== 1 ? 's' : '' ?>:</span>
                <div class="refund-returned-list">
                    <?php foreach ($returnedItems as $ri): ?>
                    <div class="refund-returned-chip">
                        <?php if (!empty($ri['image'])): ?>
                        <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($ri['image']) ?>" alt="">
                        <?php else: ?>
                        <div class="refund-returned-chip-placeholder"></div>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($ri['product_name']) ?></span>
                        <span class="refund-returned-chip-qty">×<?= $ri['quantity'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($returnRequest['admin_notes'])): ?>
            <div class="refund-banner-notes">
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
                <div class="od-card-icon"></div>
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
                    <h4>Cancel this order?</h4>
                    <p class="od-cancel-note">This order hasn't been processed yet and can be cancelled immediately.</p>
                    <form action="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>/cancel" method="POST" class="cancel-order-form" id="detailCancelForm">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-danger btn-cancel-order" id="detailCancelBtn">Cancel Order</button>
                    </form>
                </div>
            </div>
            <?php elseif ($order['status'] === 'processing'): ?>
            <div class="od-card-action">
                <?php if (!empty($cancelRequest)): ?>
                    <div class="od-return-status-note">
                        <span>Cancellation request is <strong><?= $cancelRequest['status'] ?></strong></span>
                    </div>
                <?php else: ?>
                <div class="od-cancel-section">
                    <h4>Need to cancel?</h4>
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
                        <button type="submit" class="btn btn-warning">Request Cancellation</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php elseif ($order['status'] === 'delivered'): ?>
            <div class="od-card-action">
                <?php if (!empty($returnRequest) && $returnRequest['status'] !== 'rejected'): ?>
                    <div class="od-return-status-note">
                        <span>Return request is <strong><?= $returnRequest['status'] === 'completed' ? 'refunded' : $returnRequest['status'] ?></strong></span>
                    </div>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/index.php?url=returns/request/<?= $order['id'] ?>" class="btn btn-outline btn-block">
                        Request Return
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Cancel Request Status & Staff Reply -->
        <?php if (!empty($cancelRequest)): ?>
        <div class="od-card od-card--cancel-status">
            <div class="od-card-header">

                <div>
                    <h3>Cancellation Request</h3>
                    <p>Status: <strong><?= ucfirst($cancelRequest['status']) ?></strong></p>
                </div>
            </div>
            <div class="od-card-body" style="display:flex;flex-direction:column;gap:.75rem;">
                <div>
                    <span style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-secondary);">Your Reason</span>
                    <p style="margin:.25rem 0 0;font-size:.9rem;color:var(--charcoal);"><?= htmlspecialchars($cancelRequest['reason']) ?></p>
                </div>
                <?php if (!empty($cancelRequest['admin_notes'])): ?>
                <div class="od-staff-reply">
                    <span style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-secondary);">
                        Staff Reply
                    </span>
                    <div class="od-staff-reply-bubble">
                        <?= htmlspecialchars($cancelRequest['admin_notes']) ?>
                    </div>
                </div>
                <?php endif; ?>
                <div style="font-size:.78rem;color:var(--text-secondary);">
                    Requested on <?= date('M d, Y \a\t g:i A', strtotime($cancelRequest['created_at'])) ?>
                    <?php if ($cancelRequest['status'] !== 'pending' && !empty($cancelRequest['updated_at'])): ?>
                        &middot; Responded on <?= date('M d, Y \a\t g:i A', strtotime($cancelRequest['updated_at'])) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Shipping Address -->
        <div class="od-card od-card--shipping">
            <div class="od-card-header">
                <div class="od-card-icon od-card-icon--purple"></div>
                <div>
                    <h3>Shipping Address</h3>
                    <p>Delivery destination</p>
                </div>
            </div>
            <div class="od-card-body">
                <div class="od-address-block">
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
            <div class="od-card-icon od-card-icon--green"></div>
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
                    <div class="od-item-thumb-placeholder"><span style="font-size:10px;color:var(--text-muted);">N/A</span></div>
                    <?php endif; ?>
                    <div class="od-item-info">
                        <span class="od-item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                        <span class="od-item-unit">₱<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></span>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
                    <span class="od-item-subtotal">₱<?= number_format($item['subtotal'], 2) ?></span>
                    <?php if (!empty($returnedItemIds) && in_array((int)$item['id'], $returnedItemIds)): ?>
                        <span class="badge od-item-returned-badge">Returned</span>
                    <?php endif; ?>
                    <?php if ($order['status'] === 'delivered'): ?>
                        <?php if (in_array($item['id'], $reviewedItemIds)): ?>
                            <span class="badge badge-success" style="font-size:11px;">Reviewed</span>
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
        <a href="<?= APP_URL ?>/index.php?url=orders" class="btn btn-outline">&larr; Back to Orders</a>
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

<?php if ($order['status'] === 'pending'): ?>
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
        <p class="cancel-modal-text">Are you sure you want to cancel order <strong><?= htmlspecialchars($order['order_number']) ?></strong>? This action cannot be undone.</p>
        <div class="cancel-modal-actions">
            <button type="button" class="cancel-modal-btn cancel-modal-btn--no" id="cancelModalNo">Keep Order</button>
            <button type="button" class="cancel-modal-btn cancel-modal-btn--yes" id="cancelModalYes">Yes, Cancel</button>
        </div>
    </div>
</div>

<style>
.cancel-modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 10000;
    background: rgba(0,0,0,.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
    align-items: center; justify-content: center; animation: cmFadeIn .2s ease;
}
.cancel-modal-overlay.active { display: flex; }
@keyframes cmFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes cmSlideUp { from { opacity: 0; transform: translateY(24px) scale(.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
.cancel-modal {
    background: #fff; border-radius: 16px; padding: 36px 32px 28px; max-width: 400px; width: 90%;
    text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,.2); animation: cmSlideUp .25s ease forwards;
}
.cancel-modal-icon {
    width: 72px; height: 72px; margin: 0 auto 16px; border-radius: 50%;
    background: #FEE2E2; display: flex; align-items: center; justify-content: center;
}
.cancel-modal-title { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.25rem; font-weight: 700; color: #1a1a2e; margin: 0 0 8px; }
.cancel-modal-text { font-size: .9rem; color: #64748b; line-height: 1.5; margin: 0 0 24px; }
.cancel-modal-text strong { color: #1a1a2e; font-weight: 600; }
.cancel-modal-actions { display: flex; gap: 12px; }
.cancel-modal-btn {
    flex: 1; padding: 12px 20px; border: none; border-radius: 10px; font-size: .9rem;
    font-weight: 600; cursor: pointer; transition: all .2s ease; font-family: 'Plus Jakarta Sans', sans-serif;
}
.cancel-modal-btn--no { background: #f1f5f9; color: #475569; }
.cancel-modal-btn--no:hover { background: #e2e8f0; }
.cancel-modal-btn--yes { background: linear-gradient(135deg, #DC2626, #B91C1C); color: #fff; box-shadow: 0 4px 12px rgba(220,38,38,.3); }
.cancel-modal-btn--yes:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(220,38,38,.4); }
</style>

<script>
(function() {
    var overlay = document.getElementById('cancelModal');
    var btnNo = document.getElementById('cancelModalNo');
    var btnYes = document.getElementById('cancelModalYes');
    var form = document.getElementById('detailCancelForm');

    document.getElementById('detailCancelBtn').addEventListener('click', function() {
        overlay.classList.add('active');
    });
    btnNo.addEventListener('click', function() { overlay.classList.remove('active'); });
    btnYes.addEventListener('click', function() { if (form) form.submit(); });
    overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.classList.remove('active'); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') overlay.classList.remove('active'); });
})();
</script>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
