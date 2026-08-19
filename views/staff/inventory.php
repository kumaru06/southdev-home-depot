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
$productsManageUrl = ((int) ($_SESSION['role_id'] ?? 0) === ROLE_SUPER_ADMIN)
    ? APP_URL . '/index.php?url=admin/products'
    : null;

$invStats = ['total' => 0, 'in_stock' => 0, 'low_stock' => 0, 'out_of_stock' => 0];
foreach ($inventory ?? [] as $invRow) {
    $invStats['total']++;
    $hasSizesRow = ((int) ($invRow['size_option_count'] ?? 0)) > 0;
    $qtyRow = $hasSizesRow ? (int) ($invRow['size_option_stock'] ?? 0) : (int) ($invRow['quantity'] ?? 0);
    $reorderRow = Inventory::effectiveReorderLevel((float) ($invRow['price'] ?? 0), (int) ($invRow['reorder_level'] ?? 10));
    if ($qtyRow <= 0) {
        $invStats['out_of_stock']++;
    } elseif (!$hasSizesRow && $qtyRow <= $reorderRow) {
        $invStats['low_stock']++;
    } else {
        $invStats['in_stock']++;
    }
}
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
        <div class="top-bar-right inv-toolbar">
            <a href="<?= $invBase ?>/supplier-requests" class="btn btn-outline btn-sm btn--supplier">
                <i data-lucide="truck" style="width:15px;height:15px"></i> Supplier Requests
            </a>
            <a href="<?= $invBase ?>/damaged" class="btn btn-outline btn-sm btn--damaged">
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
        <div class="inv-stats">
            <div class="inv-stat inv-stat--total">
                <div>
                    <span class="inv-stat__label">Total Products</span>
                    <span class="inv-stat__value"><?= $invStats['total'] ?></span>
                </div>
                <span class="inv-stat__icon"><i data-lucide="boxes"></i></span>
            </div>
            <div class="inv-stat inv-stat--ok">
                <div>
                    <span class="inv-stat__label">In Stock</span>
                    <span class="inv-stat__value"><?= $invStats['in_stock'] ?></span>
                </div>
                <span class="inv-stat__icon"><i data-lucide="check-circle"></i></span>
            </div>
            <div class="inv-stat inv-stat--low">
                <div>
                    <span class="inv-stat__label">Low Stock</span>
                    <span class="inv-stat__value"><?= $invStats['low_stock'] ?></span>
                </div>
                <span class="inv-stat__icon"><i data-lucide="alert-triangle"></i></span>
            </div>
            <div class="inv-stat inv-stat--out">
                <div>
                    <span class="inv-stat__label">Out of Stock</span>
                    <span class="inv-stat__value"><?= $invStats['out_of_stock'] ?></span>
                </div>
                <span class="inv-stat__icon"><i data-lucide="x-circle"></i></span>
            </div>
        </div>

        <?php if (!empty($lowStock)): ?>
            <div class="inv-alert">
                <span class="inv-alert__icon"><i data-lucide="alert-triangle"></i></span>
                <div style="flex:1;">
                    <div class="inv-alert__title">Low stock needs attention</div>
                    <p class="inv-alert__copy"><?= count($lowStock) ?> item<?= count($lowStock) !== 1 ? 's are' : ' is' ?> at or below the reorder level.</p>
                    <div class="inv-alert__chips">
                        <?php foreach ($lowStock as $ls): ?>
                            <span class="inv-alert__chip">
                                <?= htmlspecialchars($ls['product_name']) ?>
                                <span style="opacity:.75;">· <?= (int) $ls['quantity'] ?> left</span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="inv-panel">
            <div class="inv-panel__row">
                <div class="inv-search">
                    <i data-lucide="search"></i>
                    <input type="search" id="inventorySearch" placeholder="Search product or SKU..." autocomplete="off">
                </div>
                <div class="inv-filter-group">
                    <label for="categoryFilter" class="inv-filter-label"><i data-lucide="layers" style="width:14px;height:14px;"></i> Category</label>
                    <select id="categoryFilter" class="inv-filter-select">
                        <option value="">All Categories</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (<?= $cat['product_count'] ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="inv-filter-group">
                    <label for="stockFilter" class="inv-filter-label"><i data-lucide="bar-chart-2" style="width:14px;height:14px;"></i> Stock Status</label>
                    <select id="stockFilter" class="inv-filter-select">
                        <option value="">All Statuses</option>
                        <option value="in-stock">In Stock</option>
                        <option value="low-stock">Low Stock</option>
                        <option value="out-of-stock">Out of Stock</option>
                        <option value="sized">Managed by Sizes</option>
                    </select>
                </div>
                <div class="inv-filter-count">
                    <span id="filterCount"></span>
                </div>
            </div>
        </div>

        <div class="inv-table-wrap">
            <div class="inv-table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="inv-col-product">Product</th>
                        <th class="inv-col-category">Category</th>
                        <th class="inv-col-price">Price</th>
                        <th class="inv-col-stock">Stock</th>
                        <th class="inv-col-reorder" title="Auto-adjusts for high-value items — expensive products use a lower threshold">Reorder</th>
                        <th class="inv-col-status">Status</th>
                        <?php if ($canManageStock): ?>
                        <th class="inv-col-actions">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inventory)): ?>
                        <?php foreach ($inventory as $item): ?>
                            <?php
                                $hasSizes = ((int) ($item['size_option_count'] ?? 0)) > 0;
                                $qty = $hasSizes ? (int) ($item['size_option_stock'] ?? 0) : (int) ($item['quantity']);
                                $reorder = Inventory::effectiveReorderLevel(
                                    (float) ($item['price'] ?? 0),
                                    (int) ($item['reorder_level'] ?? 10)
                                );
                                $isLow = !$hasSizes && $qty <= $reorder && $qty > 0;
                                $isOut = $qty <= 0;
                                $stockCap = max($reorder * 3, 1);
                                $stockPct = min(100, (int) round(($qty / $stockCap) * 100));
                                $meterClass = $hasSizes ? 'inv-stock-meter__fill--sized' : ($isOut ? 'inv-stock-meter__fill--out' : ($isLow ? 'inv-stock-meter__fill--low' : 'inv-stock-meter__fill--ok'));
                                $stockState = $hasSizes ? 'sized' : ($isOut ? 'out-of-stock' : ($isLow ? 'low-stock' : 'in-stock'));
                                $sku = trim((string) ($item['sku'] ?? ''));
                            ?>
                            <tr class="<?= $isOut ? 'row-danger' : ($isLow ? 'row-warning' : '') ?>"
                                data-category="<?= $item['category_id'] ?? '' ?>"
                                data-stock="<?= $stockState ?>"
                                data-product-name="<?= htmlspecialchars(strtolower($item['product_name']), ENT_QUOTES) ?>"
                                data-product-sku="<?= htmlspecialchars(strtolower($sku !== '' ? $sku : 'n/a'), ENT_QUOTES) ?>">
                                <td>
                                    <div class="inv-product">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="inv-product__thumb">
                                        <?php else: ?>
                                            <span class="inv-product__thumb-placeholder"><i data-lucide="image" style="width:20px;height:20px;"></i></span>
                                        <?php endif; ?>
                                        <div class="inv-product__body">
                                            <span class="inv-product__name-scroll" title="<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>">
                                                <span class="inv-product__name"><?= htmlspecialchars($item['product_name']) ?></span>
                                            </span>
                                            <div class="inv-product__meta">
                                                <span class="inv-product__tag inv-product__tag--sku"><?= htmlspecialchars($sku !== '' ? $sku : 'N/A') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="inv-category-cell">
                                    <?php if (!empty($item['category_name'])): ?>
                                        <span class="inv-category" title="<?= htmlspecialchars($item['category_name'], ENT_QUOTES) ?>"><?= htmlspecialchars($item['category_name']) ?></span>
                                    <?php else: ?>
                                        <span class="inv-category inv-category--empty">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="inv-price-cell">
                                    <span class="inv-price" title="₱<?= number_format((float) $item['price'], 2) ?>">₱<?= number_format((float) $item['price'], 2) ?></span>
                                    <?php if ((float) $item['price'] >= 10000): ?>
                                        <small>High-value item</small>
                                    <?php endif; ?>
                                </td>
                                <td class="inv-stock-cell">
                                    <div class="inv-stock-qty">
                                        <strong><?= number_format($qty) ?></strong>
                                        <span><?= $hasSizes ? 'across ' . (int) $item['size_option_count'] . ' sizes' : 'units' ?></span>
                                    </div>
                                    <div class="inv-stock-meter" aria-hidden="true">
                                        <div class="inv-stock-meter__fill <?= $meterClass ?>" style="width: <?= $stockPct ?>%;"></div>
                                    </div>
                                    <?php if ($hasSizes): ?>
                                        <div class="inv-stock-note">Per-size stock managed in Products</div>
                                    <?php elseif ($isLow): ?>
                                        <div class="inv-stock-note">At or below reorder level (<?= $reorder ?>)</div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="inv-reorder"><?= $reorder ?></span></td>
                                <td>
                                    <?php if ($hasSizes): ?>
                                        <span class="inv-status inv-status--sizes"><i data-lucide="layers"></i> By Sizes</span>
                                    <?php elseif ($isOut): ?>
                                        <span class="inv-status inv-status--out"><i data-lucide="x-circle"></i> Out</span>
                                    <?php elseif ($isLow): ?>
                                        <span class="inv-status inv-status--low"><i data-lucide="alert-triangle"></i> Low</span>
                                    <?php else: ?>
                                        <span class="inv-status inv-status--in"><i data-lucide="check-circle"></i> In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canManageStock): ?>
                                <td class="inv-actions-cell">
                                    <?php if ($hasSizes): ?>
                                        <?php if ($productsManageUrl): ?>
                                            <a href="<?= $productsManageUrl ?>" class="inv-actions__hint">
                                                <i data-lucide="external-link" style="width:13px;height:13px"></i>
                                                Products
                                            </a>
                                        <?php else: ?>
                                            <span class="inv-actions__hint">Manage in Products</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                    <div class="action-dropdown">
                                        <button type="button" class="action-dropdown-btn" onclick="toggleActionMenu(this, event)">
                                            <i data-lucide="more-horizontal" style="width:16px;height:16px;"></i>
                                            Actions
                                            <i data-lucide="chevron-down" style="width:13px;height:13px;opacity:.6;"></i>
                                        </button>
                                        <div class="action-dropdown-menu">
                                            <button type="button" class="action-menu-item"
                                                    data-id="<?= $item['product_id'] ?>"
                                                    data-name="<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>"
                                                    data-qty="<?= $qty ?>"
                                                    data-mode="update">
                                                <i data-lucide="edit-3" style="width:14px;height:14px;"></i> Update Stock
                                            </button>
                                            <button type="button" class="action-menu-item"
                                                    data-id="<?= $item['product_id'] ?>"
                                                    data-name="<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>"
                                                    data-qty="<?= $qty ?>"
                                                    data-mode="add">
                                                <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> Add Stock
                                            </button>
                                            <?php if ($isLow || $isOut): ?>
                                                <?php $openReqId = $openSupplierByProduct[(int)$item['product_id']] ?? null; ?>
                                                <?php if ($openReqId): ?>
                                            <a href="<?= $invBase ?>/supplier-requests" class="action-menu-item">
                                                <i data-lucide="truck" style="width:14px;height:14px;"></i> View Request
                                            </a>
                                                <?php else: ?>
                                            <button type="button" class="action-menu-item"
                                                    data-id="<?= $item['product_id'] ?>"
                                                    data-name="<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>"
                                                    data-qty="<?= $qty ?>"
                                                    data-mode="supplier">
                                                <i data-lucide="truck" style="width:14px;height:14px;"></i> Request Supplier
                                            </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= $canManageStock ? 7 : 6 ?>" class="text-center">No inventory records found.</td></tr>
                    <?php endif; ?>
                    <tr id="noFilterResults" style="display:none;"><td colspan="<?= $canManageStock ? 7 : 6 ?>" class="text-center" style="padding:2rem;color:var(--text-secondary);"><i data-lucide="search-x" style="width:24px;height:24px;margin-bottom:6px;"></i><br>No products match the selected filters.</td></tr>
                </tbody>
            </table>
            </div><!-- end inv-table-scroll -->
        </div><!-- end inv-table-wrap -->
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

@media (max-width: 640px) {
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
function closeAllActionMenus() {
    document.querySelectorAll('.action-dropdown.open').forEach(function (dropdown) {
        dropdown.classList.remove('open');
    });
}

function positionActionMenu(dropdown) {
    var btn = dropdown.querySelector('.action-dropdown-btn');
    var menu = dropdown.querySelector('.action-dropdown-menu');
    if (!btn || !menu) return;

    menu.style.visibility = 'hidden';
    menu.style.display = 'block';

    var btnRect = btn.getBoundingClientRect();
    var menuRect = menu.getBoundingClientRect();
    var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
    var viewportHeight = window.innerHeight || document.documentElement.clientHeight;

    var top = btnRect.bottom + 6;
    var left = btnRect.right - menuRect.width;

    if (left < 8) left = 8;
    if (left + menuRect.width > viewportWidth - 8) {
        left = viewportWidth - menuRect.width - 8;
    }
    if (top + menuRect.height > viewportHeight - 8) {
        top = Math.max(8, btnRect.top - menuRect.height - 6);
    }

    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    menu.style.visibility = 'visible';
    menu.style.display = '';
}

function toggleActionMenu(btn, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    var dropdown = btn.closest('.action-dropdown');
    var wasOpen = dropdown.classList.contains('open');
    closeAllActionMenus();
    if (!wasOpen) {
        dropdown.classList.add('open');
        positionActionMenu(dropdown);
    }
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.action-dropdown')) {
        closeAllActionMenus();
    }
});
window.addEventListener('resize', closeAllActionMenus);
window.addEventListener('scroll', closeAllActionMenus, true);
</script>

<script>
/* ====== Inventory Filter ====== */
(function() {
    'use strict';
    var catFilter   = document.getElementById('categoryFilter');
    var stockFilter = document.getElementById('stockFilter');
    var searchInput = document.getElementById('inventorySearch');
    var countEl     = document.getElementById('filterCount');
    var noResults   = document.getElementById('noFilterResults');

    function applyFilters() {
        var cat    = catFilter ? catFilter.value : '';
        var stock  = stockFilter ? stockFilter.value : '';
        var query  = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var rows   = document.querySelectorAll('.data-table tbody tr[data-category]');
        var shown  = 0;
        rows.forEach(function(row) {
            var matchCat = !cat || row.getAttribute('data-category') === cat;
            var rowStock = row.getAttribute('data-stock') || '';
            var matchStock = !stock || rowStock === stock;
            var name = row.getAttribute('data-product-name') || '';
            var sku  = row.getAttribute('data-product-sku') || '';
            var matchQuery = !query || name.indexOf(query) !== -1 || sku.indexOf(query) !== -1;
            if (matchCat && matchStock && matchQuery) {
                row.style.display = '';
                shown++;
            } else {
                row.style.display = 'none';
            }
        });
        if (noResults) noResults.style.display = (rows.length > 0 && shown === 0) ? '' : 'none';
        if (countEl) countEl.textContent = shown + ' of ' + rows.length + ' products';
    }

    if (catFilter) catFilter.addEventListener('change', applyFilters);
    if (stockFilter) stockFilter.addEventListener('change', applyFilters);
    if (searchInput) searchInput.addEventListener('input', applyFilters);
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
            closeAllActionMenus();
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
