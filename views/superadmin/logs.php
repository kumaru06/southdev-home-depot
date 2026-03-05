<?php
$pageTitle = 'System Logs';
$extraCss = ['admin.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
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
        <div class="card" style="margin-bottom: 1.5rem;">
            <form method="GET" action="<?= APP_URL ?>/index.php" class="filter-bar">
                <input type="hidden" name="url" value="admin/logs">
                <select name="action" class="form-control" style="width:180px; height:40px;">
                    <option value="">All Actions</option>
                    <?php if (!empty($actionTypes)): ?>
                        <?php foreach ($actionTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= (isset($_GET['action']) && $_GET['action'] === $type) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $type))) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <input type="date" name="date_from" class="form-control" style="width:150px; height:40px;" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" min="2020-01-01" max="<?= date('Y-m-d') ?>" placeholder="From">
                <input type="date" name="date_to" class="form-control" style="width:150px; height:40px;" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" min="2020-01-01" max="<?= date('Y-m-d') ?>" placeholder="To">
                <input type="text" name="search" class="form-control" style="flex:1; min-width:140px; height:40px;" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Search details...">
                <button type="submit" class="btn btn-accent" style="height:40px; padding:0 16px;">
                    <i data-lucide="search" style="width:16px;height:16px;"></i> Filter
                </button>
                <?php if (!empty($_GET['action']) || !empty($_GET['date_from']) || !empty($_GET['date_to']) || !empty($_GET['search'])): ?>
                    <a href="<?= APP_URL ?>/index.php?url=admin/logs" class="btn btn-outline" style="height:40px; padding:0 16px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:.5rem;">
                    <i data-lucide="scroll-text" style="width:20px;height:20px;color:var(--accent);"></i>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:600;">Activity Logs</h3>
                </div>
                <span class="badge badge-pending" style="font-size:.8rem;"><?= $totalLogs ?? 0 ?> entries</span>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td data-label="ID"><?= $log['id'] ?></td>
                                    <td data-label="Action">
                                        <span class="badge badge-processing" style="font-size:.75rem;"><?= htmlspecialchars($log['action']) ?></span>
                                    </td>
                                    <td data-label="Details" title="<?= htmlspecialchars($log['description'] ?? '') ?>" style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        <?= htmlspecialchars($log['description'] ?? '—') ?>
                                    </td>
                                    <td data-label="User">
                                        <?php if (!empty($log['first_name'])): ?>
                                            <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?>
                                        <?php else: ?>
                                            <span style="color:var(--steel);">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="IP"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                                    <td data-label="Time"><?= date('M d, Y g:i A', strtotime($log['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; padding:2rem;">
                                <i data-lucide="file-x" style="width:40px;height:40px;color:var(--steel);display:block;margin:0 auto .5rem;"></i>
                                <p style="color:var(--steel);">No log entries found.</p>
                            </td></tr>
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
