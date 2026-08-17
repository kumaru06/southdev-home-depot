<?php
// Edit Product View
$galleryImages = $galleryImages ?? [];
$specPairs = $specPairs ?? [];
if (empty($specPairs)) {
    $specPairs = ['' => ''];
}
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
        <style>
            .size-opt-rows { display: flex; flex-direction: column; gap: 12px; }
            .size-opt-row {
                display: flex;
                align-items: flex-end;
                gap: 8px;
                padding: 12px;
                border: 1px solid var(--border, #e2e8f0);
                border-radius: 12px;
                background: #f8fafc;
            }
            .size-opt-fields {
                display: grid;
                grid-template-columns: 1.4fr 1fr 0.8fr;
                gap: 10px;
                flex: 1;
                min-width: 0;
            }
            .size-opt-field { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
            .size-opt-label {
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: var(--text-muted, #64748b);
                margin: 0;
            }
            .size-opt-row .size-opt-remove {
                flex-shrink: 0;
                width: 36px;
                height: 36px;
            }
            @media (max-width: 640px) {
                .size-opt-fields { grid-template-columns: 1fr; }
            }
        </style>
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

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Size Label</label>
                            <input type="text" name="size_label" class="form-control" value="<?= htmlspecialchars($product['size_label'] ?? '') ?>" placeholder="e.g. 60x60 cm">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Size Group</label>
                            <input type="text" name="size_group" class="form-control" value="<?= htmlspecialchars($product['size_group'] ?? '') ?>" placeholder="e.g. porcelain-floor-tile">
                            <small style="color:var(--text-muted);font-size:.75rem;">Products with the same group appear together in Choose Your Size.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Specifications</label>
                    <div id="editSpecRows">
                        <?php foreach ($specPairs as $sk => $sv): ?>
                        <div class="form-row spec-row" style="gap:.5rem;margin-bottom:.5rem;">
                            <input type="text" name="spec_keys[]" class="form-control" placeholder="Label" value="<?= htmlspecialchars($sk) ?>">
                            <input type="text" name="spec_values[]" class="form-control" placeholder="Value" value="<?= htmlspecialchars($sv) ?>">
                            <button type="button" class="btn btn-outline btn-sm spec-remove">&times;</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" id="editAddSpecBtn">+ Add specification</button>
                </div>

                <div class="form-row">
                    <div class="form-col">
                <div class="form-group">
                    <label class="form-label">Choose Your Size options</label>
                    <div id="editSizeRows">
                        <?php
                        $sizeOptionRows = $sizeOptionRows ?? [];
                        if (empty($sizeOptionRows)) {
                            $sizeOptionRows = [['size_label' => '', 'price' => '', 'stock' => '']];
                        }
                        foreach ($sizeOptionRows as $sz):
                        ?>
                        <div class="size-opt-row">
                            <div class="size-opt-fields">
                                <div class="size-opt-field">
                                    <label class="size-opt-label">Size</label>
                                    <input type="text" name="size_opt_labels[]" class="form-control" placeholder="e.g. 60 x 60 cm" value="<?= htmlspecialchars($sz['size_label'] ?? '') ?>">
                                </div>
                                <div class="size-opt-field">
                                    <label class="size-opt-label">Price (₱)</label>
                                    <input type="number" name="size_opt_prices[]" class="form-control" step="0.01" min="0" placeholder="0.00" value="<?= htmlspecialchars($sz['price'] ?? '') ?>">
                                </div>
                                <div class="size-opt-field">
                                    <label class="size-opt-label">Stock</label>
                                    <input type="number" name="size_opt_stocks[]" class="form-control" min="0" placeholder="0" value="<?= htmlspecialchars($sz['stock'] ?? '') ?>">
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline btn-sm size-opt-remove" title="Remove size">&times;</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" id="editAddSizeBtn">+ Add size</button>
                </div>

                <div class="form-group">
                    <label class="form-label">Cover Image</label>
                            <div style="display:flex; gap:.75rem; align-items:center;">
                                <div style="width:72px;height:72px;border:1px solid var(--neutral);border-radius:6px;overflow:hidden;background:var(--neutral);">
                                    <img src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                </div>
                                <label for="edit_product_image" style="display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; border:1.5px solid var(--border); border-radius:var(--radius-sm); background:var(--white); cursor:pointer; font-size:.875rem;">
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

                <div class="form-group">
                    <label class="form-label">Gallery Images</label>
                    <?php if (!empty($galleryImages)): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:.75rem;">
                        <?php foreach ($galleryImages as $gImg): ?>
                        <label style="display:block;width:88px;text-align:center;font-size:.72rem;">
                            <div style="width:88px;height:88px;border:1px solid var(--border);border-radius:8px;overflow:hidden;background:var(--neutral);">
                                <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($gImg['filename']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <span style="display:inline-flex;gap:4px;align-items:center;margin-top:4px;">
                                <input type="checkbox" name="delete_gallery_ids[]" value="<?= (int)$gImg['id'] ?>"> Remove
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                    <small style="color:var(--text-muted);font-size:.75rem;">Upload additional product photos (max 5 MB each).</small>
                </div>

                <div class="form-actions" style="margin-top:1rem;">
                    <button type="submit" class="btn btn-accent"><i data-lucide="save"></i> Save Changes</button>
                    <a href="<?= APP_URL ?>/index.php?url=admin/products" class="btn" style="margin-left:.5rem;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var box = document.getElementById('editSpecRows');
    var addBtn = document.getElementById('editAddSpecBtn');
    function makeRow(k, v) {
        var row = document.createElement('div');
        row.className = 'form-row spec-row';
        row.style.cssText = 'gap:.5rem;margin-bottom:.5rem;';
        row.innerHTML = '<input type="text" name="spec_keys[]" class="form-control" placeholder="Label">' +
            '<input type="text" name="spec_values[]" class="form-control" placeholder="Value">' +
            '<button type="button" class="btn btn-outline btn-sm spec-remove">&times;</button>';
        row.querySelectorAll('input')[0].value = k || '';
        row.querySelectorAll('input')[1].value = v || '';
        return row;
    }
    if (box) {
        box.addEventListener('click', function (e) {
            var btn = e.target.closest('.spec-remove');
            if (!btn) return;
            var rows = box.querySelectorAll('.spec-row');
            if (rows.length <= 1) {
                rows[0].querySelectorAll('input').forEach(function (i) { i.value = ''; });
                return;
            }
            btn.closest('.spec-row').remove();
        });
    }
    if (addBtn && box) {
        addBtn.addEventListener('click', function () {
            box.appendChild(makeRow('', ''));
        });
    }
    var sizeBox = document.getElementById('editSizeRows');
    var sizeAdd = document.getElementById('editAddSizeBtn');
    function makeSizeRow(label, price, stock) {
        var row = document.createElement('div');
        row.className = 'size-opt-row';
        row.innerHTML =
            '<div class="size-opt-fields">' +
                '<div class="size-opt-field">' +
                    '<label class="size-opt-label">Size</label>' +
                    '<input type="text" name="size_opt_labels[]" class="form-control" placeholder="e.g. 60 x 60 cm">' +
                '</div>' +
                '<div class="size-opt-field">' +
                    '<label class="size-opt-label">Price (₱)</label>' +
                    '<input type="number" name="size_opt_prices[]" class="form-control" step="0.01" min="0" placeholder="0.00">' +
                '</div>' +
                '<div class="size-opt-field">' +
                    '<label class="size-opt-label">Stock</label>' +
                    '<input type="number" name="size_opt_stocks[]" class="form-control" min="0" placeholder="0">' +
                '</div>' +
            '</div>' +
            '<button type="button" class="btn btn-outline btn-sm size-opt-remove" title="Remove size">&times;</button>';
        var inputs = row.querySelectorAll('input');
        inputs[0].value = label || '';
        inputs[1].value = price != null ? price : '';
        inputs[2].value = stock != null ? stock : '';
        return row;
    }
    if (sizeBox) {
        sizeBox.addEventListener('click', function (e) {
            var btn = e.target.closest('.size-opt-remove');
            if (!btn) return;
            var rows = sizeBox.querySelectorAll('.size-opt-row');
            if (rows.length <= 1) {
                rows[0].querySelectorAll('input').forEach(function (i) { i.value = ''; });
                return;
            }
            btn.closest('.size-opt-row').remove();
        });
    }
    if (sizeAdd && sizeBox) {
        sizeAdd.addEventListener('click', function () {
            sizeBox.appendChild(makeSizeRow('', '', ''));
        });
    }
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
