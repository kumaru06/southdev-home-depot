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
                    <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
                        <div id="add_image_preview_wrap" style="display:none; width:72px; height:72px; border:1px solid var(--border); border-radius:6px; overflow:hidden; background:var(--neutral);">
                            <img id="add_image_preview" src="" alt="Preview" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                        <label for="add_product_image" style="display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; border:1.5px solid var(--border); border-radius:var(--radius-sm); background:var(--white); cursor:pointer; font-size:.875rem; color:var(--text-primary); transition:border-color .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                            <i data-lucide="upload" style="width:15px;height:15px;"></i> Choose Image
                        </label>
                        <input type="file" id="add_product_image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;">
                        <span id="add_image_filename" style="font-size:.8rem; color:var(--text-muted);">No file chosen</span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-accent">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Add Product
                    </button>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="card card--table-locked">
            <div class="products-table-toolbar">
                <div class="products-table-toolbar__title">
                    <i data-lucide="package" style="width:20px;height:20px;color:var(--accent);"></i>
                    <h3>All Products</h3>
                </div>
                <div class="products-table-toolbar__actions">
                    <form id="bulkDeleteForm" class="products-bulk-form" action="<?= APP_URL ?>/index.php?url=admin/products/bulk-delete" method="POST">
                        <?= csrf_field() ?>
                        <div id="bulkDeleteIds"></div>
                        <button type="submit" id="bulkDeleteBtn" class="btn btn-outline btn-sm products-bulk-delete"
                                data-confirm="Delete the selected products? This cannot be undone."
                                data-confirm-title="Delete Selected Products"
                                data-confirm-ok="Delete Selected"
                                data-confirm-variant="danger"
                                hidden>
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            Delete Selected (<span id="bulkSelectedCount">0</span>)
                        </button>
                    </form>
                    <select id="adminCategoryFilter" class="form-control products-category-filter">
                        <option value="">All Categories</option>
                        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                    <span class="badge badge-pending products-count-badge"><?= count($products ?? []) ?> items</span>
                </div>
            </div>
            <div class="data-table-wrap data-table-wrap--locked">
                <table class="data-table" id="productsTable">
                    <thead>
                        <tr>
                            <th style="width:42px;">
                                <input type="checkbox" id="selectAllProducts" class="product-select-all" title="Select all" aria-label="Select all products">
                            </th>
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
                                <tr data-product-id="<?= (int) $product['id'] ?>">
                                    <td data-label="Select">
                                        <input type="checkbox" class="product-row-check" value="<?= (int) $product['id'] ?>" aria-label="Select product #<?= (int) $product['id'] ?>">
                                    </td>
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
                                            <a href="#" data-modal="productEditModal" class="action-btn edit product-edit-trigger"
                                               title="Edit"
                                               data-id="<?= $product['id'] ?>"
                                               data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                               data-price="<?= htmlspecialchars($product['price']) ?>"
                                               data-sku="<?= htmlspecialchars($product['sku'] ?? '', ENT_QUOTES) ?>"
                                               data-desc="<?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES) ?>"
                                               data-category-id="<?= $product['category_id'] ?? '' ?>"
                                               data-stock="<?= $product['stock'] ?? 0 ?>"
                                               data-image="<?= APP_URL ?>/assets/uploads/<?= $product['image'] ?: 'placeholder.svg' ?>">
                                                <i data-lucide="edit-2" style="width:15px;height:15px;"></i> Edit
                                            </a>
                                            <form action="<?= APP_URL ?>/index.php?url=admin/products/<?= $product['id'] ?>/delete"
                                                  method="POST" style="display:inline;"
                                                  class="inline-form">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="action-btn delete" title="Delete"
                                                        data-confirm="Delete this product?"
                                                        data-confirm-title="Delete Product"
                                                        data-confirm-ok="Delete"
                                                        data-confirm-variant="danger">
                                                    <i data-lucide="trash-2" style="width:15px;height:15px;"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center; padding:2rem;">
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

<!-- Product Edit Modal -->
<style>
/* ---------- Premium Edit Product modal ---------- */
#productEditModal {
    background: rgba(15, 23, 42, .62);
}
#productEditModal .modal-box {
    width: 640px;
    max-width: 94vw;
    max-height: 92vh;
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, .18);
    box-shadow: 0 32px 80px rgba(2, 6, 23, .35), 0 4px 18px rgba(2, 6, 23, .18);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transform: translateY(26px) scale(.96);
    opacity: 0;
    transition: transform .32s cubic-bezier(.21, 1.02, .35, 1), opacity .26s ease;
}
#productEditModal .modal-box form {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
}
#productEditModal .modal-header,
#productEditModal .modal-footer {
    flex-shrink: 0;
}
#productEditModal .modal-body {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
}
#productEditModal.active .modal-box {
    transform: translateY(0) scale(1);
    opacity: 1;
}
/* Smooth close: keep overlay visible while the box animates out */
#productEditModal.closing {
    opacity: 0;
    visibility: visible;
    transition: opacity .28s ease .06s, visibility 0s linear .34s;
}
#productEditModal.closing .modal-box {
    transform: translateY(18px) scale(.96);
    opacity: 0;
    transition: transform .28s cubic-bezier(.5, 0, .75, .4), opacity .24s ease;
}
#productEditModal .modal-header {
    padding: 20px 26px;
    background: linear-gradient(135deg, #1B2A4A 0%, #24385f 55%, #2D4A7A 100%);
    border-bottom: none;
    gap: 14px;
}
#productEditModal .modal-header h3 {
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: -.01em;
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#productEditModal .pe-header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    border-radius: 10px;
    background: rgba(249, 115, 22, .18);
    border: 1px solid rgba(249, 115, 22, .35);
    color: #fb923c;
}
#productEditModal .modal-close {
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .16);
    color: rgba(255, 255, 255, .85);
    border-radius: 10px;
    font-size: 19px;
    transition: background .2s ease, color .2s ease, transform .18s ease;
}
#productEditModal .modal-close:hover {
    background: var(--danger);
    border-color: var(--danger);
    color: #fff;
    transform: rotate(90deg);
}
#productEditModal .modal-body {
    padding: 26px;
    background:
        radial-gradient(900px 240px at 50% -80px, rgba(45, 74, 122, .05), transparent 60%),
        var(--white);
}
#productEditModal .form-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-secondary, #64748b);
    margin-bottom: 6px;
}
#productEditModal .form-control {
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 10px 13px;
    font-size: .88rem;
    background: #fbfcfe;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}
#productEditModal .form-control:focus {
    border-color: var(--accent, #F97316);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, .1);
    outline: none;
}
#productEditModal textarea.form-control { resize: vertical; min-height: 84px; }
#productEditModal .pe-media-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px;
    border: 1px dashed rgba(148, 163, 184, .55);
    border-radius: 14px;
    background: rgba(248, 250, 252, .8);
    margin-bottom: 18px;
}
#productEditModal .pe-thumb {
    width: 84px;
    height: 84px;
    flex-shrink: 0;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border);
    background: #fff;
    box-shadow: 0 4px 12px rgba(2, 6, 23, .08);
}
#productEditModal .pe-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
#productEditModal .pe-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    background: var(--white);
    cursor: pointer;
    font-size: .8rem;
    font-weight: 700;
    color: var(--charcoal, #1B2A4A);
    transition: border-color .2s ease, color .2s ease, box-shadow .2s ease, transform .15s ease;
}
#productEditModal .pe-upload-btn:hover {
    border-color: var(--accent, #F97316);
    color: var(--accent, #F97316);
    box-shadow: 0 3px 10px rgba(249, 115, 22, .14);
    transform: translateY(-1px);
}
#productEditModal .pe-upload-hint {
    display: block;
    margin-top: 6px;
    font-size: .72rem;
    color: var(--text-muted, #94a3b8);
}
#productEditModal .modal-footer {
    padding: 18px 26px;
    background: rgba(248, 250, 252, .9);
    border-top: 1px solid var(--border);
    gap: 10px;
}
#productEditModal .modal-footer .btn {
    border-radius: 11px;
    padding: 10px 20px;
    font-weight: 700;
}
#productEditModal .modal-footer .btn-accent {
    box-shadow: 0 6px 18px rgba(249, 115, 22, .3);
    transition: transform .18s ease, box-shadow .2s ease, background .2s ease;
}
#productEditModal .modal-footer .btn-accent:hover {
    transform: translateY(-1px);
    box-shadow: 0 9px 24px rgba(249, 115, 22, .38);
}
@media (max-width: 640px) {
    #productEditModal .modal-box { width: 100%; max-width: calc(100vw - 20px); border-radius: 16px; }
    #productEditModal .modal-body { padding: 18px; }
    #productEditModal .modal-header { padding: 16px 18px; }
    #productEditModal .modal-footer { padding: 14px 18px; }
    #productEditModal .pe-media-card { flex-wrap: wrap; }
}
</style>
<div id="productEditModal" class="modal-overlay" style="display:flex;align-items:center;justify-content:center;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>
                <span class="pe-header-icon">
                    <i data-lucide="package" style="width:17px;height:17px;"></i>
                </span>
                <span id="productEditTitle">Edit Product</span>
            </h3>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="productEditForm" method="POST" enctype="multipart/form-data" action="#">
            <?= csrf_field() ?>
            <input type="hidden" name="existing_image" id="pe_existing_image" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" id="pe_name" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="pe_category" class="form-control">
                                <option value="">Select Category</option>
                                <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" name="price" id="pe_price" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" id="pe_sku" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="pe_desc" class="form-control" rows="3"></textarea>
                </div>

                <div class="pe-media-card">
                    <div class="pe-thumb">
                        <img id="pe_image_preview" src="<?= APP_URL ?>/assets/uploads/placeholder.svg" alt="">
                    </div>
                    <div style="flex:1; min-width:150px;">
                        <label class="form-label">Product Image</label>
                        <label for="pe_image_input" class="pe-upload-btn">
                            <i data-lucide="upload" style="width:14px;height:14px;"></i> Replace Image
                        </label>
                        <span class="pe-upload-hint">JPG, PNG, or WebP — cropped square</span>
                        <input type="file" name="image" id="pe_image_input" accept="image/jpeg,image/png,image/webp,image/gif" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;">
                    </div>
                    <div style="width:120px;">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="pe_quantity" class="form-control" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Price Change Reason (optional)</label>
                    <input type="text" name="price_change_reason" id="pe_price_reason" class="form-control" placeholder="e.g. Supplier price increase">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="productEditCancel">Cancel</button>
                <button type="submit" class="btn btn-accent">
                    <i data-lucide="save" style="width:15px;height:15px;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Load Cropper.js (CSS + JS)
var cropperCss = document.createElement('link');
cropperCss.rel = 'stylesheet';
cropperCss.href = 'https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css';
document.head.appendChild(cropperCss);
var cropperScript = document.createElement('script');
cropperScript.src = 'https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js';
document.head.appendChild(cropperScript);

(function(){
    var appUrl = '<?= APP_URL ?>';

    // ── Shared crop state ──────────────────────────────────────────────────
    var cropper         = null;
    var cropContext     = null;   // 'add' | 'edit'
    var addCroppedBlob  = null;
    var editCroppedBlob = null;
    var addOriginalName = '';    // original filename (no extension) for display

    function waitForCropper(cb){ if(typeof Cropper==='undefined'){ setTimeout(function(){ waitForCropper(cb); },120); return; } cb(); }

    function openCropModal(src, context){
        cropContext = context;
        waitForCropper(function(){
            var overlay = document.getElementById('imageCropModal');
            var img     = document.getElementById('cropperImage');
            img.src = src;
            overlay.classList.add('active');
            if(cropper){ try{ cropper.destroy(); }catch(e){} cropper=null; }
            cropper = new Cropper(img, { aspectRatio:1, viewMode:1, autoCropArea:1 });
        });
    }

    function closeCropModal(){
        var overlay = document.getElementById('imageCropModal');
        overlay.classList.remove('active');
        if(cropper){ try{ cropper.destroy(); }catch(e){} cropper=null; }
    }

    // Apply / Cancel crop buttons
    document.addEventListener('click', function(e){
        if(e.target && (e.target.id==='cropCancelBtn' || e.target.id==='cropCancelBtn2')){
            closeCropModal();
        }
        if(e.target && e.target.id==='cropApplyBtn'){
            if(!cropper) return;
            cropper.getCroppedCanvas({ width:800, height:800 }).toBlob(function(blob){
                if(cropContext === 'add'){
                    addCroppedBlob = blob;
                    var wrap    = document.getElementById('add_image_preview_wrap');
                    var preview = document.getElementById('add_image_preview');
                    var span    = document.getElementById('add_image_filename');
                    if(wrap)    wrap.style.display = 'block';
                    if(preview) preview.src = URL.createObjectURL(blob);
                    if(span)    span.textContent = addOriginalName || 'Cropped image';
                } else if(cropContext === 'edit'){
                    editCroppedBlob = blob;
                    var preview = document.getElementById('pe_image_preview');
                    if(preview) preview.src = URL.createObjectURL(blob);
                }
                closeCropModal();
            }, 'image/jpeg', 0.9);
        }
    });

    // ── ADD PRODUCT: file input → open crop modal ──────────────────────────
    var addInput = document.getElementById('add_product_image');
    if(addInput){
        addInput.addEventListener('change', function(){
            var f = this.files[0];
            if(!f) return;
            addOriginalName = f.name.replace(/\.[^/.]+$/, ''); // strip extension
            var reader = new FileReader();
            reader.onload = function(ev){ openCropModal(ev.target.result, 'add'); };
            reader.readAsDataURL(f);
        });
    }

    // ADD PRODUCT: inject cropped blob into the file input before normal submit
    var addForm = document.querySelector('form[action*="admin/products/create"]');
    if(addForm){
        addForm.addEventListener('submit', function(){
            if(!addCroppedBlob) return; // no crop — let browser submit as-is
            try {
                var dt = new DataTransfer();
                dt.items.add(new File([addCroppedBlob], 'cropped_'+Date.now()+'.jpg', { type:'image/jpeg' }));
                var inp = document.getElementById('add_product_image');
                if(inp) inp.files = dt.files;
            } catch(ex) { /* DataTransfer not supported — fall back to uncropped */ }
            // let the form submit normally so server redirects & flash messages work
        });
    }

    // ── EDIT PRODUCT MODAL ─────────────────────────────────────────────────
    document.querySelectorAll('.product-edit-trigger').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            var id    = this.dataset.id;
            var rowImg = this.closest('tr') ? this.closest('tr').querySelector('td[data-label="Image"] img') : null;
            var image  = rowImg && rowImg.src ? rowImg.src : (this.dataset.image || (appUrl+'/assets/uploads/placeholder.svg'));

            document.getElementById('productEditTitle').textContent = 'Edit Product — '+(this.dataset.name||'');
            document.getElementById('pe_name').value        = this.dataset.name    || '';
            document.getElementById('pe_price').value       = this.dataset.price   || '';
            document.getElementById('pe_sku').value         = this.dataset.sku     || '';
            document.getElementById('pe_desc').value        = this.dataset.desc    || '';
            document.getElementById('pe_category').value    = this.dataset.categoryId || '';
            document.getElementById('pe_quantity').value    = this.dataset.stock   || 0;
            document.getElementById('pe_existing_image').value = image.replace(appUrl+'/assets/uploads/','');
            document.getElementById('pe_image_preview').src = image;

            var form = document.getElementById('productEditForm');
            form.action = appUrl+'/index.php?url=admin/products/'+id+'/update';

            editCroppedBlob = null; // reset any previous crop
            document.getElementById('productEditModal').classList.add('active');
        });
    });

    // Edit image input → open crop modal
    var peInput = document.getElementById('pe_image_input');
    if(peInput){
        peInput.addEventListener('change', function(){
            var f = this.files[0];
            if(!f) return;
            var reader = new FileReader();
            reader.onload = function(ev){ openCropModal(ev.target.result, 'edit'); };
            reader.readAsDataURL(f);
        });
    }

    // Edit form submit — inject cropped blob if present
    var peForm = document.getElementById('productEditForm');
    if(peForm){
        peForm.addEventListener('submit', function(e){
            if(!editCroppedBlob) return;
            e.preventDefault();
            var fd = new FormData(peForm);
            fd.set('image', new File([editCroppedBlob], 'cropped_'+Date.now()+'.jpg', { type:'image/jpeg' }));
            fetch(peForm.action, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(resp){ return resp.json().catch(function(){ throw new Error('Invalid JSON'); }); })
            .then(function(data){
                if(data && data.success){
                    if(typeof showNotification==='function') showNotification(data.message||'Saved','success');
                    try{
                        var m   = peForm.action.match(/admin\/products\/(\d+)\/update/);
                        var pid = m ? m[1] : (data.id||null);
                        if(pid){
                            var trigger = document.querySelector('.product-edit-trigger[data-id="'+pid+'"]');
                            var row     = trigger ? trigger.closest('tr') : null;
                            if(row){
                                var img = row.querySelector('td[data-label="Image"] img');
                                if(img) img.src = data.image ? (appUrl+'/assets/uploads/'+data.image+'?v='+Date.now()) : (appUrl+'/assets/uploads/placeholder.svg');
                                var nc = row.querySelector('td[data-label="Name"] strong');
                                if(nc) nc.textContent = document.getElementById('pe_name').value;
                                var pc = row.querySelector('td[data-label="Price"] strong');
                                if(pc) pc.textContent = '₱'+parseFloat(document.getElementById('pe_price').value||0).toFixed(2);
                            }
                        }
                    }catch(ex){}
                    closeEditModal();
                    editCroppedBlob = null;
                } else {
                    throw new Error((data&&data.message)||'Save failed');
                }
            })
            .catch(function(err){ if(typeof showNotification==='function') showNotification(err.message||'Failed','error'); else alert(err.message); });
        });
    }

    function clearEditModal(){
        var inp = document.getElementById('pe_image_input');
        if(inp) inp.value = '';
        var preview  = document.getElementById('pe_image_preview');
        var existing = document.getElementById('pe_existing_image');
        if(preview && existing){
            preview.src = existing.value ? (appUrl+'/assets/uploads/'+existing.value) : (appUrl+'/assets/uploads/placeholder.svg');
        }
    }

    // Animated close: fade the box out first, then hide the overlay
    function closeEditModal(){
        var overlay = document.getElementById('productEditModal');
        if(!overlay || !overlay.classList.contains('active')) return;
        overlay.classList.add('closing');
        overlay.classList.remove('active');
        setTimeout(function(){
            overlay.classList.remove('closing');
            clearEditModal();
        }, 340);
    }

    var editModalOverlay = document.getElementById('productEditModal');
    if(editModalOverlay){
        editModalOverlay.addEventListener('click', function(e){
            if(e.target===editModalOverlay || e.target.closest('.modal-close') || e.target.closest('#productEditCancel')){
                closeEditModal();
            }
        });
    }

    // Esc key closes the edit modal
    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') closeEditModal();
    });
})();
</script>

<!-- Image Crop Modal Markup -->
<div id="imageCropModal" class="modal-overlay" style="display:flex; align-items:center; justify-content:center;">
    <div class="modal-box" style="max-width:820px; width:90vw;">
        <div class="modal-header">
            <h3>Crop Image</h3>
            <button type="button" class="modal-close" id="cropCancelBtn">&times;</button>
        </div>
        <div class="modal-body" style="display:flex; flex-direction:column; gap:12px;">
            <div style="max-height:68vh; overflow:auto;">
                <img id="cropperImage" src="" alt="Cropper image" style="max-width:100%; display:block; margin:0 auto;">
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="btn btn-outline" id="cropCancelBtn2">Cancel</button>
                <button type="button" class="btn btn-accent" id="cropApplyBtn">Apply Crop</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Category filter
    var sel = document.getElementById('adminCategoryFilter');
    if(sel){
        sel.addEventListener('change', function(){
            var v = this.value.trim().toLowerCase();
            var rows = document.querySelectorAll('#productsTable tbody tr');
            rows.forEach(function(r){
                var catTd = r.querySelector('td[data-label="Category"]');
                if(!catTd) return;
                var text = catTd.textContent.trim().toLowerCase();
                r.style.display = (v === '' || text === v) ? '' : 'none';
            });
            syncProductSelection();
        });
    }

    var selectAll = document.getElementById('selectAllProducts');
    var bulkBtn = document.getElementById('bulkDeleteBtn');
    var bulkCount = document.getElementById('bulkSelectedCount');
    var bulkForm = document.getElementById('bulkDeleteForm');
    var bulkIds = document.getElementById('bulkDeleteIds');

    function visibleRowChecks() {
        return Array.prototype.slice.call(document.querySelectorAll('#productsTable tbody tr')).filter(function(row){
            return row.style.display !== 'none';
        }).map(function(row){
            return row.querySelector('.product-row-check');
        }).filter(Boolean);
    }

    function syncProductSelection() {
        var checks = visibleRowChecks();
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
        }

        if (bulkIds) {
            bulkIds.innerHTML = selected.map(function(c){
                return '<input type="hidden" name="product_ids[]" value="' + c.value + '">';
            }).join('');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function(){
            visibleRowChecks().forEach(function(c){ c.checked = selectAll.checked; });
            syncProductSelection();
        });
    }

    document.querySelectorAll('.product-row-check').forEach(function(c){
        c.addEventListener('change', syncProductSelection);
    });

    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e){
            syncProductSelection();
            var selected = document.querySelectorAll('#bulkDeleteIds input[name="product_ids[]"]');
            if (!selected.length) {
                e.preventDefault();
            }
        });
    }

    syncProductSelection();
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
