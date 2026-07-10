<?php
/* $pageTitle, $isAdmin, $extraCss provided by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Customer Reviews' ?></h2>
        </div>
        <div class="top-bar-right">
            <!-- optional actions -->
        </div>
    </div>

    <div class="page-content page-content--table-locked">
        <div class="card card--table-locked" style="margin-bottom:0;">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:.5rem;">
                    <i data-lucide="message-square" style="width:20px;height:20px;color:var(--accent);"></i>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:600;"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Customer Reviews' ?></h3>
                </div>
                <span class="badge badge-pending" style="font-size:.8rem;"><?= count($reviews ?? []) ?> total</span>
            </div>

            <div class="data-table-wrap data-table-wrap--locked">
                <?php if (empty($reviews)): ?>
                    <table class="data-table">
                        <tbody>
                            <tr>
                                <td class="text-center" style="padding:2.5rem 1rem;">
                                    <p class="text-muted" style="margin:0;">No reviews yet.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Reviewer</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td><?= intval($r['id']) ?></td>
                                    <td><?= htmlspecialchars($r['product_name'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars(mb_substr($r['first_name'] ?? '',0,1) . '. ' . mb_substr($r['last_name'] ?? '',0,1) . '.*') ?></td>
                                    <td><?= intval($r['rating']) ?>/5</td>
                                    <td title="<?= htmlspecialchars($r['comment'] ?? '') ?>"><?= htmlspecialchars(mb_strlen($r['comment'] ?? '') > 80 ? mb_substr($r['comment'], 0, 80) . '…' : ($r['comment'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                                    <td>
                                        <form action="<?= APP_URL ?>/index.php?url=staff/reviews/delete/<?= intval($r['id']) ?>" method="POST" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
