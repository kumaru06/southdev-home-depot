<?php
$pageTitle = 'Update Order Status';
$extraCss = ['admin.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <button class="sidebar-toggle-btn" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i data-lucide="menu"></i>
        </button>
        <h2>Update Order Status</h2>
    </div>

    <div class="page-content">
        <?php if (isset($order)): ?>
            <div class="card" style="max-width:600px;">
                <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem;">
                    <i data-lucide="clipboard-edit" style="width:22px;height:22px;color:var(--accent);"></i>
                    <div>
                        <h3 style="margin:0; font-weight:700;">Order #<?= htmlspecialchars($order['order_number']) ?></h3>
                        <span class="badge badge-<?= $order['status'] ?>" style="margin-top:.25rem;"><?= ucfirst($order['status']) ?></span>
                    </div>
                </div>

                <form action="<?= APP_URL ?>/index.php?url=staff/orders/<?= $order['id'] ?>/status" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">New Status <span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-actions" style="display:flex; gap:.75rem;">
                        <button type="submit" class="btn btn-accent">
                            <i data-lucide="save" style="width:16px;height:16px;"></i> Update Status
                        </button>
                        <a href="<?= APP_URL ?>/index.php?url=staff/orders" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
