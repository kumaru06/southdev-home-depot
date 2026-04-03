<?php
/* $pageTitle, $extraCss, $isAdmin set by OrderController::cancelRequests() */
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
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Staff Reply</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cancelRequests)): ?>
                        <?php foreach ($cancelRequests as $req): ?>
                            <tr>
                                <td>#<?= $req['id'] ?></td>
                                <td><strong><?= htmlspecialchars($req['order_number'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? '')) ?></td>
                                <td title="<?= htmlspecialchars($req['reason']) ?>"><?= htmlspecialchars(substr($req['reason'], 0, 60)) ?><?= strlen($req['reason']) > 60 ? '…' : '' ?></td>
                                <td><span class="badge badge-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                <td>
                                    <?php if (!empty($req['admin_notes']) && $req['status'] !== 'pending'): ?>
                                        <span class="cr-reply-text" title="<?= htmlspecialchars($req['admin_notes']) ?>"><?= htmlspecialchars(substr($req['admin_notes'], 0, 50)) ?><?= strlen($req['admin_notes']) > 50 ? '…' : '' ?></span>
                                    <?php elseif ($req['status'] === 'pending'): ?>
                                        <span class="text-muted" style="font-size:.82rem;">—</span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:.82rem;">No reply</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <div class="action-btn-group">
                                            <button type="button" class="action-btn approve cr-reply-btn" title="Approve with reply"
                                                data-id="<?= $req['id'] ?>"
                                                data-name="<?= htmlspecialchars(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-order="<?= htmlspecialchars($req['order_number'] ?? '', ENT_QUOTES) ?>"
                                                data-reason="<?= htmlspecialchars($req['reason'], ENT_QUOTES) ?>"
                                                data-action="approve">
                                                <i data-lucide="check"></i>
                                            </button>
                                            <button type="button" class="action-btn delete cr-reply-btn" title="Reject with reply"
                                                data-id="<?= $req['id'] ?>"
                                                data-name="<?= htmlspecialchars(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-order="<?= htmlspecialchars($req['order_number'] ?? '', ENT_QUOTES) ?>"
                                                data-reason="<?= htmlspecialchars($req['reason'], ENT_QUOTES) ?>"
                                                data-action="reject">
                                                <i data-lucide="x"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted"><?= ucfirst($req['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No cancel requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== Reply Modal ===== -->
<div id="crReplyModal" class="modal-overlay">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <h3 id="crReplyTitle">Reply to Cancel Request</h3>
            <button type="button" class="modal-close" onclick="closeCrModal()">&times;</button>
        </div>
        <div class="cr-modal-info">
            <div class="cr-info-row">
                <span class="cr-info-label">Customer</span>
                <span class="cr-info-value" id="crCustomer"></span>
            </div>
            <div class="cr-info-row">
                <span class="cr-info-label">Order</span>
                <span class="cr-info-value" id="crOrder"></span>
            </div>
            <div class="cr-info-row">
                <span class="cr-info-label">Reason</span>
                <span class="cr-info-value" id="crReason" style="font-style:italic;color:var(--text-secondary);"></span>
            </div>
        </div>
        <form id="crReplyForm" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Your Reply to Customer <span style="color:var(--text-secondary);font-weight:400;">(visible to customer)</span></label>
                <textarea name="admin_notes" id="crReplyText" class="form-control" rows="3" placeholder="e.g. We've approved your cancellation. Refund will be processed within 3-5 days." required></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeCrModal()">Cancel</button>
                <button type="submit" class="btn" id="crSubmitBtn">
                    <i data-lucide="send" style="width:15px;height:15px;"></i> <span id="crSubmitText">Approve & Reply</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(4px);
}
.modal-box {
    background: var(--white); border-radius: var(--radius-lg);
    padding: 1.75rem; width: 90%; box-shadow: var(--shadow-lg);
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.25rem;
}
.modal-header h3 { margin: 0; font-size: 1.1rem; }
.modal-close {
    background: none; border: none; font-size: 1.5rem;
    cursor: pointer; color: var(--text-secondary);
    line-height: 1;
}
.modal-close:hover { color: var(--danger); }

/* Cancel Request Modal Info */
.cr-modal-info {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: .6rem;
}
.cr-info-row {
    display: flex;
    gap: .75rem;
    align-items: baseline;
}
.cr-info-label {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--text-secondary);
    min-width: 72px;
    flex-shrink: 0;
}
.cr-info-value {
    font-size: .88rem;
    font-weight: 600;
    color: var(--charcoal);
}
.cr-reply-text {
    font-size: .82rem;
    color: var(--charcoal);
    background: #f0fdf4;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid #bbf7d0;
    display: inline-block;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

<script>
(function() {
    'use strict';
    var modal    = document.getElementById('crReplyModal');
    var form     = document.getElementById('crReplyForm');
    var title    = document.getElementById('crReplyTitle');
    var customer = document.getElementById('crCustomer');
    var order    = document.getElementById('crOrder');
    var reason   = document.getElementById('crReason');
    var replyTxt = document.getElementById('crReplyText');
    var submitBtn= document.getElementById('crSubmitBtn');
    var submitTxt= document.getElementById('crSubmitText');

    window.closeCrModal = function() {
        modal.classList.remove('active');
    };

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.cr-reply-btn');
        if (btn) {
            var id     = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');
            var name   = btn.getAttribute('data-name');
            var ord    = btn.getAttribute('data-order');
            var rsn    = btn.getAttribute('data-reason');

            customer.textContent = name;
            order.textContent    = ord;
            reason.textContent   = rsn;
            replyTxt.value       = '';

            if (action === 'approve') {
                title.textContent    = 'Approve & Reply';
                submitTxt.textContent = 'Approve & Send Reply';
                submitBtn.className  = 'btn btn-accent';
                replyTxt.placeholder = 'e.g. Your cancellation has been approved. Refund will be processed shortly.';
                form.action = '<?= APP_URL ?>/index.php?url=staff/cancel-requests/' + id + '/approve';
            } else {
                title.textContent    = 'Reject & Reply';
                submitTxt.textContent = 'Reject & Send Reply';
                submitBtn.className  = 'btn';
                submitBtn.style.background = 'var(--danger)';
                submitBtn.style.borderColor = 'var(--danger)';
                submitBtn.style.color = '#fff';
                replyTxt.placeholder = 'e.g. We cannot cancel this order as it has already been shipped.';
                form.action = '<?= APP_URL ?>/index.php?url=staff/cancel-requests/' + id + '/reject';
            }

            modal.classList.add('active');
            setTimeout(function(){ replyTxt.focus(); }, 100);
            return;
        }

        if (e.target === modal) {
            closeCrModal();
        }
    });
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
