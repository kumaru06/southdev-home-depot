<?php
$pageTitle = 'System Logs';
$extraCss = ['admin.css', 'logs.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

// Map each action type → [color-class, lucide-icon, label]
$actionMeta = [
    'user_login'           => ['log-badge--login',    'log-in',         'User Login'],
    'user_logout'          => ['log-badge--logout',   'log-out',        'User Logout'],
    'order_created'        => ['log-badge--order',    'shopping-cart',  'Order Created'],
    'order_cancelled'      => ['log-badge--cancel',   'x-circle',       'Order Cancelled'],
    'order_status_updated' => ['log-badge--status',   'refresh-cw',     'Order Status'],
    'stock_restored'       => ['log-badge--stock',    'rotate-ccw',     'Stock Restored'],
    'return_requested'     => ['log-badge--return',   'corner-up-left', 'Return Requested'],
    'return_updated'       => ['log-badge--return',   'corner-up-left', 'Return Updated'],
    'cancel_requested'     => ['log-badge--cancel',   'x-circle',       'Cancel Requested'],
    'cancel_approved'      => ['log-badge--cancel',   'check-circle',   'Cancel Approved'],
    'cancel_rejected'      => ['log-badge--danger',   'slash',          'Cancel Rejected'],
    'product_created'      => ['log-badge--product',  'package-plus',   'Product Created'],
    'product_updated'      => ['log-badge--edit',     'package',        'Product Updated'],
    'product_deleted'      => ['log-badge--danger',   'package-minus',  'Product Deleted'],
    'user_created'         => ['log-badge--user',     'user-plus',      'User Created'],
    'user_updated'         => ['log-badge--edit',     'user-cog',       'User Updated'],
    'category_created'     => ['log-badge--product',  'folder-plus',    'Category Created'],
    'category_deleted'     => ['log-badge--danger',   'folder-minus',   'Category Deleted'],
    'payment_processed'    => ['log-badge--payment',  'credit-card',    'Payment'],
    'payment_failed'       => ['log-badge--danger',   'credit-card',    'Payment Failed'],
    'price_updated'        => ['log-badge--edit',     'tag',            'Price Updated'],
    'stock_added'          => ['log-badge--stock',    'package-plus',   'Stock Added'],
    'stock_movement'       => ['log-badge--status',   'move',           'Stock Movement'],
    'supplier_request'     => ['log-badge--order',    'truck',          'Supplier Request'],
    'damaged_product'      => ['log-badge--damaged',  'alert-triangle', 'Damaged Product'],
];
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>System Logs</h2>
        </div>
    </div>

    <div class="page-content">

        <!-- Filters -->
        <div class="card logs-filter-card">
            <form method="GET" action="<?= APP_URL ?>/index.php" class="logs-filter-bar">
                <input type="hidden" name="url" value="admin/logs">

                <div class="logs-filter-group">
                    <label class="logs-filter-label">Action</label>
                    <select name="action" class="form-control logs-filter-select">
                        <option value="">All Actions</option>
                        <?php if (!empty($actionTypes)): ?>
                            <?php foreach ($actionTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" <?= (isset($_GET['action']) && $_GET['action'] === $type) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($actionMeta[$type][2] ?? ucwords(str_replace('_', ' ', $type))) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="logs-filter-group">
                    <label class="logs-filter-label">From</label>
                    <input type="date" name="date_from" class="form-control logs-filter-date"
                           value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>"
                           min="2020-01-01" max="<?= date('Y-m-d') ?>">
                </div>

                <div class="logs-filter-group">
                    <label class="logs-filter-label">To</label>
                    <input type="date" name="date_to" class="form-control logs-filter-date"
                           value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>"
                           min="2020-01-01" max="<?= date('Y-m-d') ?>">
                </div>

                <div class="logs-filter-group logs-filter-search">
                    <label class="logs-filter-label">Search</label>
                    <div class="logs-search-wrap">
                        <i data-lucide="search" class="logs-search-icon"></i>
                        <input type="text" name="search" class="form-control logs-filter-input"
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                               placeholder="Search details, users...">
                    </div>
                </div>

                <div class="logs-filter-actions">
                    <button type="submit" class="btn btn-accent logs-btn-filter">
                        <i data-lucide="filter" style="width:15px;height:15px;"></i> Filter
                    </button>
                    <?php if (!empty($_GET['action']) || !empty($_GET['date_from']) || !empty($_GET['date_to']) || !empty($_GET['search'])): ?>
                        <a href="<?= APP_URL ?>/index.php?url=admin/logs" class="btn btn-outline logs-btn-clear">
                            <i data-lucide="x" style="width:15px;height:15px;"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <div class="logs-card-header">
                <div class="logs-card-title">
                    <i data-lucide="scroll-text" style="width:18px;height:18px;color:var(--accent);"></i>
                    <span>Activity Logs</span>
                </div>
                <span class="logs-total-badge"><?= number_format($totalLogs ?? 0) ?> entries</span>
            </div>

            <div class="data-table-wrap">
                <table class="data-table logs-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th style="width:200px;">Action</th>
                            <th>Details</th>
                            <th style="width:160px;">User</th>
                            <th style="width:120px;">IP Address</th>
                            <th style="width:160px;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                $meta  = $actionMeta[$log['action']] ?? ['log-badge--default', 'activity', ucwords(str_replace('_', ' ', $log['action']))];
                                $bClass = $meta[0];
                                $icon   = $meta[1];
                                $label  = $meta[2];
                                $initials = '';
                                if (!empty($log['first_name'])) {
                                    $initials = strtoupper(substr($log['first_name'], 0, 1) . substr($log['last_name'] ?? '', 0, 1));
                                }
                                ?>
                                <tr>
                                    <td data-label="ID" class="logs-id-cell">#<?= $log['id'] ?></td>

                                    <td data-label="Action">
                                        <span class="log-badge <?= $bClass ?>">
                                            <i data-lucide="<?= $icon ?>" class="log-badge-icon"></i>
                                            <?= $label ?>
                                        </span>
                                    </td>

                                    <td data-label="Details" class="logs-detail-cell"
                                        title="<?= htmlspecialchars($log['description'] ?? '') ?>">
                                        <?= htmlspecialchars($log['description'] ?? '—') ?>
                                    </td>

                                    <td data-label="User">
                                        <?php if ($initials): ?>
                                            <div class="logs-user-cell">
                                                <span class="logs-avatar"><?= $initials ?></span>
                                                <span class="logs-username"><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="logs-system-label">
                                                <i data-lucide="cpu" style="width:13px;height:13px;"></i> System
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td data-label="IP">
                                        <span class="logs-ip"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></span>
                                    </td>

                                    <td data-label="Timestamp" class="logs-time-cell">
                                        <span class="logs-time-main"><?= date('M d, Y', strtotime($log['created_at'])) ?></span>
                                        <span class="logs-time-sub"><?= date('g:i A', strtotime($log['created_at'])) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="logs-empty-state">
                                    <i data-lucide="file-x" class="logs-empty-icon"></i>
                                    <p>No log entries found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination" style="margin-top:1.5rem;">
                    <?php
                    $queryParams = $_GET;
                    unset($queryParams['url']);
                    ?>
                    <?php if ($currentPage > 1): ?>
                        <?php $queryParams['page'] = $currentPage - 1; ?>
                        <a href="<?= APP_URL ?>/index.php?url=admin/logs&<?= http_build_query($queryParams) ?>" class="page-btn">
                            <i data-lucide="chevron-left" style="width:16px;height:16px;"></i> Prev
                        </a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <?php $queryParams['page'] = $i; ?>
                        <a href="<?= APP_URL ?>/index.php?url=admin/logs&<?= http_build_query($queryParams) ?>"
                           class="page-btn <?= $i === $currentPage ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <?php $queryParams['page'] = $currentPage + 1; ?>
                        <a href="<?= APP_URL ?>/index.php?url=admin/logs&<?= http_build_query($queryParams) ?>" class="page-btn">
                            Next <i data-lucide="chevron-right" style="width:16px;height:16px;"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
