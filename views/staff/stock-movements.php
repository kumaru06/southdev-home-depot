<?php
/* $pageTitle, $extraCss, $isAdmin set by InventoryController::movements() */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

// Movement type config: icon, colour
$typeConfig = [
    'purchase'   => ['icon' => 'download',       'color' => 'var(--success)',  'bg' => 'rgba(22,163,74,.1)'],
    'sale'       => ['icon' => 'shopping-cart',   'color' => 'var(--info)',     'bg' => 'rgba(59,130,246,.1)'],
    'return'     => ['icon' => 'rotate-ccw',      'color' => 'var(--warning)',  'bg' => 'rgba(245,158,11,.1)'],
    'adjustment' => ['icon' => 'sliders',         'color' => 'var(--charcoal)', 'bg' => 'rgba(27,42,74,.08)'],
    'initial'    => ['icon' => 'database',        'color' => '#7C3AED',         'bg' => 'rgba(124,58,237,.1)'],
];
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

        <!-- ─── Summary Cards ────────────────────────────────── -->
        <?php if (!empty($summary)): ?>
        <div class="sm-summary">
            <?php foreach ($summary as $i => $s):
                $type = strtolower($s['type']);
                $cfg  = $typeConfig[$type] ?? $typeConfig['adjustment'];
                $totalIn  = (int)($s['total_in'] ?? 0);
                $totalOut = (int)($s['total_out'] ?? 0);
            ?>
            <div class="sm-summary-card">
                <div class="sm-summary-icon" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>">
                    <i data-lucide="<?= $cfg['icon'] ?>"></i>
                </div>
                <div class="sm-summary-info">
                    <span class="sm-summary-label"><?= ucfirst($type) ?></span>
                    <span class="sm-summary-value"><?= $s['movement_count'] ?></span>
                    <span class="sm-summary-sub">
                        <?php if ($totalIn > 0 && $totalOut > 0): ?>
                            <span style="color:var(--success)">+<?= $totalIn ?></span> / <span style="color:var(--danger)">-<?= $totalOut ?></span> units
                        <?php elseif ($totalIn > 0): ?>
                            <span style="color:var(--success)">+<?= $totalIn ?> units</span>
                        <?php elseif ($totalOut > 0): ?>
                            <span style="color:var(--danger)">-<?= $totalOut ?> units</span>
                        <?php else: ?>
                            0 units
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ─── Filters ─────────────────────────────────────── -->
        <div class="sm-filters">
            <form method="GET" action="<?= APP_URL ?>/index.php" class="sm-filter-form">
                <input type="hidden" name="url" value="staff/inventory/movements">

                <div class="sm-filter-group">
                    <label><i data-lucide="filter" style="width:13px;height:13px"></i> Type</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="">All Types</option>
                        <?php foreach (['purchase','sale','return','adjustment','initial'] as $t): ?>
                            <option value="<?= $t ?>" <?= (($_GET['type'] ?? '') === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm-filter-group sm-filter-wide">
                    <label><i data-lucide="package" style="width:13px;height:13px"></i> Product</label>
                    <select name="product_id" class="form-control form-control-sm">
                        <option value="">All Products</option>
                        <?php if (!empty($products)): foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (($_GET['product_id'] ?? '') == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="sm-filter-group">
                    <label><i data-lucide="calendar" style="width:13px;height:13px"></i> From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                </div>

                <div class="sm-filter-group">
                    <label><i data-lucide="calendar" style="width:13px;height:13px"></i> To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                </div>

                <div class="sm-filter-actions">
                    <button type="submit" class="btn btn-accent btn-sm"><i data-lucide="search" style="width:14px;height:14px"></i> Filter</button>
                    <a href="<?= APP_URL ?>/index.php?url=staff/inventory/movements" class="btn btn-outline btn-sm">Reset</a>
                </div>
            </form>
        </div>

        <!-- ─── Movements Table ──────────────────────────────── -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h3><i data-lucide="activity"></i> Movement History</h3>
                <span style="font-size:12px;color:var(--text-muted);"><?= $totalMovements ?? 0 ?> total records</span>
            </div>
            <div class="dash-card-body">
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Notes</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($movements)): ?>
                                <?php foreach ($movements as $m):
                                    $mType = strtolower($m['type'] ?? 'adjustment');
                                    $mCfg  = $typeConfig[$mType] ?? $typeConfig['adjustment'];
                                ?>
                                <tr>
                                    <td>
                                        <span class="sm-date-main"><?= date('M d, Y', strtotime($m['created_at'])) ?></span>
                                        <span class="sm-date-time"><?= date('h:i A', strtotime($m['created_at'])) ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight:600;color:var(--charcoal);"><?= htmlspecialchars($m['product_name'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <span class="sm-type-badge" style="background:<?= $mCfg['bg'] ?>;color:<?= $mCfg['color'] ?>">
                                            <i data-lucide="<?= $mCfg['icon'] ?>" style="width:12px;height:12px"></i>
                                            <?= ucfirst($mType) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($m['quantity'] > 0): ?>
                                            <span class="sm-qty sm-qty-plus">+<?= $m['quantity'] ?></span>
                                        <?php elseif ($m['quantity'] < 0): ?>
                                            <span class="sm-qty sm-qty-minus"><?= $m['quantity'] ?></span>
                                        <?php else: ?>
                                            <span class="sm-qty">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="sm-notes"><?= htmlspecialchars($m['notes'] ?? '—') ?></span>
                                    </td>
                                    <td>
                                        <span class="sm-user"><?= htmlspecialchars(trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? ''))) ?: '—' ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="dash-empty">
                                            <i data-lucide="inbox"></i>
                                            <p>No stock movements found.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ─── Pagination ───────────────────────────────────── -->
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

<style>
/* ─── Stock Movements Summary Cards ─── */
.sm-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
    animation: dashFadeUp .45s ease both;
}
.sm-summary-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: box-shadow .3s ease, transform .3s ease, border-color .3s;
}
.sm-summary-card:hover {
    box-shadow: 0 8px 28px rgba(0,0,0,.07);
    transform: translateY(-3px);
    border-color: transparent;
}
.sm-summary-icon {
    width: 46px;
    height: 46px;
    min-width: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sm-summary-icon i, .sm-summary-icon svg {
    width: 20px;
    height: 20px;
}
.sm-summary-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.sm-summary-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--text-muted);
    margin-bottom: 2px;
}
.sm-summary-value {
    font-size: 24px;
    font-weight: 800;
    color: var(--charcoal);
    line-height: 1.1;
    letter-spacing: -.5px;
}
.sm-summary-sub {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
    margin-top: 2px;
}

/* ─── Filters ─── */
.sm-filters {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 22px;
    margin-bottom: 20px;
    animation: dashFadeUp .5s ease both;
    animation-delay: .06s;
}
.sm-filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: flex-end;
}
.sm-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
    min-width: 140px;
}
.sm-filter-group.sm-filter-wide {
    flex: 2;
    min-width: 200px;
}
.sm-filter-group label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
}
.sm-filter-group label i, .sm-filter-group label svg {
    color: var(--steel);
}
.sm-filter-actions {
    display: flex;
    gap: 8px;
    align-items: flex-end;
    padding-bottom: 1px;
}

/* ─── Type Badge ─── */
.sm-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

/* ─── Quantity ─── */
.sm-qty {
    display: inline-flex;
    align-items: center;
    font-size: 13px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 8px;
}
.sm-qty-plus {
    background: rgba(22,163,74,.08);
    color: var(--success);
}
.sm-qty-minus {
    background: rgba(239,68,68,.08);
    color: var(--danger);
}

/* ─── Date Cell ─── */
.sm-date-main {
    display: block;
    font-weight: 600;
    font-size: 13px;
    color: var(--charcoal);
}
.sm-date-time {
    display: block;
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 1px;
}

/* ─── Notes & User ─── */
.sm-notes {
    display: block;
    max-width: 220px;
    white-space: normal;
    line-height: 1.4;
    font-size: 12.5px;
    color: var(--text-secondary, var(--steel));
}
.sm-user {
    font-weight: 500;
    color: var(--charcoal);
    font-size: 13px;
}

/* ─── Table in card ─── */
.dash-card .data-table-wrap {
    overflow-x: auto;
}
.dash-card .data-table thead th {
    background: var(--neutral);
}

/* ─── Responsive ─── */
@media (max-width: 768px) {
    .sm-summary { grid-template-columns: 1fr 1fr; }
    .sm-filter-form { flex-direction: column; }
    .sm-filter-group { min-width: 100%; }
    .sm-filter-group.sm-filter-wide { min-width: 100%; }
}
@media (max-width: 480px) {
    .sm-summary { grid-template-columns: 1fr; }
}
</style>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
