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
        <div class="card">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:.5rem;">
                    <i data-lucide="package" style="width:20px;height:20px;color:var(--accent);"></i>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:600;">All Products</h3>
                </div>
                <div style="display:flex; align-items:center; gap:.75rem;">
                    <select id="adminCategoryFilter" class="form-control" style="min-width:180px;padding:.45rem;border-radius:6px;border:1px solid var(--border);background:var(--surface);">
                        <option value="">All Categories</option>
                        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                    <span class="badge badge-pending" style="font-size:.8rem;"><?= count($products ?? []) ?> items</span>
                </div>
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

<!-- Product Edit Modal -->
<div id="productEditModal" class="modal-overlay" style="display:flex;align-items:center;justify-content:center;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="productEditTitle">Edit Product</h3>
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

                <div class="form-row" style="align-items:center; gap:.75rem;">
                    <div style="width:80px;height:80px;border:1px solid var(--border);border-radius:6px;overflow:hidden;background:var(--neutral);">
                        <img id="pe_image_preview" src="<?= APP_URL ?>/assets/uploads/placeholder.svg" alt="" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div style="flex:1;">
                        <label class="form-label">Replace Image</label><br>
                        <label for="pe_image_input" style="display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .85rem; border:1.5px solid var(--border); border-radius:var(--radius-sm); background:var(--white); cursor:pointer; font-size:.8rem; color:var(--text-primary); transition:border-color .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                            <i data-lucide="upload" style="width:13px;height:13px;"></i> Choose Image
                        </label>
                        <input type="file" name="image" id="pe_image_input" accept="image/jpeg,image/png,image/webp,image/gif" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;">
                    </div>
                    <div style="width:120px;">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="pe_quantity" class="form-control" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Price Change Reason (optional)</label>
                    <input type="text" name="price_change_reason" id="pe_price_reason" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?= APP_URL ?>/index.php?url=admin/products" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-accent">Save Changes</button>
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
                    document.getElementById('productEditModal').classList.remove('active');
                    editCroppedBlob = null;
                    clearEditModal();
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

    var editModalOverlay = document.getElementById('productEditModal');
    if(editModalOverlay){
        editModalOverlay.addEventListener('click', function(e){
            if(e.target===editModalOverlay || e.target.closest('.modal-close')){
                editModalOverlay.classList.remove('active');
                clearEditModal();
            }
        });
    }
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
            var rows = document.querySelectorAll('.data-table tbody tr');
            rows.forEach(function(r){
                var catTd = r.querySelector('td[data-label="Category"]');
                if(!catTd) return;
                var text = catTd.textContent.trim().toLowerCase();
                r.style.display = (v === '' || text === v) ? '' : 'none';
            });
        });
    }
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
