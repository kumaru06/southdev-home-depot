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
                        <label class="form-label">Replace Image</label>
                        <input type="file" name="image" id="pe_image_input" accept="image/*">
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

// Populate and open product edit modal
(function(){
    var appUrl = '<?= APP_URL ?>';
    document.querySelectorAll('.product-edit-trigger').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            var id = this.dataset.id;
            var name = this.dataset.name || '';
            var price = this.dataset.price || '';
            var sku = this.dataset.sku || '';
            var desc = this.dataset.desc || '';
            var cat = this.dataset.categoryId || '';
            var stock = this.dataset.stock || 0;
            // Prefer current row image src (keeps modal in sync if image changed)
            var rowImg = this.closest('tr') ? this.closest('tr').querySelector('td[data-label="Image"] img') : null;
            var image = rowImg && rowImg.src ? rowImg.src : (this.dataset.image || (appUrl + '/assets/uploads/placeholder.svg'));

            document.getElementById('productEditTitle').textContent = 'Edit Product — ' + name;
            document.getElementById('pe_name').value = name;
            document.getElementById('pe_price').value = price;
            document.getElementById('pe_sku').value = sku;
            document.getElementById('pe_desc').value = desc;
            document.getElementById('pe_category').value = cat;
            document.getElementById('pe_quantity').value = stock;
            document.getElementById('pe_existing_image').value = image.replace(appUrl + '/assets/uploads/', '');
            document.getElementById('pe_image_preview').src = image;

            var form = document.getElementById('productEditForm');
            form.action = appUrl + '/index.php?url=admin/products/' + id + '/update';

            // open modal
            var overlay = document.getElementById('productEditModal');
            if(overlay) overlay.classList.add('active');
        });
    });

    // Image preview for replacement
    var input = document.getElementById('pe_image_input');
    if(input){
        input.addEventListener('change', function(){
            var file = this.files[0];
            if(!file) return;
            var reader = new FileReader();
            reader.onload = function(ev){
                var img = document.getElementById('pe_image_preview');
                if(img) img.src = ev.target.result;
                // offer cropping: open crop modal automatically after loading image
                setTimeout(function(){ openImageCropModal(ev.target.result); }, 120);
            };
            reader.readAsDataURL(file);
        });
    }

    // --- Image Crop Modal ---
    var cropper = null;
    var croppedBlob = null;
    function openImageCropModal(src){
        // wait until cropper script loaded
        if(typeof Cropper === 'undefined'){
            setTimeout(function(){ openImageCropModal(src); }, 120);
            return;
        }
        var overlay = document.getElementById('imageCropModal');
        var img = document.getElementById('cropperImage');
        img.src = src;
        overlay.classList.add('active');
        // destroy existing cropper
        if(cropper){ try{ cropper.destroy(); }catch(e){} cropper = null; }
        cropper = new Cropper(img, { aspectRatio: 1, viewMode: 1, autoCropArea: 1 });
    }

    function closeImageCropModal(){
        var overlay = document.getElementById('imageCropModal');
        overlay.classList.remove('active');
        if(cropper){ try{ cropper.destroy(); }catch(e){} cropper = null; }
    }

    document.addEventListener('click', function(e){
        if(e.target && e.target.id === 'cropApplyBtn'){
            if(!cropper) return;
            cropper.getCroppedCanvas({ width: 800, height: 800 }).toBlob(function(blob){
                croppedBlob = blob;
                // show preview inside modal
                var preview = document.getElementById('pe_image_preview');
                preview.src = URL.createObjectURL(blob);
                // close crop modal
                closeImageCropModal();
            }, 'image/jpeg', 0.9);
        }
        if(e.target && e.target.id === 'cropCancelBtn'){
            closeImageCropModal();
        }
    });

    // Intercept product edit form submit to attach cropped image (if any)
    var peForm = document.getElementById('productEditForm');
    if(peForm){
        peForm.addEventListener('submit', function(e){
            // if user provided a cropped blob, submit via AJAX with cropped image
            if(croppedBlob){
                e.preventDefault();
                var formData = new FormData(peForm);
                var filename = 'cropped_' + Date.now() + '.jpg';
                var file = new File([croppedBlob], filename, { type: croppedBlob.type });
                formData.set('image', file);
                fetch(peForm.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(resp){
                    return resp.json().catch(function(){ throw new Error('Invalid JSON response'); });
                })
                .then(function(data){
                    if(data && data.success){
                        // Show toast
                        if(typeof showNotification === 'function') showNotification(data.message || 'Saved', 'success');
                        // Update row image (cache-busted)
                        try{
                            var m = peForm.action.match(/admin\/products\/(\d+)\/update/);
                            var pid = m ? m[1] : (data.id || null);
                            if(pid){
                                var trigger = document.querySelector('.product-edit-trigger[data-id="' + pid + '"]');
                                var row = trigger ? trigger.closest('tr') : null;
                                if(row){
                                    var img = row.querySelector('td[data-label="Image"] img');
                                    if(img){
                                        var newSrc = data.image ? (appUrl + '/assets/uploads/' + data.image + '?v=' + Date.now()) : (appUrl + '/assets/uploads/placeholder.svg');
                                        img.src = newSrc;
                                    }
                                    // update name/price text quickly
                                    var nameCell = row.querySelector('td[data-label="Name"] strong');
                                    if(nameCell) nameCell.textContent = document.getElementById('pe_name').value;
                                    var priceCell = row.querySelector('td[data-label="Price"] strong');
                                    if(priceCell) priceCell.textContent = '₱' + parseFloat(document.getElementById('pe_price').value || 0).toFixed(2);
                                }
                            }
                        }catch(e){/* ignore */}

                        // close modal and reset
                        var overlay = document.getElementById('productEditModal');
                        if(overlay) overlay.classList.remove('active');
                        croppedBlob = null;
                        clearProductEditModal();
                    } else {
                        throw new Error((data && data.message) ? data.message : 'Save failed');
                    }
                })
                .catch(function(err){ if(typeof showNotification === 'function') showNotification(err.message || 'Failed to save product', 'error'); else alert('Failed to save product: ' + err.message); });
            }
        });
    }
    
    // Reset modal preview and file input when modal is closed or cancelled
    function clearProductEditModal(){
        var input = document.getElementById('pe_image_input');
        if(input){ input.value = ''; }
        var preview = document.getElementById('pe_image_preview');
        var existing = document.getElementById('pe_existing_image');
        if(preview && existing){
            preview.src = existing.value ? (appUrl + '/assets/uploads/' + existing.value) : (appUrl + '/assets/uploads/placeholder.svg');
        }
    }

    // Close handlers: close button(s) and backdrop click will call clear
    var modalOverlay = document.getElementById('productEditModal');
    if(modalOverlay){
        modalOverlay.addEventListener('click', function(e){
            if(e.target === modalOverlay || e.target.closest('.modal-close')){
                modalOverlay.classList.remove('active');
                clearProductEditModal();
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

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
