<?php
$pageTitle = 'Manage Categories';
$extraCss = ['admin.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>Manage Categories</h2>
        </div>
    </div>

    <div class="page-content">

        <!-- Add Category Card -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <i data-lucide="folder-plus" style="width:20px;height:20px;color:var(--accent);"></i>
                <h3 style="margin:0; font-size:1.05rem; font-weight:600;">Add New Category</h3>
            </div>
            <form action="<?= APP_URL ?>/index.php?url=admin/categories/create" method="POST">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Category Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Power Tools">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Brief description...">
                        </div>
                    </div>
                    <!-- image upload removed for categories per UI decision -->
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-accent">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Add Category
                    </button>
                </div>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="card">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:.5rem;">
                    <i data-lucide="layers" style="width:20px;height:20px;color:var(--accent);"></i>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:600;">All Categories</h3>
                </div>
                <span class="badge badge-pending" style="font-size:.8rem;"><?= count($categories ?? []) ?> total</span>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td data-label="ID"><?= $cat['id'] ?></td>
                                    <td data-label="Name">
                                        <div style="display:flex; align-items:center; gap:.5rem;">
                                            <i data-lucide="folder" style="width:16px;height:16px;color:var(--accent);"></i>
                                            <strong><?= htmlspecialchars($cat['name']) ?></strong>
                                        </div>
                                    </td>
                                    <td data-label="Description"><?= htmlspecialchars($cat['description'] ?? 'N/A') ?></td>
                                    <td data-label="Products">
                                        <span class="badge badge-delivered"><?= $cat['product_count'] ?? 0 ?></span>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-btn-group">
                                            <form action="<?= APP_URL ?>/index.php?url=admin/categories/<?= $cat['id'] ?>/delete"
                                                  method="POST" class="inline-form">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="action-btn delete" title="Delete">
                                                    <i data-lucide="trash-2" style="width:15px;height:15px;"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:2rem;">
                                <i data-lucide="folder-x" style="width:40px;height:40px;color:var(--steel);display:block;margin:0 auto .5rem;"></i>
                                <p style="color:var(--steel);">No categories found.</p>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
