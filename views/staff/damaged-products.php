<?php
/* $pageTitle, $extraCss, $isAdmin, $damaged, $summary set by InventoryController::damagedProducts() */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

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
            <h2><i data-lucide="alert-octagon" style="width:22px;height:22px;color:var(--danger);"></i> <?= $pageTitle ?></h2>
        </div>
        <div class="top-bar-right">
            <a href="<?= $invBase ?>" class="btn btn-outline btn-sm">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i> Back to Inventory
            </a>
        </div>
    </div>

    <div class="page-content">

        <!-- Summary Cards -->
        <div class="stat-cards" style="margin-bottom: 24px;">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Damaged</span>
                    <span class="stat-value"><?= $summary['total'] ?? 0 ?></span>
                </div>
                <div class="stat-icon" style="background:var(--danger-bg);color:var(--danger);"><i data-lucide="alert-octagon"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Received</span>
                    <span class="stat-value"><?= $summary['received'] ?? 0 ?></span>
                </div>
                <div class="stat-icon" style="background:#DBEAFE;color:#1E40AF;"><i data-lucide="package-check"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Written Off</span>
                    <span class="stat-value"><?= $summary['written_off'] ?? 0 ?></span>
                </div>
                <div class="stat-icon" style="background:var(--danger-bg);color:var(--danger);"><i data-lucide="trash-2"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Repaired</span>
                    <span class="stat-value"><?= $summary['repaired'] ?? 0 ?></span>
                </div>
                <div class="stat-icon" style="background:#D4EDDA;color:#155724;"><i data-lucide="check-circle"></i></div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card filter-bar">
            <form method="GET" class="filter-form">
                <input type="hidden" name="url" value="<?= htmlspecialchars($_GET['url'] ?? '') ?>">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <?php foreach (['received', 'inspected', 'written_off', 'repaired'] as $s): ?>
                        <option value="<?= $s ?>" <?= (isset($_GET['status']) && $_GET['status'] == $s) ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-accent"><i data-lucide="filter"></i> Filter</button>
            </form>
        </div>

        <!-- Data Table -->
        <div class="data-table-wrap">
            <table class="data-table" style="table-layout:auto;">
                <thead>
                    <tr>
                        <th style="width:50px;">ID</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th style="width:50px;text-align:center;">Qty</th>
                        <th>Order</th>
                        <th style="min-width:260px;">Return Reason</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:110px;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($damaged)): ?>
                        <?php foreach ($damaged as $item): ?>
                            <?php
                                $statusClass = match($item['status']) {
                                    'received'    => 'badge-pending',
                                    'inspected'   => 'badge-processing',
                                    'written_off' => 'badge-cancelled',
                                    'repaired'    => 'badge-delivered',
                                    default       => 'badge-pending'
                                };
                            ?>
                            <tr>
                                <td><strong>#<?= $item['id'] ?></strong></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($item['image']) ?>"
                                                 alt="" style="width:40px;height:40px;border-radius:8px;object-fit:cover;border:1px solid var(--border);">
                                        <?php else: ?>
                                            <div style="width:40px;height:40px;border-radius:8px;background:var(--light);display:flex;align-items:center;justify-content:center;">
                                                <i data-lucide="package" style="width:18px;height:18px;color:var(--steel);"></i>
                                            </div>
                                        <?php endif; ?>
                                        <strong style="font-weight:600;"><?= htmlspecialchars($item['product_name']) ?></strong>
                                    </div>
                                </td>
                                <td><code style="background:var(--light);padding:3px 8px;border-radius:4px;font-size:.8rem;"><?= htmlspecialchars($item['sku'] ?? 'N/A') ?></code></td>
                                <td style="text-align:center;"><span style="background:var(--danger-bg, #FEE2E2);color:var(--danger);padding:3px 10px;border-radius:20px;font-weight:700;font-size:.82rem;"><?= $item['quantity'] ?></span></td>
                                <td>
                                    <a href="<?= APP_URL ?>/index.php?url=<?= $_SESSION['role_id'] == ROLE_SUPER_ADMIN ? 'admin' : 'staff' ?>/orders/<?= $item['order_id'] ?>"
                                       style="color:var(--accent);text-decoration:none;font-weight:600;font-size:.85rem;">
                                        <?= htmlspecialchars($item['order_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div style="font-size:.85rem;line-height:1.45;color:var(--text-primary);">
                                        <?= htmlspecialchars($item['return_reason']) ?>
                                    </div>
                                    <?php if (!empty($item['admin_notes'])): ?>
                                        <div style="font-size:.78rem;color:var(--steel);margin-top:4px;">
                                            <i data-lucide="message-circle" style="width:11px;height:11px;vertical-align:-1px;"></i>
                                            <?= htmlspecialchars($item['admin_notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $item['status'])) ?></span></td>
                                <td style="font-size:.84rem;color:var(--text-secondary);"><?= date('M d, Y', strtotime($item['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding:40px 0;">
                                <i data-lucide="check-circle" style="width:40px;height:40px;color:var(--success);opacity:.5;display:block;margin:0 auto 12px;"></i>
                                <strong>No damaged products found</strong>
                                <p class="text-muted" style="margin-top:4px;">Damaged items from approved return requests will appear here.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Damaged products table enhancements */
.data-table td { vertical-align: middle; }
.data-table td div[style*="line-height"] { white-space: normal; word-break: break-word; }
</style>

<script>
// Re-init Lucide icons for dynamically rendered content
if (typeof lucide !== 'undefined') { lucide.createIcons(); }
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
