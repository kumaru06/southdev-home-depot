<?php
/* $pageTitle, $extraCss, $isAdmin set by OrderController::cancelRequests() */
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
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cancelRequests)): ?>
                        <?php foreach ($cancelRequests as $req): ?>
                            <tr>
                                <td>#<?= $req['id'] ?></td>
                                <td><strong><?= htmlspecialchars($req['order_number'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? '')) ?></td>
                                <td title="<?= htmlspecialchars($req['reason']) ?>"><?= htmlspecialchars(substr($req['reason'], 0, 60)) ?><?= strlen($req['reason']) > 60 ? '…' : '' ?></td>
                                <td><span class="badge badge-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                <td>
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <div class="action-btn-group">
                                            <form action="<?= APP_URL ?>/index.php?url=staff/cancel-requests/<?= $req['id'] ?>/approve" method="POST" class="inline-form">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="admin_notes" value="Approved by staff">
                                                <button type="submit" class="action-btn approve" title="Approve"><i data-lucide="check"></i></button>
                                            </form>
                                            <form action="<?= APP_URL ?>/index.php?url=staff/cancel-requests/<?= $req['id'] ?>/reject" method="POST" class="inline-form">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="admin_notes" value="Rejected by staff">
                                                <button type="submit" class="action-btn delete" title="Reject"><i data-lucide="x"></i></button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted"><?= ucfirst($req['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No cancel requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
