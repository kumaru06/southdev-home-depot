<?php
$pageTitle = 'Manage Products';
$extraCss = ['admin.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>Manage Products</h2>
        </div>
    </div>

    <div class="page-content">

        <!-- Add Product Card -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <i data-lucide="package-plus" style="width:20px;height:20px;color:var(--accent);"></i>
                <h3 style="margin:0; font-size:1.05rem; font-weight:600;">Add New Product</h3>
            </div>
            <form action="<?= APP_URL ?>/index.php?url=admin/products/create" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-col" style="flex:2;">
                        <div class="form-group">
                            <label class="form-label">Product Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Category <span class="required">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Price (₱) <span class="required">*</span></label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" placeholder="e.g. HW-001">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" name="quantity" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Product description..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-accent">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Add Product
                    </button>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:.5rem;">
                    <i data-lucide="package" style="width:20px;height:20px;color:var(--accent);"></i>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:600;">All Products</h3>
                </div>
                <span class="badge badge-pending" style="font-size:.8rem;"><?= count($products ?? []) ?> items</span>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td data-label="ID"><?= $product['id'] ?></td>
                                    <td data-label="Image">
                                        <div style="width:48px;height:48px;border-radius:6px;overflow:hidden;border:1px solid var(--neutral);background:var(--neutral);">
                                            <img src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>"
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 style="width:100%;height:100%;object-fit:cover;">
                                        </div>
                                    </td>
                                    <td data-label="Name">
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        <?php if (!empty($product['sku'])): ?>
                                            <br><small style="color:var(--steel);">SKU: <?= htmlspecialchars($product['sku']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Category"><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                                    <td data-label="Price"><strong>₱<?= number_format($product['price'], 2) ?></strong></td>
                                    <td data-label="Stock">
                                        <?php $stock = $product['stock'] ?? 0; ?>
                                        <span class="badge <?= $stock <= 10 ? 'badge-cancelled' : 'badge-delivered' ?>">
                                            <?= $stock ?>
                                        </span>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-btn-group">
                                            <form action="<?= APP_URL ?>/index.php?url=admin/products/<?= $product['id'] ?>/delete"
                                                  method="POST" style="display:inline;"
                                                  class="inline-form">
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
                            <tr><td colspan="7" style="text-align:center; padding:2rem;">
                                <i data-lucide="package-x" style="width:40px;height:40px;color:var(--steel);margin-bottom:.5rem;display:block;margin:0 auto .5rem;"></i>
                                <p style="color:var(--steel);">No products found.</p>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
