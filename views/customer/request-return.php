<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="<?= APP_URL ?>/index.php?url=orders">Profile</a>
        /
        <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>"><?= htmlspecialchars($order['order_number']) ?></a>
        /
        <span>Request Return</span>
    </nav>

    <h1 class="page-heading">Request Return</h1>

    <div class="card return-request-card">
        <div class="return-order-summary">
            <div class="return-summary-row"><span>Order</span><strong><?= htmlspecialchars($order['order_number']) ?></strong></div>
            <div class="return-summary-row"><span>Order Date</span><strong><?= date('M d, Y', strtotime($order['created_at'])) ?></strong></div>
            <div class="return-summary-row"><span>Total</span><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></div>
        </div>

        <form action="<?= APP_URL ?>/index.php?url=returns/submit" method="POST" style="margin-top: 24px;">
            <?= csrf_field() ?>
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

            <div class="form-group">
                <label for="return_reason_select">Reason for Return <span class="required">*</span></label>
                <select id="return_reason_select" class="form-control" required>
                    <option value="">Select a reason…</option>
                    <option value="Item arrived damaged or broken">Item arrived damaged or broken</option>
                    <option value="Received wrong item">Received wrong item</option>
                    <option value="Item does not match description or photos">Item does not match description or photos</option>
                    <option value="Other">Other (please specify)</option>
                </select>
            </div>

            <div class="form-group" id="return-items-group">
                <label id="return-items-label">What product did you receive with a problem? <span class="required">*</span></label>
                <p class="text-muted" style="margin: 0 0 10px;">Select only the product or products included in this return request.</p>
                <div class="return-item-list">
                    <?php foreach (($orderItems ?? []) as $item): ?>
                        <label class="return-item-option">
                            <input type="checkbox" name="return_items[]" value="<?= (int) $item['id'] ?>">
                            <span class="return-item-thumb">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                <?php else: ?>
                                    <span class="return-item-placeholder"></span>
                                <?php endif; ?>
                            </span>
                            <span class="return-item-info">
                                <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                <small>Qty: <?= (int) $item['quantity'] ?> • ₱<?= number_format($item['price'], 2) ?> each</small>
                            </span>
                            <span class="return-item-status">Select</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group" id="return-detail-group">
                <label for="reason">Additional Details</label>
                <textarea id="reason" name="reason" class="form-control" rows="4" placeholder="Please provide more details about your return request…"></textarea>
                <small class="text-muted">Describe the issue in detail. Attach photos if possible when the request is reviewed.</small>
            </div>

            <input type="hidden" name="reason" id="final_reason" value="">

            <div class="form-actions">
                <button type="submit" class="btn btn-accent">Submit Request</button>
                <a href="<?= APP_URL ?>/index.php?url=orders/<?= $order['id'] ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var sel = document.getElementById('return_reason_select');
    var detailGroup = document.getElementById('return-detail-group');
    var itemGroup = document.getElementById('return-items-group');
    var itemLabel = document.getElementById('return-items-label');
    var textarea = document.getElementById('reason');
    var finalReason = document.getElementById('final_reason');
    var form = textarea.closest('form');

    // Show/hide additional details — always visible but required for "Other"
    sel.addEventListener('change', function(){
        if (this.value === 'Other') {
            textarea.setAttribute('required', 'required');
            textarea.placeholder = 'Please specify your reason for returning…';
            itemLabel.innerHTML = 'What product do you want to return? <span class="required">*</span>';
        } else if (this.value === 'Item arrived damaged or broken') {
            textarea.removeAttribute('required');
            textarea.placeholder = 'Please describe the damage or broken part…';
            itemLabel.innerHTML = 'What damaged product did you receive? <span class="required">*</span>';
        } else {
            textarea.removeAttribute('required');
            textarea.placeholder = 'Please provide more details about your return request…';
            itemLabel.innerHTML = 'What product do you want to return? <span class="required">*</span>';
        }
    });

    // Combine selected reason + details into final reason field on submit
    form.addEventListener('submit', function(e){
        var selected = sel.value;
        if (!selected) {
            e.preventDefault();
            sel.focus();
            return;
        }
        var checkedItems = form.querySelectorAll('input[name="return_items[]"]:checked');
        if (!checkedItems.length) {
            e.preventDefault();
            itemGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
            itemGroup.classList.add('return-item-error');
            setTimeout(function(){ itemGroup.classList.remove('return-item-error'); }, 1200);
            return;
        }
        var details = textarea.value.trim();
        var combined = '';
        if (selected === 'Other') {
            combined = details || 'Other reason (no details provided)';
        } else {
            combined = details ? selected + ' — ' + details : selected;
        }
        finalReason.value = combined;
        // Disable the textarea name so only the hidden field submits
        textarea.removeAttribute('name');
    });
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
