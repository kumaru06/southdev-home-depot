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
        <div class="card card--table-locked">
            <div class="products-table-toolbar">
                <div class="products-table-toolbar__title">
                    <i data-lucide="layers" style="width:20px;height:20px;color:var(--accent);"></i>
                    <h3>All Categories</h3>
                </div>
                <div class="products-table-toolbar__actions">
                    <form id="bulkDeleteCategoriesForm" class="products-bulk-form" action="<?= APP_URL ?>/index.php?url=admin/categories/bulk-delete" method="POST">
                        <?= csrf_field() ?>
                        <div id="bulkDeleteCategoryIds"></div>
                        <button type="submit" id="bulkDeleteCategoriesBtn" class="btn btn-outline btn-sm products-bulk-delete"
                                data-confirm="Delete the selected categories? This cannot be undone."
                                data-confirm-title="Delete Selected Categories"
                                data-confirm-ok="Delete Selected"
                                data-confirm-variant="danger"
                                hidden>
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            Delete Selected (<span id="bulkSelectedCategoriesCount">0</span>)
                        </button>
                    </form>
                    <span class="badge badge-pending products-count-badge"><?= count($categories ?? []) ?> total</span>
                </div>
            </div>
            <div class="data-table-wrap data-table-wrap--locked">
                <table class="data-table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th style="width:42px;">
                                <input type="checkbox" id="selectAllCategories" class="product-select-all" title="Select all" aria-label="Select all categories">
                            </th>
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
                                <?php
                                    $productCount = (int) ($cat['product_count'] ?? 0);
                                    $canDelete = $productCount === 0;
                                ?>
                                <tr data-category-id="<?= (int) $cat['id'] ?>" data-product-count="<?= $productCount ?>">
                                    <td data-label="Select">
                                        <input type="checkbox"
                                               class="category-row-check product-row-check"
                                               value="<?= (int) $cat['id'] ?>"
                                               aria-label="Select category #<?= (int) $cat['id'] ?>"
                                               <?= $canDelete ? '' : 'disabled title="Cannot select: category still has products"' ?>>
                                    </td>
                                    <td data-label="ID"><?= $cat['id'] ?></td>
                                    <td data-label="Name">
                                        <div style="display:flex; align-items:center; gap:.5rem;">
                                            <i data-lucide="folder" style="width:16px;height:16px;color:var(--accent);"></i>
                                            <strong><?= htmlspecialchars($cat['name']) ?></strong>
                                        </div>
                                    </td>
                                    <td data-label="Description"><?= htmlspecialchars($cat['description'] ?? 'N/A') ?></td>
                                    <td data-label="Products">
                                        <span class="badge <?= $productCount > 0 ? 'badge-pending' : 'badge-delivered' ?>"><?= $productCount ?></span>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-btn-group">
                                            <?php if ($canDelete): ?>
                                                <form action="<?= APP_URL ?>/index.php?url=admin/categories/<?= $cat['id'] ?>/delete"
                                                      method="POST" class="inline-form">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="action-btn delete" title="Delete"
                                                            data-confirm="Delete this category?"
                                                            data-confirm-title="Delete Category"
                                                            data-confirm-ok="Delete"
                                                            data-confirm-variant="danger">
                                                        <i data-lucide="trash-2" style="width:15px;height:15px;"></i> Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="action-btn delete" disabled
                                                        title="Cannot delete while this category still has <?= $productCount ?> product(s)">
                                                    <i data-lucide="trash-2" style="width:15px;height:15px;"></i> In Use
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; padding:2rem;">
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

<script>
document.addEventListener('DOMContentLoaded', function(){
    var selectAll = document.getElementById('selectAllCategories');
    var bulkBtn = document.getElementById('bulkDeleteCategoriesBtn');
    var bulkCount = document.getElementById('bulkSelectedCategoriesCount');
    var bulkForm = document.getElementById('bulkDeleteCategoriesForm');
    var bulkIds = document.getElementById('bulkDeleteCategoryIds');

    function rowChecks() {
        return Array.prototype.slice.call(document.querySelectorAll('#categoriesTable .category-row-check:not(:disabled)'));
    }

    function syncCategorySelection() {
        var checks = rowChecks();
        var selected = checks.filter(function(c){ return c.checked; });
        var count = selected.length;

        if (bulkCount) bulkCount.textContent = String(count);
        if (bulkBtn) {
            if (count > 0) bulkBtn.removeAttribute('hidden');
            else bulkBtn.setAttribute('hidden', '');
        }

        if (selectAll) {
            selectAll.checked = checks.length > 0 && selected.length === checks.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < checks.length;
            selectAll.disabled = checks.length === 0;
        }

        if (bulkIds) {
            bulkIds.innerHTML = selected.map(function(c){
                return '<input type="hidden" name="category_ids[]" value="' + c.value + '">';
            }).join('');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function(){
            rowChecks().forEach(function(c){ c.checked = selectAll.checked; });
            syncCategorySelection();
        });
    }

    rowChecks().forEach(function(c){
        c.addEventListener('change', syncCategorySelection);
    });

    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e){
            syncCategorySelection();
            if (!document.querySelectorAll('#bulkDeleteCategoryIds input[name="category_ids[]"]').length) {
                e.preventDefault();
            }
        });
    }

    syncCategorySelection();
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
