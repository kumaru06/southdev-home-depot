<?php
/* $pageTitle, $extraCss, $isAdmin set by InventoryController::priceHistory() */
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
        <!-- Filter by Product -->
        <div class="card" style="margin-bottom:1.5rem;padding:1rem;">
            <form method="GET" action="<?= APP_URL ?>/index.php" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
                <input type="hidden" name="url" value="staff/inventory/price-history">
                <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px;">
                    <label class="form-label" style="font-size:.8rem;">Filter by Product</label>
                    <select name="product_id" class="form-control form-control-sm">
                        <option value="">All Products</option>
                        <?php if (!empty($products)): foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (($_GET['product_id'] ?? '') == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-accent btn-sm">Filter</button>
                <a href="<?= APP_URL ?>/index.php?url=staff/inventory/price-history" class="btn btn-outline btn-sm">Reset</a>
            </form>
        </div>

        <!-- Price History Table -->
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Old Price</th>
                        <th>New Price</th>
                        <th>Change</th>
                        <th>Reason</th>
                        <th>Changed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $h): ?>
                            <?php
                                $diff = floatval($h['new_price']) - floatval($h['old_price']);
                                $pctChange = floatval($h['old_price']) > 0 ? ($diff / floatval($h['old_price'])) * 100 : 0;
                            ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($h['created_at'])) ?></td>
                                <td><?= htmlspecialchars($h['product_name'] ?? 'N/A') ?></td>
                                <td>₱<?= number_format($h['old_price'], 2) ?></td>
                                <td>₱<?= number_format($h['new_price'], 2) ?></td>
                                <td>
                                    <?php if ($diff > 0): ?>
                                        <span style="color:var(--danger);font-weight:600;">
                                            <i data-lucide="trending-up" style="width:13px;height:13px"></i>
                                            +₱<?= number_format($diff, 2) ?> (<?= number_format($pctChange, 1) ?>%)
                                        </span>
                                    <?php elseif ($diff < 0): ?>
                                        <span style="color:var(--success);font-weight:600;">
                                            <i data-lucide="trending-down" style="width:13px;height:13px"></i>
                                            -₱<?= number_format(abs($diff), 2) ?> (<?= number_format(abs($pctChange), 1) ?>%)
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">No change</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width:200px;white-space:normal;"><?= htmlspecialchars($h['reason'] ?? '') ?></td>
                                <td><?= htmlspecialchars(($h['first_name'] ?? '') . ' ' . ($h['last_name'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No price history records found.</td></tr>
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
