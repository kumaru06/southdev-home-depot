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

                <?php
                    $statusFlow = [
                        'pending'    => 'processing',
                        'processing' => 'shipped',
                        'shipped'    => 'delivered',
                    ];
                    $currentStatus = strtolower((string) ($order['status'] ?? ''));
                    $nextStatus = $statusFlow[$currentStatus] ?? null;
                ?>
                <?php if ($nextStatus): ?>
                <form action="<?= APP_URL ?>/index.php?url=staff/orders/<?= $order['id'] ?>/status"
                      method="POST"
                      class="js-order-status-form"
                      data-current-status="<?= htmlspecialchars($currentStatus) ?>"
                      data-next-status="<?= htmlspecialchars($nextStatus) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">New Status <span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="<?= htmlspecialchars($nextStatus) ?>"><?= ucfirst($nextStatus) ?></option>
                        </select>
                    </div>
                    <div class="form-actions" style="display:flex; gap:.75rem;">
                        <button type="submit" class="btn btn-accent">
                            <i data-lucide="save" style="width:16px;height:16px;"></i> Update Status
                        </button>
                        <a href="<?= APP_URL ?>/index.php?url=staff/orders" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-info">This order is final and its status can no longer be changed.</div>
                    <a href="<?= APP_URL ?>/index.php?url=staff/orders" class="btn btn-outline">Back to Orders</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.js-order-status-form');
    if (!form) return;
    form.addEventListener('submit', function (event) {
        if (form.dataset.confirmed === '1') return;
        event.preventDefault();
        var current = form.dataset.currentStatus || '';
        var next = form.dataset.nextStatus || '';
        var label = function (value) {
            return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
        };
        var proceed = function () {
            form.dataset.confirmed = '1';
            form.submit();
        };
        var message = 'Change order status from ' + label(current) + ' to ' + label(next)
            + '? This cannot be undone and the order cannot return to ' + label(current) + '.';
        if (typeof window.confirmDialog === 'function') {
            window.confirmDialog({
                title: 'Confirm status update',
                message: message,
                confirmText: 'Update to ' + label(next),
                cancelText: 'Keep ' + label(current),
                confirmVariant: 'accent'
            }).then(function (confirmed) {
                if (confirmed) proceed();
            });
        } else if (window.confirm(message)) {
            proceed();
        }
    });
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
