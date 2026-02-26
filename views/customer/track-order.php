<?php
$pageTitle = 'Track Order';
$extraCss = ['customer.css'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="page-heading">
        <h1>Track Your Order</h1>
    </div>

    <!-- Search Form -->
    <div class="card" style="margin-bottom:2rem;">
        <form action="<?= APP_URL ?>/index.php" method="GET" class="search-bar" style="display:flex; gap:.75rem; flex-wrap:wrap;">
            <input type="hidden" name="url" value="orders/track">
            <div class="input-icon-wrap" style="flex:1; min-width:250px;">
                <i data-lucide="search" class="input-icon"></i>
                <input type="text" name="order_number" class="form-control"
                       placeholder="Enter your order number (e.g. SHD-20260214-A1B2C3)"
                       value="<?= htmlspecialchars($orderNumber ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-accent">
                <i data-lucide="search" style="width:16px;height:16px;"></i> Track
            </button>
        </form>
    </div>

    <?php if (isset($order) && $order): ?>
        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
                <div>
                    <h3 style="margin:0; font-weight:700;">Order #<?= htmlspecialchars($order['order_number']) ?></h3>
                    <p style="color:var(--steel); margin-top:.25rem; font-size:.9rem;">
                        Placed on <?= date('F d, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                    </p>
                </div>
                <span class="badge badge-lg badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Customer</span>
                <span class="detail-value"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value"><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Updated</span>
                <span class="detail-value"><?= date('M d, Y g:i A', strtotime($order['updated_at'])) ?></span>
            </div>

            <!-- Order Timeline -->
            <?php if ($order['status'] !== 'cancelled'): ?>
                <div class="order-timeline" style="margin-top:2rem;">
                    <h4 style="margin-bottom:1rem; font-weight:600;">
                        <i data-lucide="git-branch" style="width:16px;height:16px;vertical-align:middle;color:var(--accent);"></i>
                        Order Progress
                    </h4>
                    <?php
                    $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                    $currentIndex = array_search($order['status'], $statuses);
                    ?>
                    <div class="timeline-steps">
                        <?php foreach ($statuses as $i => $status): ?>
                            <div class="timeline-step <?= $i <= $currentIndex ? 'completed' : '' ?> <?= $i === $currentIndex ? 'current' : '' ?>">
                                <div class="step-dot">
                                    <?php if ($i <= $currentIndex): ?>
                                        <i data-lucide="check" style="width:14px;height:14px;"></i>
                                    <?php else: ?>
                                        <span><?= $i + 1 ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="step-label"><?= ucfirst($status) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align:center; margin-top:2rem; padding:1.5rem; background:var(--neutral); border-radius:8px;">
                    <i data-lucide="x-circle" style="width:32px;height:32px;color:var(--accent);margin-bottom:.5rem;"></i>
                    <p style="color:var(--accent); font-weight:600;">This order has been cancelled.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif (isset($orderNumber) && $orderNumber): ?>
        <div class="card" style="text-align:center; padding:3rem;">
            <i data-lucide="search-x" style="width:48px;height:48px;color:var(--steel);margin-bottom:1rem;"></i>
            <h3 style="color:var(--charcoal); margin-bottom:.5rem;">Order Not Found</h3>
            <p style="color:var(--steel);">No order matching "<strong><?= htmlspecialchars($orderNumber) ?></strong>" was found. Please check the order number and try again.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
