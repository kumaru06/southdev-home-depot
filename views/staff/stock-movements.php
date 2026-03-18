<?php
/* $pageTitle, $extraCss, $isAdmin set by InventoryController::movements() */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
        <div class="top-bar-right">
            <a href="<?= APP_URL ?>/index.php?url=staff/inventory" class="btn btn-outline btn-sm">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i> Back to Inventory
            </a>
        </div>
    </div>

    <div class="page-content">
        <!-- Summary Cards -->
        <?php if (!empty($summary)): ?>
        <div class="stat-cards" style="margin-bottom:1.5rem;">
            <?php foreach ($summary as $s): ?>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label"><?= ucfirst($s['type']) ?></span>
                    <span class="stat-value"><?= $s['movement_count'] ?> moves</span>
                </div>
                <div class="stat-icon">
                    <span style="font-size:.85rem;color:var(--text-secondary);">
                        <?php $totalQty = isset($s['total_quantity']) ? (int)$s['total_quantity'] : 0; ?>
                        Total: <?= $totalQty > 0 ? '+' : '' ?><?= $totalQty ?> units
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card" style="margin-bottom:1.5rem;padding:1rem;">
            <form method="GET" action="<?= APP_URL ?>/index.php" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
                <input type="hidden" name="url" value="staff/inventory/movements">
                <div class="form-group" style="margin-bottom:0;flex:1;min-width:140px;">
                    <label class="form-label" style="font-size:.8rem;">Type</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="">All Types</option>
                        <?php foreach (['purchase','sale','return','adjustment','initial'] as $t): ?>
                            <option value="<?= $t ?>" <?= (($_GET['type'] ?? '') === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;flex:1;min-width:140px;">
                    <label class="form-label" style="font-size:.8rem;">Product</label>
                    <select name="product_id" class="form-control form-control-sm">
                        <option value="">All Products</option>
                        <?php if (!empty($products)): foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (($_GET['product_id'] ?? '') == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;min-width:130px;">
                    <label class="form-label" style="font-size:.8rem;">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;min-width:130px;">
                    <label class="form-label" style="font-size:.8rem;">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-accent btn-sm">Filter</button>
                <a href="<?= APP_URL ?>/index.php?url=staff/inventory/movements" class="btn btn-outline btn-sm">Reset</a>
            </form>
        </div>

        <!-- Movements Table -->
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movements)): ?>
                        <?php foreach ($movements as $m): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($m['created_at'])) ?></td>
                                <td><?= htmlspecialchars($m['product_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
                                    $typeBadge = [
                                        'purchase' => 'badge-delivered',
                                        'sale' => 'badge-processing',
                                        'return' => 'badge-pending',
                                        'adjustment' => 'badge-cancelled',
                                        'initial' => 'badge-delivered',
                                    ];
                                    $badge = $typeBadge[$m['type']] ?? 'badge-pending';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= ucfirst($m['type']) ?></span>
                                </td>
                                <td>
                                    <?php if ($m['quantity'] > 0): ?>
                                        <span style="color:var(--success);font-weight:600;">+<?= $m['quantity'] ?></span>
                                    <?php else: ?>
                                        <span style="color:var(--danger);font-weight:600;"><?= $m['quantity'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width:200px;white-space:normal;"><?= htmlspecialchars($m['notes'] ?? '') ?></td>
                                <td><?= htmlspecialchars(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No stock movements found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="pagination" style="margin-top:1rem;">
            <?php
                $params = $_GET;
                unset($params['page']);
                $qs = http_build_query($params);
            ?>
            <?php if ($page > 1): ?>
                <a href="<?= APP_URL ?>/index.php?<?= $qs ?>&page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= APP_URL ?>/index.php?<?= $qs ?>&page=<?= $i ?>" class="btn <?= $page == $i ? 'btn-accent' : 'btn-outline' ?> btn-sm"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="<?= APP_URL ?>/index.php?<?= $qs ?>&page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
