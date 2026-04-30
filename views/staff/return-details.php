<?php
/* $return, $order, $orderItems, $selectedItemIds, $returnBaseUrl set by ReturnController::details() */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

$rstatus = $return['status'] ?: 'pending';
$badgeClass = match($rstatus) {
    'pending'   => 'badge-pending',
    'approved'  => 'badge-processing',
    'rejected'  => 'badge-cancelled',
    'completed' => 'badge-delivered',
    default     => 'badge-pending',
};
$selectedLookup = array_flip(array_map('intval', $selectedItemIds ?? []));
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>Return Request Details</h2>
        </div>
        <a href="<?= APP_URL ?>/index.php?url=<?= htmlspecialchars($returnBaseUrl) ?>" class="btn btn-outline btn-sm">Back to Returns</a>
    </div>

    <div class="page-content">
        <div class="return-detail-hero card">
            <div>
                <span class="return-detail-kicker">Return #<?= (int) $return['id'] ?></span>
                <h3><?= htmlspecialchars($return['order_number']) ?></h3>
                <p><?= htmlspecialchars($return['first_name'] . ' ' . $return['last_name']) ?> • <?= date('M d, Y h:i A', strtotime($return['created_at'])) ?></p>
            </div>
            <span class="badge <?= $badgeClass ?>"><?= ucfirst($rstatus) ?></span>
        </div>

        <div class="return-detail-grid">
            <div class="card return-detail-card">
                <h3>Return Information</h3>
                <div class="return-detail-list">
                    <div><span>Order</span><strong><?= htmlspecialchars($return['order_number']) ?></strong></div>
                    <div><span>Customer</span><strong><?= htmlspecialchars($return['first_name'] . ' ' . $return['last_name']) ?></strong></div>
                    <div><span>Order Total</span><strong>₱<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></strong></div>
                    <div><span>Status</span><strong><?= ucfirst($rstatus) ?></strong></div>
                </div>

                <div class="return-reason-box">
                    <span>Reason</span>
                    <p><?= nl2br(htmlspecialchars($return['reason'])) ?></p>
                </div>

                <?php if (!empty($return['admin_notes'])): ?>
                    <div class="return-reason-box">
                        <span>Admin Notes</span>
                        <p><?= nl2br(htmlspecialchars($return['admin_notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card return-detail-card">
                <h3>Selected for Return</h3>
                <p class="return-detail-muted">Products marked as selected are the items the customer included in this return request.</p>
                <div class="return-selected-count">
                    <?= count($selectedItemIds ?? []) ?> selected item<?= count($selectedItemIds ?? []) === 1 ? '' : 's' ?>
                </div>

                <div class="return-action-panel">
                    <h3>Review Action</h3>
                    <?php if ($rstatus === 'pending'): ?>
                        <form action="<?= APP_URL ?>/index.php?url=<?= htmlspecialchars($returnBaseUrl) ?>/<?= (int) $return['id'] ?>/update" method="POST">
                            <?= csrf_field() ?>
                            <label for="admin_notes">Admin Notes</label>
                            <textarea id="admin_notes" name="admin_notes" class="form-control" rows="4" placeholder="Add notes for this return request..."><?= htmlspecialchars($return['admin_notes'] ?? '') ?></textarea>
                            <div class="return-action-buttons">
                                <button type="submit" name="status" value="approved" class="btn btn-accent">Approve Return</button>
                                <button type="submit" name="status" value="rejected" class="btn btn-outline">Reject Return</button>
                            </div>
                        </form>
                    <?php elseif ($rstatus === 'approved'): ?>
                        <form action="<?= APP_URL ?>/index.php?url=<?= htmlspecialchars($returnBaseUrl) ?>/<?= (int) $return['id'] ?>/update" method="POST">
                            <?= csrf_field() ?>
                            <label for="admin_notes">Admin Notes</label>
                            <textarea id="admin_notes" name="admin_notes" class="form-control" rows="4" placeholder="Add refund notes if needed..."><?= htmlspecialchars($return['admin_notes'] ?? '') ?></textarea>
                            <div class="return-action-buttons">
                                <button type="submit" name="status" value="completed" class="btn btn-accent">Mark as Refunded</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="return-detail-muted">This request is already <?= htmlspecialchars($rstatus) ?>.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card return-products-card">
            <div class="return-products-header">
                <div>
                    <h3>Order Products</h3>
                    <p>All products from this order, including pictures, names, quantities, and prices.</p>
                </div>
            </div>

            <div class="return-products-grid">
                <?php foreach ($orderItems as $item): ?>
                    <?php $isSelected = empty($selectedLookup) || isset($selectedLookup[(int) $item['id']]); ?>
                    <div class="return-product-card <?= $isSelected ? 'is-selected' : '' ?>">
                        <div class="return-product-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                            <?php else: ?>
                                <div class="return-product-placeholder">No Image</div>
                            <?php endif; ?>
                            <?php if ($isSelected): ?>
                                <span class="return-product-selected">Selected</span>
                            <?php endif; ?>
                        </div>
                        <div class="return-product-body">
                            <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                            <div class="return-product-meta">
                                <span>Qty: <?= (int) $item['quantity'] ?></span>
                                <span>₱<?= number_format((float) $item['price'], 2) ?> each</span>
                            </div>
                            <strong>Subtotal: ₱<?= number_format((float) $item['subtotal'], 2) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
