<?php
/* $pageTitle, $extraCss, $isAdmin set by ReturnController::manage() */
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
                <input type="hidden" name="url" value="staff/returns">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending','approved','rejected','completed'] as $s): ?>
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
                        <th>ID</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($returns)): ?>
                        <?php foreach ($returns as $return): ?>
                            <tr>
                                <td>#<?= $return['id'] ?></td>
                                <td><strong><?= htmlspecialchars($return['order_number']) ?></strong></td>
                                <td><?= htmlspecialchars($return['first_name'] . ' ' . $return['last_name']) ?></td>
                                <td title="<?= htmlspecialchars($return['reason']) ?>"><?= htmlspecialchars(substr($return['reason'], 0, 50)) ?><?= strlen($return['reason']) > 50 ? '…' : '' ?></td>
                                <td>
                                    <?php
                                        $rstatus = $return['status'] ?: 'pending';
                                        $badgeClass = match($rstatus) {
                                            'pending'   => 'badge-pending',
                                            'approved'  => 'badge-processing',
                                            'rejected'  => 'badge-cancelled',
                                            'completed' => 'badge-delivered',
                                            default     => 'badge-pending',
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($rstatus) ?></span>
                                </td>
                                <td><?= date('M d, Y', strtotime($return['created_at'])) ?></td>
                                <td>
                                    <?php if ($rstatus === 'pending'): ?>
                                        <form action="<?= APP_URL ?>/index.php?url=staff/returns/<?= $return['id'] ?>/update" method="POST" class="inline-form">
                                            <?= csrf_field() ?>
                                            <input type="text" name="admin_notes" placeholder="Notes…" class="form-control form-control-sm" style="width: 120px;">
                                            <div class="action-btn-group">
                                                <button type="submit" name="status" value="approved" class="action-btn approve js-return-approve" title="Approve"><i data-lucide="check"></i></button>
                                                <button type="submit" name="status" value="rejected" class="action-btn reject js-return-reject" title="Reject"><i data-lucide="x"></i></button>
                                            </div>
                                        </form>
                                    <?php elseif ($rstatus === 'approved'): ?>
                                        <form action="<?= APP_URL ?>/index.php?url=staff/returns/<?= $return['id'] ?>/update" method="POST" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button type="submit" name="status" value="completed" class="btn btn-sm btn-accent js-return-refund" title="Mark as Refunded"><i data-lucide="badge-check" style="width:14px;height:14px"></i> Mark Refunded</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted"><?= ucfirst($rstatus) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No return requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Helper: submit form with a named button value (since form.submit() skips button values)
    function submitFormWithStatus(form, statusValue) {
        var hidden = form.querySelector('input[type="hidden"][name="status"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'status';
            form.appendChild(hidden);
        }
        hidden.value = statusValue;
        form.submit();
    }

    // Reject return request – styled confirm dialog
    document.querySelectorAll('.js-return-reject').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var form = btn.closest('form');
            if (typeof window.confirmDialog === 'function') {
                window.confirmDialog({
                    title: 'Reject return request?',
                    message: 'Are you sure you want to reject this return request? The customer will be notified.',
                    confirmText: 'Reject',
                    confirmVariant: 'danger'
                }).then(function (ok) {
                    if (ok) submitFormWithStatus(form, 'rejected');
                });
            } else {
                if (confirm('Reject this return request?')) submitFormWithStatus(form, 'rejected');
            }
        });
    });

    // Approve return request – styled confirm dialog
    document.querySelectorAll('.js-return-approve').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var form = btn.closest('form');
            if (typeof window.confirmDialog === 'function') {
                window.confirmDialog({
                    title: 'Approve return request?',
                    message: 'This will approve the return and process stock adjustments. Continue?',
                    confirmText: 'Approve',
                    confirmVariant: 'accent'
                }).then(function (ok) {
                    if (ok) submitFormWithStatus(form, 'approved');
                });
            } else {
                if (confirm('Approve this return request?')) submitFormWithStatus(form, 'approved');
            }
        });
    });

    // Mark as Refunded – styled confirm dialog
    document.querySelectorAll('.js-return-refund').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var form = btn.closest('form');
            if (typeof window.confirmDialog === 'function') {
                window.confirmDialog({
                    title: 'Mark as refunded?',
                    message: 'This will mark the payment as refunded. Make sure the refund has been processed.',
                    confirmText: 'Mark Refunded',
                    confirmVariant: 'accent'
                }).then(function (ok) {
                    if (ok) submitFormWithStatus(form, 'completed');
                });
            } else {
                if (confirm('Mark this return as refunded?')) submitFormWithStatus(form, 'completed');
            }
        });
    });
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
