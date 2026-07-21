<?php
/* $pageTitle, $extraCss, $isAdmin set by InventoryController */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
$canManageStock = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [ROLE_SUPER_ADMIN, ROLE_STAFF, ROLE_INVENTORY]);

/* Build base inventory URL based on current user role */
if ($_SESSION['role_id'] == ROLE_INVENTORY) {
    $invBase = APP_URL . '/index.php?url=inventory/stock';
} elseif ($_SESSION['role_id'] == ROLE_SUPER_ADMIN) {
    $invBase = APP_URL . '/index.php?url=admin/inventory';
} else {
    $invBase = APP_URL . '/index.php?url=staff/inventory';
}
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
        <div class="top-bar-right">
            <a href="<?= $invBase ?>/supplier-requests" class="btn btn-outline btn-sm" style="color:#7C3AED;border-color:#7C3AED;">
                <i data-lucide="truck" style="width:15px;height:15px"></i> Supplier Requests
            </a>
            <a href="<?= $invBase ?>/damaged" class="btn btn-outline btn-sm" style="color:var(--danger);border-color:var(--danger);">
                <i data-lucide="alert-octagon" style="width:15px;height:15px"></i> Damaged Products
            </a>
            <a href="<?= $invBase ?>/movements" class="btn btn-outline btn-sm">
                <i data-lucide="activity" style="width:15px;height:15px"></i> Stock Movements
            </a>
            <a href="<?= $invBase ?>/price-history" class="btn btn-outline btn-sm">
                <i data-lucide="trending-up" style="width:15px;height:15px"></i> Price History
            </a>
        </div>
    </div>

    <div class="page-content page-content--table-locked">
        <?php if (!empty($lowStock)): ?>
            <div class="alert alert-warning" style="display:flex;align-items:flex-start;gap:12px;">
                <i data-lucide="alert-triangle" style="flex-shrink:0;margin-top:2px;"></i>
                <div style="flex:1;">
                    <strong>Low Stock Alert:</strong> <?= count($lowStock) ?> item(s) are below reorder level.
                    <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;">
                        <?php foreach ($lowStock as $ls): ?>
                            <span class="badge badge-cancelled" style="font-size:.78rem;">
                                <?= htmlspecialchars($ls['product_name']) ?> (<?= $ls['quantity'] ?> left)
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Category Filter -->
        <div class="card filter-bar" style="margin-bottom:16px;">
            <div class="inv-filter-row">
                <div class="inv-filter-group">
                    <label for="categoryFilter" class="inv-filter-label"><i data-lucide="layers" style="width:15px;height:15px;"></i> Category</label>
                    <select id="categoryFilter" class="form-control inv-filter-select">
                        <option value="">All Categories</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (<?= $cat['product_count'] ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="inv-filter-group">
                    <label for="stockFilter" class="inv-filter-label"><i data-lucide="bar-chart-2" style="width:15px;height:15px;"></i> Stock Status</label>
                    <select id="stockFilter" class="form-control inv-filter-select">
                        <option value="">All Statuses</option>
                        <option value="in-stock">In Stock</option>
                        <option value="low-stock">Low Stock</option>
                        <option value="out-of-stock">Out of Stock</option>
                    </select>
                </div>
                <div class="inv-filter-count">
                    <span id="filterCount"></span>
                </div>
            </div>
        </div>

        <div class="data-table-wrap data-table-wrap--locked">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Current Stock</th>
                        <th title="Triggers Low Stock warning when stock falls to this level">Reorder Level</th>
                        <th>Status</th>
                        <?php if ($canManageStock): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inventory)): ?>
                        <?php foreach ($inventory as $item): ?>
                            <?php
                                $qty = intval($item['quantity']);
                                $reorder = intval($item['reorder_level'] ?? 10);
                                $isLow = $qty <= $reorder && $qty > 0;
                                $isOut = $qty <= 0;
                            ?>
                            <tr class="<?= $isOut ? 'row-danger' : ($isLow ? 'row-warning' : '') ?>" data-category="<?= $item['category_id'] ?? '' ?>" data-stock="<?= $isOut ? 'out-of-stock' : ($isLow ? 'low-stock' : 'in-stock') ?>">
                                <td><code><?= htmlspecialchars($item['sku'] ?? 'N/A') ?></code></td>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <div style="width:48px;height:48px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;"><i data-lucide="image" style="width:20px;height:20px;color:#94a3b8;"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td>₱<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <?php if ($isOut): ?>
                                        <span class="badge badge-cancelled">0 — Out of Stock</span>
                                    <?php elseif ($isLow): ?>
                                        <span class="badge badge-cancelled"><?= $qty ?> — Low</span>
                                    <?php else: ?>
                                        <span class="badge badge-delivered"><?= $qty ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $reorder ?></td>
                                <td>
                                    <?php if ($isOut): ?>
                                        <span class="badge badge-cancelled"><i data-lucide="x-circle" style="width:11px;height:11px"></i> Out of Stock</span>
                                    <?php elseif ($isLow): ?>
                                        <span class="badge badge-pending"><i data-lucide="alert-triangle" style="width:11px;height:11px"></i> Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge badge-delivered"><i data-lucide="check-circle" style="width:11px;height:11px"></i> In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canManageStock): ?>
                                <td>
                                    <div class="action-btn-group" style="flex-wrap:wrap;gap:4px;">
                                        <!-- Update Stock -->
                                        <button type="button" class="action-btn edit" data-id="<?= $item['product_id'] ?>" data-name="<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>" data-qty="<?= $qty ?>" data-mode="update" title="Set Stock">
                                            <i data-lucide="edit-3" style="width:13px;height:13px"></i> Update
                                        </button>
                                        <!-- Add Stock -->
                                        <button type="button" class="action-btn approve" data-id="<?= $item['product_id'] ?>" data-name="<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>" data-qty="<?= $qty ?>" data-mode="add" title="Add Stock">
                                            <i data-lucide="plus-circle" style="width:13px;height:13px"></i> Add Stock
                                        </button>
                                        <!-- Request Supplier -->
                                        <?php if ($isLow || $isOut): ?>
                                            <?php
                                            $openReqId = $openSupplierByProduct[(int)$item['product_id']] ?? null;
                                            ?>
                                            <?php if ($openReqId): ?>
                                        <a href="<?= $invBase ?>/supplier-requests" class="action-btn" style="background:#7C3AED;color:#fff;text-decoration:none;" title="Open supplier request #<?= (int)$openReqId ?>">
                                            <i data-lucide="truck" style="width:13px;height:13px"></i> View Request
                                        </a>
                                            <?php else: ?>
                                        <button type="button" class="action-btn" style="background:#7C3AED;color:#fff;" data-id="<?= $item['product_id'] ?>" data-name="<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>" data-qty="<?= $qty ?>" data-mode="supplier" title="Request Supplier">
                                            <i data-lucide="truck" style="width:13px;height:13px"></i> Request Supplier
                                        </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= $canManageStock ? 8 : 7 ?>" class="text-center">No inventory records found.</td></tr>
                    <?php endif; ?>
                    <tr id="noFilterResults" style="display:none;"><td colspan="<?= $canManageStock ? 8 : 7 ?>" class="text-center" style="padding:2rem;color:var(--text-secondary);"><i data-lucide="search-x" style="width:24px;height:24px;margin-bottom:6px;"></i><br>No products match the selected filters.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== Stock Action Modal ===== -->
<?php if ($canManageStock): ?>
<style>
/* Premium inventory stock modal — no backdrop blur (perf) */
#stockModal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(15, 23, 42, .62);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    visibility: hidden;
    transition: opacity .28s ease, visibility 0s linear .28s;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
    filter: none !important;
}
#stockModal.active {
    display: flex;
    opacity: 1;
    visibility: visible;
    transition: opacity .28s ease, visibility 0s;
}
#stockModal.closing {
    display: flex;
    opacity: 0;
    visibility: visible;
    transition: opacity .28s ease .06s, visibility 0s linear .34s;
}
#stockModal .modal-box {
    width: 520px;
    max-width: 94vw;
    max-height: 92vh;
    background: #fff;
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, .18);
    box-shadow: 0 32px 80px rgba(2, 6, 23, .35), 0 4px 18px rgba(2, 6, 23, .18);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transform: translateY(26px) scale(.96);
    opacity: 0;
    transition: transform .32s cubic-bezier(.21, 1.02, .35, 1), opacity .26s ease;
}
#stockModal.active .modal-box {
    transform: translateY(0) scale(1);
    opacity: 1;
}
#stockModal.closing .modal-box {
    transform: translateY(18px) scale(.96);
    opacity: 0;
    transition: transform .28s cubic-bezier(.5, 0, .75, .4), opacity .24s ease;
}
#stockModal .modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 24px;
    margin: 0;
    background: linear-gradient(135deg, #1B2A4A 0%, #24385f 55%, #2D4A7A 100%);
    border-bottom: none;
}
#stockModal .modal-header h3 {
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
#stockModal .stock-header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    border-radius: 10px;
    background: rgba(249, 115, 22, .18);
    border: 1px solid rgba(249, 115, 22, .35);
    color: #fb923c;
}
#stockModal[data-mode="supplier"] .stock-header-icon {
    background: rgba(124, 58, 237, .2);
    border-color: rgba(124, 58, 237, .4);
    color: #c4b5fd;
}
#stockModal .modal-close {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .16);
    color: rgba(255, 255, 255, .85);
    border-radius: 10px;
    font-size: 19px;
    line-height: 1;
    cursor: pointer;
    transition: background .2s ease, color .2s ease, transform .18s ease;
}
#stockModal .modal-close:hover {
    background: var(--danger);
    border-color: var(--danger);
    color: #fff;
    transform: rotate(90deg);
}
#stockModal .stock-modal-body {
    padding: 22px 24px 16px;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
    background:
        radial-gradient(800px 200px at 50% -70px, rgba(45, 74, 122, .05), transparent 60%),
        #fff;
}
#stockModal .form-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-secondary, #64748b);
    margin-bottom: 6px;
}
#stockModal .form-control {
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 10px 13px;
    font-size: .88rem;
    background: #fbfcfe;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}
#stockModal .form-control:focus {
    border-color: var(--accent, #F97316);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, .1);
    outline: none;
}
#stockModal .form-control[readonly] {
    background: #f1f5f9;
    color: #334155;
    font-weight: 600;
}
#stockModal #addNewTotal {
    font-weight: 800 !important;
    color: var(--accent) !important;
    background: #fff8f0 !important;
}
#stockModal .form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    margin: 0;
    background: rgba(248, 250, 252, .9);
    border-top: 1px solid var(--border);
}
#stockModal .form-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 12px;
    padding: 11px 18px;
    font-weight: 700;
    font-size: .84rem;
    letter-spacing: .02em;
    line-height: 1;
    min-height: 44px;
    transition: transform .15s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease;
}
#stockModal .form-actions .btn i,
#stockModal .form-actions .btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
#stockModal .form-actions .btn-outline {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    color: #1e293b;
}
#stockModal .form-actions .btn-outline:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
#stockModal .form-actions .btn-accent {
    background: linear-gradient(135deg, #F97316 0%, #ea6a0c 100%);
    border: none;
    color: #fff;
    box-shadow: 0 8px 20px rgba(249, 115, 22, .32);
}
#stockModal .form-actions .btn-accent:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 26px rgba(249, 115, 22, .4);
    background: linear-gradient(135deg, #fb8330 0%, #F97316 100%);
}
#stockModal .form-actions .btn-accent:active {
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(249, 115, 22, .28);
}
#stockModal[data-mode="supplier"] .form-actions .btn-accent {
    background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%);
    box-shadow: 0 8px 20px rgba(124, 58, 237, .32);
}
#stockModal[data-mode="supplier"] .form-actions .btn-accent:hover {
    background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    box-shadow: 0 12px 26px rgba(124, 58, 237, .4);
}
.row-danger { background: var(--danger-bg) !important; }

/* ====== Inventory Filter Bar ====== */
.inv-filter-row {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
}
.inv-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 180px;
}
.inv-filter-label {
    font-size: .78rem;
    font-weight: 600;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.inv-filter-select {
    padding: 8px 12px;
    font-size: .88rem;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    transition: border-color .2s, box-shadow .2s;
    cursor: pointer;
    min-width: 200px;
}
.inv-filter-select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(249,115,22,.15);
    outline: none;
}
.inv-filter-count {
    margin-left: auto;
    font-size: .82rem;
    color: var(--text-secondary);
    align-self: flex-end;
    padding-bottom: 10px;
}
.inv-filter-count span {
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
}
@media (max-width: 640px) {
    .inv-filter-row { flex-direction: column; align-items: stretch; }
    .inv-filter-select { min-width: 100%; }
    .inv-filter-count { margin-left: 0; text-align: center; }
    #stockModal .modal-box { border-radius: 16px; }
}
</style>

<div id="stockModal" class="modal-overlay" data-mode="update">
    <div class="modal-box">
        <div class="modal-header">
            <h3>
                <span class="stock-header-icon" id="stockHeaderIcon">
                    <i data-lucide="package" style="width:17px;height:17px;"></i>
                </span>
                <span id="stockModalTitle">Update Stock</span>
            </h3>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>

        <!-- Update Stock Form -->
        <form id="formUpdateStock" action="<?= $invBase ?>/update" method="POST" style="display:none;">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" id="updateProductId">
            <div class="stock-modal-body">
                <div class="form-group">
                    <label class="form-label">Product</label>
                    <input type="text" id="updateProductName" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Set Quantity To</label>
                    <input type="number" name="quantity" id="updateQuantity" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Reason</label>
                    <input type="text" name="reason" class="form-control" placeholder="e.g. Manual count correction" value="Manual stock update">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline btn-close-stock-modal">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i data-lucide="package-check" style="width:16px;height:16px"></i>
                    <span>Update Stock</span>
                </button>
            </div>
        </form>

        <!-- Add Stock Form -->
        <form id="formAddStock" action="<?= $invBase ?>/add-stock" method="POST" style="display:none;">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" id="addProductId">
            <div class="stock-modal-body">
                <div class="form-group">
                    <label class="form-label">Product</label>
                    <input type="text" id="addProductName" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Current Stock</label>
                    <input type="text" id="addCurrentStock" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity to Add</label>
                    <input type="number" name="add_quantity" id="addQuantityInput" class="form-control" min="1" required placeholder="e.g. 50" oninput="updateNewTotal()">
                </div>
                <div class="form-group">
                    <label class="form-label">New Total After Adding</label>
                    <input type="text" id="addNewTotal" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Reason</label>
                    <input type="text" name="reason" class="form-control" placeholder="e.g. New shipment received" value="Stock purchase/restock">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline btn-close-stock-modal">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i data-lucide="plus-circle" style="width:16px;height:16px"></i>
                    <span>Add Stock</span>
                </button>
            </div>
        </form>

        <!-- Supplier Request Form -->
        <form id="formSupplier" action="<?= $invBase ?>/request-supplier" method="POST" style="display:none;">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" id="supplierProductId">
            <div class="stock-modal-body">
                <div class="form-group">
                    <label class="form-label">Product</label>
                    <input type="text" id="supplierProductName" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Requested Quantity</label>
                    <input type="number" name="request_quantity" class="form-control" min="1" required placeholder="e.g. 100">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes (supplier name, PO reference, etc.)"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline btn-close-stock-modal">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i data-lucide="truck" style="width:16px;height:16px"></i>
                    <span>Submit Request</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* ====== Inventory Filter ====== */
(function() {
    'use strict';
    var catFilter   = document.getElementById('categoryFilter');
    var stockFilter = document.getElementById('stockFilter');
    var countEl     = document.getElementById('filterCount');
    var noResults   = document.getElementById('noFilterResults');

    function applyFilters() {
        var cat   = catFilter ? catFilter.value : '';
        var stock = stockFilter ? stockFilter.value : '';
        var rows  = document.querySelectorAll('.data-table tbody tr[data-category]');
        var shown = 0;
        rows.forEach(function(row) {
            var matchCat   = !cat   || row.getAttribute('data-category') === cat;
            var matchStock = !stock || row.getAttribute('data-stock') === stock;
            if (matchCat && matchStock) {
                row.style.display = '';
                shown++;
            } else {
                row.style.display = 'none';
            }
        });
        if (noResults) noResults.style.display = (rows.length > 0 && shown === 0) ? '' : 'none';
        if (countEl) countEl.textContent = shown + ' of ' + rows.length + ' products';
    }

    if (catFilter)   catFilter.addEventListener('change', applyFilters);
    if (stockFilter) stockFilter.addEventListener('change', applyFilters);
    applyFilters();
})();
</script>

<script>
/* ====== Inventory Modal – event-delegation approach ====== */
(function () {
    'use strict';

    /* ---- helpers ---- */
    function $(id) { return document.getElementById(id); }

    function setStockHeaderIcon(mode) {
        var iconWrap = $('stockHeaderIcon');
        if (!iconWrap) return;
        var icon = 'package';
        if (mode === 'add') icon = 'plus-circle';
        if (mode === 'supplier') icon = 'truck';
        iconWrap.innerHTML = '<i data-lucide="' + icon + '" style="width:17px;height:17px;"></i>';
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons({ nodes: [iconWrap] });
        }
    }

    function openStockModal(btn) {
        var productId   = btn.getAttribute('data-id');
        var productName = btn.getAttribute('data-name');
        var currentQty  = parseInt(btn.getAttribute('data-qty'), 10);
        var mode        = btn.getAttribute('data-mode');

        var modal = $('stockModal');
        modal.classList.remove('closing');
        modal.setAttribute('data-mode', mode || 'update');
        modal.style.display = 'flex';
        // force reflow so open animation plays
        void modal.offsetWidth;
        modal.classList.add('active');

        $('formUpdateStock').style.display       = 'none';
        $('formAddStock').style.display          = 'none';
        $('formSupplier').style.display          = 'none';
        setStockHeaderIcon(mode);

        if (mode === 'update') {
            $('stockModalTitle').textContent  = 'Update Stock — ' + productName;
            $('updateProductId').value        = productId;
            $('updateProductName').value      = productName;
            $('updateQuantity').value         = currentQty;
            $('formUpdateStock').style.display = 'block';
        } else if (mode === 'add') {
            $('stockModalTitle').textContent  = 'Add Stock — ' + productName;
            $('addProductId').value           = productId;
            $('addProductName').value         = productName;
            $('addCurrentStock').value        = currentQty + ' units';
            $('addQuantityInput').value       = '';
            $('addNewTotal').value            = '—';
            window._addCurrentQty            = currentQty;   // store for live calc
            $('formAddStock').style.display   = 'block';
        } else if (mode === 'supplier') {
            $('stockModalTitle').textContent  = 'Request Supplier — ' + productName;
            $('supplierProductId').value      = productId;
            $('supplierProductName').value    = productName;
            $('formSupplier').style.display   = 'block';
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons({ nodes: [modal] });
        }
    }

    function closeStockModal() {
        var modal = $('stockModal');
        if (!modal || (!modal.classList.contains('active') && modal.style.display === 'none')) return;
        modal.classList.add('closing');
        modal.classList.remove('active');
        setTimeout(function () {
            modal.classList.remove('closing');
            modal.style.display = 'none';
        }, 340);
    }

    /* Expose globally so inline onclick still works as fallback */
    window.openStockModal  = openStockModal;
    window.closeStockModal = closeStockModal;

    /* Live "New Total" calculation in Add Stock modal */
    window.updateNewTotal = function() {
        var addedInput = document.getElementById('addQuantityInput');
        var totalInput = document.getElementById('addNewTotal');
        if (!addedInput || !totalInput) return;
        var added   = parseInt(addedInput.value, 10);
        var current = parseInt(window._addCurrentQty || 0, 10);
        if (!isNaN(added) && added > 0) {
            totalInput.value = (current + added) + ' units  (was ' + current + ' + ' + added + ' added)';
        } else {
            totalInput.value = '—';
        }
    };

    /* ---- event delegation (catches clicks on SVG icons inside buttons too) ---- */
    document.addEventListener('click', function (e) {
        /* Open modal – any .action-btn with data-mode inside .action-btn-group */
        var actionBtn = e.target.closest('button[data-mode]');
        if (actionBtn) {
            e.preventDefault();
            openStockModal(actionBtn);
            return;
        }

        /* Close modal – × button or Cancel button */
        if (e.target.closest('#stockModal .modal-close') || e.target.closest('.btn-close-stock-modal')) {
            closeStockModal();
            return;
        }

        /* Close modal – backdrop click */
        if (e.target.id === 'stockModal') {
            closeStockModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeStockModal();
    });
})();
</script>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
