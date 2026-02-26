<?php
/* $pageTitle, $extraCss, $isAdmin set by ReturnController::manage() */
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

    <div class="page-content">
        <!-- Filter Bar -->
        <div class="card filter-bar">
            <form method="GET" class="filter-form">
                <input type="hidden" name="url" value="staff/returns">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending','approved','rejected','completed'] as $s): ?>
                        <option value="<?= $s ?>" <?= (isset($_GET['status']) && $_GET['status'] == $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-accent"><i data-lucide="filter"></i> Filter</button>
            </form>
        </div>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($returns)): ?>
                        <?php foreach ($returns as $return): ?>
                            <tr>
                                <td>#<?= $return['id'] ?></td>
                                <td><strong><?= htmlspecialchars($return['order_number']) ?></strong></td>
                                <td><?= htmlspecialchars($return['first_name'] . ' ' . $return['last_name']) ?></td>
                                <td title="<?= htmlspecialchars($return['reason']) ?>"><?= htmlspecialchars(substr($return['reason'], 0, 50)) ?><?= strlen($return['reason']) > 50 ? '…' : '' ?></td>
                                <td><span class="badge badge-<?= $return['status'] ?>"><?= ucfirst($return['status']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($return['created_at'])) ?></td>
                                <td>
                                    <?php if ($return['status'] == 'pending'): ?>
                                        <form action="<?= APP_URL ?>/index.php?url=staff/returns/<?= $return['id'] ?>/update" method="POST" class="inline-form">
                                            <?= csrf_field() ?>
                                            <input type="text" name="admin_notes" placeholder="Notes…" class="form-control form-control-sm" style="width: 120px;">
                                            <div class="action-btn-group">
                                                <button type="submit" name="status" value="approved" class="action-btn approve"><i data-lucide="check"></i></button>
                                                <button type="submit" name="status" value="rejected" class="action-btn delete"><i data-lucide="x"></i></button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted"><?= ucfirst($return['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No return requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
