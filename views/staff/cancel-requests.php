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

    <div class="page-content page-content--table-locked">
        <div class="data-table-wrap data-table-wrap--locked">
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
                                        <button type="button" class="btn-view-reply"
                                            data-reply="<?= htmlspecialchars($req['admin_notes'], ENT_QUOTES) ?>"
                                            data-order="<?= htmlspecialchars($req['order_number'] ?? '', ENT_QUOTES) ?>"
                                            data-status="<?= htmlspecialchars($req['status'], ENT_QUOTES) ?>">
                                            <i data-lucide="eye" style="width:13px;height:13px;"></i> View
                                        </button>
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
                                            <button type="button" class="action-btn reject cr-reply-btn" title="Reject with reply"
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

<!-- ===== View Reply Modal ===== -->
<div id="crViewReplyModal" class="modal-overlay cr-modal">
    <div class="modal-box" style="width:480px;">
        <div class="modal-header">
            <h3>
                <span class="cr-header-icon">
                    <i data-lucide="message-square-reply" style="width:17px;height:17px;"></i>
                </span>
                Staff Reply
            </h3>
            <button type="button" class="modal-close" aria-label="Close" onclick="document.getElementById('crViewReplyModal').classList.remove('active')">&times;</button>
        </div>
        <div class="cr-modal-body">
            <div class="cr-modal-info">
                <div class="cr-info-row">
                    <span class="cr-info-label">Order</span>
                    <span class="cr-info-value" id="vrOrder"></span>
                </div>
                <div class="cr-info-row">
                    <span class="cr-info-label">Decision</span>
                    <span class="cr-info-value" id="vrStatus"></span>
                </div>
            </div>
            <div class="cr-reply-bubble" id="vrReplyText"></div>
        </div>
        <div class="cr-modal-footer">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('crViewReplyModal').classList.remove('active')">Close</button>
        </div>
    </div>
</div>

<!-- ===== Reply Modal ===== -->
<div id="crReplyModal" class="modal-overlay cr-modal">
    <div class="modal-box" style="width:540px;">
        <div class="modal-header">
            <h3>
                <span class="cr-header-icon">
                    <i data-lucide="message-square-reply" style="width:17px;height:17px;"></i>
                </span>
                <span id="crReplyTitle">Reply to Cancel Request</span>
            </h3>
            <button type="button" class="modal-close" aria-label="Close" onclick="closeCrModal()">&times;</button>
        </div>
        <form id="crReplyForm" method="POST">
            <?= csrf_field() ?>
            <div class="cr-modal-body">
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
                <div class="form-group">
                    <label class="form-label">Your Reply to Customer <span style="color:var(--text-secondary);font-weight:400;text-transform:none;letter-spacing:0;">(visible to customer)</span></label>
                    <textarea name="admin_notes" id="crReplyText" class="form-control" rows="3" placeholder="e.g. We've approved your cancellation. Refund will be processed within 3-5 days." required></textarea>
                </div>
            </div>
            <div class="cr-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeCrModal()">Cancel</button>
                <button type="submit" class="btn" id="crSubmitBtn">
                    <i data-lucide="send" style="width:15px;height:15px;"></i> <span id="crSubmitText">Approve & Reply</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Premium cancel-request modals — fade in AND out via transitions, no blur */
.cr-modal {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(15, 23, 42, .62);
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
    opacity: 0;
    visibility: hidden;
    transition: opacity .28s ease, visibility .28s ease;
}
.cr-modal.active {
    opacity: 1;
    visibility: visible;
}
.cr-modal .modal-box {
    background: var(--white);
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, .18);
    max-width: 94vw;
    max-height: 92vh;
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 32px 80px rgba(2, 6, 23, .35), 0 4px 18px rgba(2, 6, 23, .18);
    transform: translateY(26px) scale(.96);
    opacity: 0;
    transition: transform .32s cubic-bezier(.21, 1.02, .35, 1), opacity .26s ease;
}
.cr-modal.active .modal-box {
    transform: translateY(0) scale(1);
    opacity: 1;
}
.cr-modal .modal-box form {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
}
.cr-modal .modal-header {
    display: flex; align-items: center; justify-content: space-between;
    gap: 14px;
    padding: 18px 24px;
    margin: 0;
    background: linear-gradient(135deg, #1B2A4A 0%, #24385f 55%, #2D4A7A 100%);
    flex-shrink: 0;
}
.cr-modal .modal-header h3 {
    margin: 0;
    color: #fff;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: -.01em;
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cr-header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px; height: 34px;
    flex-shrink: 0;
    border-radius: 10px;
    background: rgba(249, 115, 22, .18);
    border: 1px solid rgba(249, 115, 22, .35);
    color: #fb923c;
}
.cr-modal .modal-close {
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .16);
    color: rgba(255, 255, 255, .85);
    border-radius: 10px;
    font-size: 19px;
    line-height: 1;
    cursor: pointer;
    transition: background .2s ease, color .2s ease, transform .18s ease;
}
.cr-modal .modal-close:hover {
    background: var(--danger);
    border-color: var(--danger);
    color: #fff;
    transform: rotate(90deg);
}
.cr-modal-body {
    padding: 22px 24px 16px;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}
.cr-modal .form-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-secondary, #64748b);
    margin-bottom: 6px;
}
.cr-modal .form-control {
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 10px 13px;
    font-size: .88rem;
    background: #fbfcfe;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}
.cr-modal .form-control:focus {
    border-color: var(--accent, #F97316);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, .1);
    outline: none;
}
.cr-modal textarea.form-control { resize: vertical; min-height: 90px; }
.cr-reply-bubble {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    font-size: .92rem;
    line-height: 1.6;
    color: var(--charcoal);
    white-space: pre-wrap;
}
.cr-modal-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    background: rgba(248, 250, 252, .9);
    border-top: 1px solid var(--border);
    flex-shrink: 0;
}
.cr-modal-footer .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 12px;
    padding: 11px 18px;
    font-weight: 700;
    font-size: .84rem;
    line-height: 1;
    min-height: 44px;
    transition: transform .15s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease;
}
.cr-modal-footer .btn-outline {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    color: #1e293b;
}
.cr-modal-footer .btn-outline:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.cr-modal-footer .btn-accent {
    background: linear-gradient(135deg, #F97316 0%, #ea6a0c 100%);
    border: none;
    color: #fff;
    box-shadow: 0 8px 20px rgba(249, 115, 22, .32);
}
.cr-modal-footer .btn-accent:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 26px rgba(249, 115, 22, .4);
}
.cr-modal-footer .btn-danger-solid {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: #fff;
    box-shadow: 0 8px 20px rgba(239, 68, 68, .32);
}
.cr-modal-footer .btn-danger-solid:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 26px rgba(239, 68, 68, .4);
}

/* Cancel Request Modal Info */
.cr-modal-info {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.1rem;
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
.btn-view-reply {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: .8rem;
    font-weight: 600;
    color: var(--primary, #2563eb);
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 4px 10px;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.btn-view-reply:hover {
    background: var(--primary, #2563eb);
    color: #fff;
    border-color: var(--primary, #2563eb);
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
                submitBtn.removeAttribute('style');
                replyTxt.placeholder = 'e.g. Your cancellation has been approved. Refund will be processed shortly.';
                form.action = '<?= APP_URL ?>/index.php?url=staff/cancel-requests/' + id + '/approve';
            } else {
                title.textContent    = 'Reject & Reply';
                submitTxt.textContent = 'Reject & Send Reply';
                submitBtn.className  = 'btn btn-danger-solid';
                submitBtn.removeAttribute('style');
                replyTxt.placeholder = 'e.g. We cannot cancel this order as it has already been shipped.';
                form.action = '<?= APP_URL ?>/index.php?url=staff/cancel-requests/' + id + '/reject';
            }

            modal.classList.add('active');
            if (window.lucide) lucide.createIcons({ nodes: [modal] });
            setTimeout(function(){ replyTxt.focus(); }, 100);
            return;
        }

        if (e.target === modal) {
            closeCrModal();
        }

        // View reply button
        var vBtn = e.target.closest('.btn-view-reply');
        if (vBtn) {
            var vrModal = document.getElementById('crViewReplyModal');
            document.getElementById('vrOrder').textContent   = vBtn.getAttribute('data-order') || '—';
            document.getElementById('vrReplyText').textContent = vBtn.getAttribute('data-reply') || '';
            var st = vBtn.getAttribute('data-status');
            var stEl = document.getElementById('vrStatus');
            stEl.textContent = st.charAt(0).toUpperCase() + st.slice(1);
            stEl.style.color = st === 'approved' ? 'var(--success, #16a34a)' : 'var(--danger, #dc2626)';
            vrModal.classList.add('active');
            if (window.lucide) lucide.createIcons();
        }

        if (e.target === document.getElementById('crViewReplyModal')) {
            document.getElementById('crViewReplyModal').classList.remove('active');
        }
    });
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
