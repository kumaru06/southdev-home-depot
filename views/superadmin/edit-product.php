<?php
// Edit Product View
?>
<?php require_once INCLUDES_PATH . '/header.php'; ?>
<?php require_once INCLUDES_PATH . '/sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>Edit Product</h2>
        </div>
    </div>

    <div class="page-content">
        <div class="card">
            <form action="<?= APP_URL ?>/index.php?url=admin/products/<?= $product['id'] ?>/update" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">

                <div class="form-row">
                    <div class="form-col" style="flex:2;">
                        <div class="form-group">
                            <label class="form-label">Product Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Category <span class="required">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == ($product['category_id'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Price (₱) <span class="required">*</span></label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required value="<?= htmlspecialchars($product['price']) ?>">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($product['stock'] ?? 0) ?>" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Product Image</label>
                            <div style="display:flex; gap:.75rem; align-items:center;">
                                <div style="width:72px;height:72px;border:1px solid var(--neutral);border-radius:6px;overflow:hidden;background:var(--neutral);">
                                    <img src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                </div>
                                <label for="edit_product_image" style="display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; border:1.5px solid var(--border); border-radius:var(--radius-sm); background:var(--white); cursor:pointer; font-size:.875rem; color:var(--text-primary); transition:border-color .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                                    <i data-lucide="upload" style="width:15px;height:15px;"></i> Choose Image
                                </label>
                                <input type="file" id="edit_product_image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;" onchange="(function(e){var f=e.files[0];if(!f)return;var r=new FileReader();r.onload=function(ev){e.target.closest('.form-row').querySelector('img').src=ev.target.result;};r.readAsDataURL(f);})(event)">
                            </div>
                        </div>
                    </div>
                    <div class="form-col" style="flex:1;">
                        <div class="form-group">
                            <label class="form-label">Price Change Reason (optional)</label>
                            <input type="text" name="price_change_reason" class="form-control" placeholder="e.g. seasonal discount">
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:1rem;">
                    <button type="submit" class="btn btn-accent"><i data-lucide="save"></i> Save Changes</button>
                    <a href="<?= APP_URL ?>/index.php?url=admin/products" class="btn" style="margin-left:.5rem;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
