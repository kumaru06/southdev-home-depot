/**
 * SouthDev Home Depot – Cart JavaScript
 * AJAX cart operations with CSRF protection
 */

(function () {
    'use strict';

    function addToCart(productId, quantity) {
        quantity = quantity || 1;
        fetch(APP_URL + '/index.php?url=cart/add', {
            method: 'POST',
            headers: csrfHeaders(),
            body: csrfBody('product_id=' + productId + '&quantity=' + quantity)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                updateCartBadge(data.cart_count);
                showNotification('Product added to cart', 'success');
            } else {
                showNotification(data.message || 'Failed to add to cart', 'error');
            }
        })
        .catch(function () {
            showNotification('Network error – please try again', 'error');
        });
    }

    function updateQuantity(cartId, quantity) {
        if (quantity < 1) return;
        fetch(APP_URL + '/index.php?url=cart/update', {
            method: 'POST',
            headers: csrfHeaders(),
            body: csrfBody('cart_id=' + cartId + '&quantity=' + quantity)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                location.reload();
            } else {
                showNotification(data.message || 'Failed to update quantity', 'error');
            }
        });
    }

    function removeFromCart(cartId) {
        var ask = (typeof window.confirmDialog === 'function')
            ? window.confirmDialog({
                title: 'Remove item?',
                message: 'Remove this item from your cart?',
                confirmText: 'Remove',
                cancelText: 'Cancel',
                confirmVariant: 'danger'
            })
            : Promise.resolve(confirm('Remove this item from your cart?'));

        ask.then(function (ok) {
            if (!ok) return;

            fetch(APP_URL + '/index.php?url=cart/remove', {
                method: 'POST',
                headers: csrfHeaders(),
                body: csrfBody('cart_id=' + cartId)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                }
            });
        });
        return;
    }

    function updateCartBadge(count) {
        var badge = document.querySelector('.cart-count');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
            /* Pop animation */
            badge.classList.remove('pop');
            void badge.offsetWidth;
            badge.classList.add('pop');
        }
    }

    /* ===== Quantity Stepper ===== */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.qty-stepper').forEach(function (stepper) {
            var input = stepper.querySelector('input');
            var minus = stepper.querySelector('.qty-minus');
            var plus = stepper.querySelector('.qty-plus');
            var cartId = stepper.getAttribute('data-cart-id');

            if (minus) minus.addEventListener('click', function () {
                var val = parseInt(input.value, 10) || 1;
                if (val > 1) {
                    input.value = val - 1;
                    if (cartId) updateQuantity(cartId, val - 1);
                }
            });
            if (plus) plus.addEventListener('click', function () {
                var val = parseInt(input.value, 10) || 1;
                var max = parseInt(input.getAttribute('max'), 10) || 999;
                if (val < max) {
                    input.value = val + 1;
                    if (cartId) updateQuantity(cartId, val + 1);
                }
            });
        });
    });

    /* Expose */
    window.addToCart = addToCart;
    window.updateQuantity = updateQuantity;
    window.removeFromCart = removeFromCart;
    window.updateCartBadge = updateCartBadge;

})();
