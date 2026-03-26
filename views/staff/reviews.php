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

    <div class="page-content">
        <div class="container">
            <h2 style="margin:12px 0"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Customer Reviews' ?></h2>

            <div style="margin-top:12px">
        <?php if (empty($reviews)): ?>
            <p class="text-muted">No reviews yet.</p>
        <?php else: ?>
            <table class="table">
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
                            <td><?= nl2br(htmlspecialchars($r['comment'] ?? '')) ?></td>
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
