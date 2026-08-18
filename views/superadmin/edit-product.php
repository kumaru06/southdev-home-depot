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
            .sell-mode {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .sell-mode__opt {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                margin: 0;
                padding: 12px 14px;
                border: 1.5px solid var(--border, #e2e8f0);
                border-radius: 12px;
                background: #fff;
                cursor: pointer;
            }
            .sell-mode__opt:has(input:checked) {
                border-color: var(--accent, #F97316);
                background: rgba(249, 115, 22, .06);
            }
            .sell-mode__opt strong { display: block; font-size: .88rem; }
            .sell-mode__opt small { display: block; margin-top: 2px; color: var(--text-muted, #64748b); font-size: .75rem; }
            @media (max-width: 640px) { .sell-mode { grid-template-columns: 1fr; } }
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
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <?php
                $sizeOptionRows = $sizeOptionRows ?? [];
                $hasFilledSizes = false;
                foreach ($sizeOptionRows as $sz) {
                    if (trim((string) ($sz['size_label'] ?? '')) !== '') {
                        $hasFilledSizes = true;
                        break;
                    }
                }
                if (empty($sizeOptionRows)) {
                    $sizeOptionRows = [['size_label' => '', 'price' => '', 'stock' => '']];
                }
                ?>

                <div class="form-group">
                    <label class="form-label">How is this sold? <span class="required">*</span></label>
                    <div class="sell-mode" data-sell-mode>
                        <label class="sell-mode__opt">
                            <input type="radio" name="selling_type" value="simple" <?= $hasFilledSizes ? '' : 'checked' ?>>
                            <span>
                                <strong>Simple product</strong>
                                <small>One price, one stock. Example: toilet, faucet.</small>
                            </span>
                        </label>
                        <label class="sell-mode__opt">
                            <input type="radio" name="selling_type" value="sizes" <?= $hasFilledSizes ? 'checked' : '' ?>>
                            <span>
                                <strong>Multiple sizes</strong>
                                <small>Each size has its own price and stock. Example: tiles.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="js-simple-fields" <?= $hasFilledSizes ? 'hidden' : '' ?>>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Price (₱) <span class="required">*</span></label>
                                <input type="number" name="price" class="form-control js-simple-price" step="0.01" min="0" <?= $hasFilledSizes ? '' : 'required' ?> value="<?= htmlspecialchars($product['price']) ?>">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Stock</label>
                                <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($hasFilledSizes ? 0 : ($product['stock'] ?? 0)) ?>" min="0" <?= $hasFilledSizes ? 'disabled' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="js-sizes-fields" <?= $hasFilledSizes ? '' : 'hidden' ?>>
                    <div class="form-group">
                        <label class="form-label">Listing price</label>
                        <input type="number" class="form-control js-listing-price" step="0.01" min="0" readonly tabindex="-1" value="<?= htmlspecialchars($product['price']) ?>">
                        <small style="color:var(--text-muted);font-size:.75rem;">Auto-filled from the cheapest size.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sizes, prices, and stock <span class="required">*</span></label>
                        <div id="editSizeRows" class="size-opt-rows">
                            <?php foreach ($sizeOptionRows as $sz): ?>
                            <div class="size-opt-row">
                                <div class="size-opt-fields">
                                    <div class="size-opt-field">
                                        <label class="size-opt-label">Size</label>
                                        <input type="text" name="size_opt_labels[]" class="form-control" placeholder="e.g. 60 x 60 cm" value="<?= htmlspecialchars($sz['size_label'] ?? '') ?>" <?= $hasFilledSizes ? '' : 'disabled' ?>>
                                    </div>
                                    <div class="size-opt-field">
                                        <label class="size-opt-label">Price (₱)</label>
                                        <input type="number" name="size_opt_prices[]" class="form-control" step="0.01" min="0" placeholder="0.00" value="<?= htmlspecialchars($sz['price'] ?? '') ?>" <?= $hasFilledSizes ? '' : 'disabled' ?>>
                                    </div>
                                    <div class="size-opt-field">
                                        <label class="size-opt-label">Stock</label>
                                        <input type="number" name="size_opt_stocks[]" class="form-control" min="0" placeholder="0" value="<?= htmlspecialchars($sz['stock'] ?? '') ?>" <?= $hasFilledSizes ? '' : 'disabled' ?>>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline btn-sm size-opt-remove" title="Remove size">&times;</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" id="editAddSizeBtn" style="margin-top:.35rem;">+ Add size</button>
                    </div>
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

                <div class="form-group">
                    <label class="form-label">Cover Image</label>
                    <div style="display:flex; gap:.75rem; align-items:center;">
                        <div style="width:72px;height:72px;border:1px solid var(--neutral);border-radius:6px;overflow:hidden;background:var(--neutral);">
                            <img id="edit_cover_preview" src="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                        <label for="edit_product_image" style="display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; border:1.5px solid var(--border); border-radius:var(--radius-sm); background:var(--white); cursor:pointer; font-size:.875rem;">
                            <i data-lucide="upload" style="width:15px;height:15px;"></i> Choose Image
                        </label>
                        <input type="file" id="edit_product_image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;" onchange="(function(inp){var f=inp.files[0];if(!f)return;var r=new FileReader();r.onload=function(ev){document.getElementById('edit_cover_preview').src=ev.target.result;};r.readAsDataURL(f);})(this)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Price Change Reason (optional)</label>
                    <input type="text" name="price_change_reason" class="form-control" placeholder="e.g. seasonal discount">
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
                    <small style="color:var(--text-muted);font-size:.75rem;">Hold Ctrl to select multiple files (max 5 MB each).</small>
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
    var form = document.querySelector('.page-content form');
    function cheapestSizePrice(container) {
        if (!container) return '';
        var prices = Array.prototype.slice.call(container.querySelectorAll('input[name="size_opt_prices[]"]'))
            .map(function (input) { return parseFloat(input.value); })
            .filter(function (n) { return !isNaN(n) && n >= 0; });
        if (!prices.length) return '';
        return Math.min.apply(null, prices).toFixed(2);
    }
    function applySellMode() {
        if (!form) return;
        var isSizes = ((form.querySelector('input[name="selling_type"]:checked') || {}).value || 'simple') === 'sizes';
        var simpleBox = form.querySelector('.js-simple-fields');
        var sizesBox = form.querySelector('.js-sizes-fields');
        var priceInput = form.querySelector('.js-simple-price');
        var qtyInput = form.querySelector('input[name="quantity"]');
        var listing = form.querySelector('.js-listing-price');
        if (simpleBox) simpleBox.hidden = isSizes;
        if (sizesBox) sizesBox.hidden = !isSizes;
        if (priceInput) priceInput.required = !isSizes;
        if (qtyInput) {
            qtyInput.disabled = isSizes;
            if (isSizes) qtyInput.value = '0';
        }
        if (sizeBox) {
            sizeBox.querySelectorAll('input').forEach(function (el) { el.disabled = !isSizes; });
        }
        if (isSizes) {
            var cheapest = cheapestSizePrice(sizeBox);
            if (priceInput && cheapest !== '') priceInput.value = cheapest;
            if (listing) listing.value = cheapest;
        }
    }
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
                applySellMode();
                return;
            }
            btn.closest('.size-opt-row').remove();
            applySellMode();
        });
    }
    if (sizeAdd && sizeBox) {
        sizeAdd.addEventListener('click', function () {
            sizeBox.appendChild(makeSizeRow('', '', ''));
            applySellMode();
        });
    }
    if (form) {
        form.addEventListener('change', function (e) {
            if (e.target && e.target.name === 'selling_type') applySellMode();
        });
        form.addEventListener('input', function (e) {
            if (e.target && e.target.name === 'size_opt_prices[]') applySellMode();
        });
        applySellMode();
    }
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
