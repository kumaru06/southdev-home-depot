<?php
/* $pageTitle, $extraCss, $isAdmin, $requests, $summary set by InventoryController::supplierRequests() */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

if ($_SESSION['role_id'] == ROLE_INVENTORY) {
    $invBase = APP_URL . '/index.php?url=inventory/stock';
} elseif ($_SESSION['role_id'] == ROLE_SUPER_ADMIN) {
    $invBase = APP_URL . '/index.php?url=admin/inventory';
} else {
    $invBase = APP_URL . '/index.php?url=staff/inventory';
}
$reqBase = $invBase . '/supplier-requests';
if ($_SESSION['role_id'] == ROLE_INVENTORY) {
    $filterUrl = 'inventory/stock/supplier-requests';
} elseif ($_SESSION['role_id'] == ROLE_SUPER_ADMIN) {
    $filterUrl = 'admin/inventory/supplier-requests';
} else {
    $filterUrl = 'staff/inventory/supplier-requests';
}
$isSuperAdmin = (int)($_SESSION['role_id'] ?? 0) === ROLE_SUPER_ADMIN;
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><i data-lucide="truck" style="width:22px;height:22px;color:#7C3AED;"></i> <?= htmlspecialchars($pageTitle) ?></h2>
        </div>
        <div class="top-bar-right">
            <a href="<?= $invBase ?>" class="btn btn-outline btn-sm">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i> Back to Inventory
            </a>
        </div>
    </div>

    <div class="page-content page-content--table-locked">

        <div class="stat-cards" style="margin-bottom: 24px; grid-template-columns: repeat(4, minmax(0, 1fr));">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Pending</span>
                    <span class="stat-value"><?= (int)($summary['pending'] ?? 0) ?></span>
                </div>
                <div class="stat-icon" style="background:#FEF3C7;color:#B45309;"><i data-lucide="clock"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Ordered</span>
                    <span class="stat-value"><?= (int)($summary['ordered'] ?? 0) ?></span>
                </div>
                <div class="stat-icon" style="background:#EDE9FE;color:#6D28D9;"><i data-lucide="truck"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Received</span>
                    <span class="stat-value"><?= (int)($summary['received'] ?? 0) ?></span>
                </div>
                <div class="stat-icon" style="background:#DCFCE7;color:#166534;"><i data-lucide="package-check"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Cancelled</span>
                    <span class="stat-value"><?= (int)($summary['cancelled'] ?? 0) ?></span>
                </div>
                <div class="stat-icon" style="background:#FEE2E2;color:#B91C1C;"><i data-lucide="x-circle"></i></div>
            </div>
        </div>

        <div class="card filter-bar">
            <form method="GET" class="filter-form">
                <input type="hidden" name="url" value="<?= htmlspecialchars($filterUrl) ?>">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending', 'ordered', 'received', 'cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= (isset($_GET['status']) && $_GET['status'] === $s) ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-accent"><i data-lucide="filter"></i> Filter</button>
            </form>
        </div>

        <div class="data-table-wrap data-table-wrap--locked">
            <table class="data-table" style="table-layout:auto;">
                <thead>
                    <tr>
                        <th style="width:50px;">ID</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th style="text-align:center;">Req. Qty</th>
                        <th style="text-align:center;">Stock</th>
                        <th>Requested By</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="min-width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $req): ?>
                            <?php
                                $status = $req['status'] ?? 'pending';
                                $badgeClass = match ($status) {
                                    'pending'   => 'badge-pending',
                                    'ordered'   => 'badge-processing',
                                    'received'  => 'badge-delivered',
                                    'cancelled' => 'badge-cancelled',
                                    default     => 'badge-pending',
                                };
                                $requester = trim(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? ''));
                            ?>
                            <tr>
                                <td><strong>#<?= (int)$req['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($req['product_name'] ?? '') ?></strong>
                                </td>
                                <td><?= htmlspecialchars($req['sku'] ?? '—') ?></td>
                                <td style="text-align:center;"><strong><?= (int)$req['requested_quantity'] ?></strong></td>
                                <td style="text-align:center;"><?= (int)($req['current_stock'] ?? 0) ?></td>
                                <td><?= $requester !== '' ? htmlspecialchars($requester) : '<em style="opacity:.6">—</em>' ?></td>
                                <td title="<?= htmlspecialchars($req['notes'] ?? '') ?>">
                                    <?php if (!empty($req['notes'])): ?>
                                        <?= htmlspecialchars(strlen($req['notes']) > 40 ? substr($req['notes'], 0, 40) . '…' : $req['notes']) ?>
                                    <?php else: ?>
                                        <em style="opacity:.5">—</em>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span></td>
                                <td><?= !empty($req['created_at']) ? date('M d, Y', strtotime($req['created_at'])) : '—' ?></td>
                                <td>
                                    <?php if ($status === 'pending'): ?>
                                        <div class="action-group" style="flex-wrap:wrap;gap:6px;">
                                            <?php if ($isSuperAdmin): ?>
                                            <form action="<?= $reqBase ?>/<?= (int)$req['id'] ?>/update" method="POST" style="margin:0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="ordered">
                                                <button type="submit" class="btn btn-sm btn-accent"
                                                    data-confirm="Approve this request and mark it as ordered from supplier?"
                                                    data-confirm-title="Approve Request"
                                                    data-confirm-ok="Approve">
                                                    <i data-lucide="check-circle" style="width:13px;height:13px"></i> Approve &amp; Mark Ordered
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <span style="font-size:.78rem;color:var(--text-secondary);">Waiting for Super Admin approval</span>
                                            <?php endif; ?>
                                            <form action="<?= $reqBase ?>/<?= (int)$req['id'] ?>/update" method="POST" style="margin:0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm btn-danger-outline"
                                                    data-confirm="Cancel this supplier request?"
                                                    data-confirm-title="Cancel Request"
                                                    data-confirm-ok="Cancel Request"
                                                    data-confirm-variant="danger">
                                                    <i data-lucide="x" style="width:13px;height:13px"></i> Cancel
                                                </button>
                                            </form>
                                        </div>
                                    <?php elseif ($status === 'ordered'): ?>
                                        <div class="action-group" style="flex-wrap:wrap;gap:6px;">
                                            <button type="button" class="btn btn-sm btn-accent js-receive-btn"
                                                data-id="<?= (int)$req['id'] ?>"
                                                data-name="<?= htmlspecialchars($req['product_name'] ?? '', ENT_QUOTES) ?>"
                                                data-qty="<?= (int)$req['requested_quantity'] ?>"
                                                data-stock="<?= (int)($req['current_stock'] ?? 0) ?>">
                                                <i data-lucide="package-check" style="width:13px;height:13px"></i> Receive &amp; Add Stock
                                            </button>
                                            <?php if ($isSuperAdmin): ?>
                                            <form action="<?= $reqBase ?>/<?= (int)$req['id'] ?>/update" method="POST" style="margin:0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm btn-danger-outline"
                                                    data-confirm="Cancel this ordered request?"
                                                    data-confirm-title="Cancel Request"
                                                    data-confirm-ok="Cancel"
                                                    data-confirm-variant="danger">
                                                    Cancel
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size:.8rem;color:var(--text-secondary);">No actions</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center" style="padding:2rem;color:var(--text-secondary);">
                                No supplier requests found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Receive & Add Stock Modal -->
<div id="receiveModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <h3>Receive &amp; Add Stock</h3>
            <button type="button" class="modal-close" id="receiveModalClose">&times;</button>
        </div>
        <form id="formReceive" method="POST" action="">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Product</label>
                <input type="text" id="receiveProductName" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Current Stock</label>
                <input type="text" id="receiveCurrentStock" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity Received <span class="required">*</span></label>
                <input type="number" name="add_quantity" id="receiveQty" class="form-control" min="1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Reason / Notes</label>
                <input type="text" name="reason" id="receiveReason" class="form-control" placeholder="e.g. Delivery arrived">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="receiveModalCancel">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i data-lucide="package-check" style="width:15px;height:15px"></i> Confirm Receive
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
    padding: 16px;
}
.modal-box {
    background: #fff; border-radius: 14px; width: 100%;
    box-shadow: 0 20px 50px rgba(0,0,0,.2);
    max-height: 90vh; overflow-y: auto;
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--border, #e8ecf1);
}
.modal-header h3 { margin: 0; font-size: 1.05rem; }
.modal-close {
    border: none; background: transparent; font-size: 1.5rem;
    cursor: pointer; line-height: 1; color: #64748b;
}
.modal-box .form-group { padding: 0 20px; margin: 14px 0; }
.modal-box .form-actions {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 16px 20px 20px; border-top: 1px solid var(--border, #e8ecf1);
}
@media (max-width: 900px) {
    .stat-cards { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
}
</style>

<script>
(function () {
    var modal = document.getElementById('receiveModal');
    var form = document.getElementById('formReceive');
    if (!modal || !form) return;

    var reqBase = <?= json_encode($reqBase) ?>;

    function openReceive(btn) {
        var id = btn.getAttribute('data-id');
        var name = btn.getAttribute('data-name') || '';
        var qty = btn.getAttribute('data-qty') || '1';
        var stock = btn.getAttribute('data-stock') || '0';
        form.action = reqBase + '/' + id + '/receive';
        document.getElementById('receiveProductName').value = name;
        document.getElementById('receiveCurrentStock').value = stock;
        document.getElementById('receiveQty').value = qty;
        document.getElementById('receiveReason').value = 'Supplier request #' + id + ' received';
        modal.style.display = 'flex';
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function closeReceive() {
        modal.style.display = 'none';
    }

    document.querySelectorAll('.js-receive-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { openReceive(btn); });
    });
    document.getElementById('receiveModalClose').addEventListener('click', closeReceive);
    document.getElementById('receiveModalCancel').addEventListener('click', closeReceive);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeReceive();
    });
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
