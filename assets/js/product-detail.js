/**
 * Product detail page — gallery thumbs, tabs, related scroller, lightbox
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initGallery();
        initTabs();
        initRelated();
        initLightbox();
        initSizes();
        initReviews();
    });

    function initSizes() {
        var root = document.querySelector('[data-pd-sizes]');
        if (!root) return;
        var buttons = root.querySelectorAll('[data-pd-size]');
        var priceEl = document.querySelector('[data-pd-price]');
        var stockText = document.querySelector('[data-pd-stock-text]');
        var stockBadge = document.querySelector('[data-pd-stock-badge]');
        var qty = document.querySelector('[data-pd-qty]');
        var addBtn = document.querySelector('[data-pd-add-cart]');

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) { b.classList.remove('is-selected'); });
                btn.classList.add('is-selected');

                var price = btn.getAttribute('data-price') || '0';
                var stock = parseInt(btn.getAttribute('data-stock') || '0', 10);
                var sizeId = btn.getAttribute('data-size-id') || '';
                var label = btn.getAttribute('data-label') || '';

                if (priceEl) priceEl.textContent = Number(price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (stockText) stockText.textContent = stock > 0 ? (stock + ' pcs') : 'Out of stock';
                if (stockBadge) {
                    stockBadge.classList.toggle('pd-stock-badge--in', stock > 0);
                    stockBadge.classList.toggle('pd-stock-badge--out', stock <= 0);
                }
                if (qty) {
                    qty.max = Math.max(1, stock);
                    if (parseInt(qty.value, 10) > stock) qty.value = Math.max(1, stock);
                }
                if (addBtn) {
                    addBtn.setAttribute('data-size-id', sizeId);
                    addBtn.disabled = stock <= 0;
                }

                var specSize = document.querySelector('[data-pd-spec-size]');
                if (specSize && label) specSize.textContent = label;
            });
        });

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var productId = addBtn.getAttribute('data-product-id');
                var sizeId = addBtn.getAttribute('data-size-id') || '';
                var quantity = qty ? qty.value : 1;
                if (typeof addToCart === 'function') {
                    addToCart(productId, quantity, sizeId || null);
                }
            });
        }
    }

    function initGallery() {
        var root = document.querySelector('[data-pd-gallery]');
        if (!root) return;
        var main = root.querySelector('[data-pd-main-img]');
        var thumbs = root.querySelectorAll('[data-pd-thumb]');
        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                var src = thumb.getAttribute('data-src');
                if (!src || !main) return;
                main.src = src;
                thumbs.forEach(function (t) { t.classList.remove('is-active'); });
                thumb.classList.add('is-active');
            });
        });
    }

    function openReviewsTab(expandAll) {
        var tabBtn = document.querySelector('[data-pd-tab="reviews"]');
        if (tabBtn) tabBtn.click();
        if (expandAll) expandAllReviews();
        var tabs = document.querySelector('[data-pd-tabs]');
        if (tabs) tabs.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function expandAllReviews() {
        document.querySelectorAll('.pd-mini-review.is-extra').forEach(function (el) {
            el.hidden = false;
            el.classList.remove('is-extra');
        });
        var btn = document.querySelector('[data-pd-view-all-reviews]');
        if (btn) btn.hidden = true;
    }

    function initReviews() {
        document.querySelectorAll('[data-pd-open-reviews]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                openReviewsTab(true);
            });
        });
        var viewAll = document.querySelector('[data-pd-view-all-reviews]');
        if (viewAll) {
            viewAll.addEventListener('click', function () {
                expandAllReviews();
            });
        }
    }

    function initTabs() {
        var root = document.querySelector('[data-pd-tabs]');
        if (!root) return;
        var buttons = root.querySelectorAll('[data-pd-tab]');
        var panels = root.querySelectorAll('[data-pd-panel]');

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key = btn.getAttribute('data-pd-tab');
                buttons.forEach(function (b) {
                    b.classList.toggle('is-active', b === btn);
                    b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                });
                panels.forEach(function (panel) {
                    var match = panel.getAttribute('data-pd-panel') === key;
                    panel.classList.toggle('is-active', match);
                    if (match) {
                        panel.removeAttribute('hidden');
                    } else {
                        panel.setAttribute('hidden', '');
                    }
                });
            });
        });
    }

    function initRelated() {
        var track = document.querySelector('[data-pd-related-track]');
        var prev = document.querySelector('[data-pd-related-prev]');
        var next = document.querySelector('[data-pd-related-next]');
        var navs = document.querySelector('[data-pd-related-navs]');
        if (!track) return;

        function cardStep() {
            var card = track.querySelector('.pd-related__card');
            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap) || 14;
            return card ? card.getBoundingClientRect().width + gap : 240;
        }

        function updateNav() {
            var max = Math.max(0, track.scrollWidth - track.clientWidth - 1);
            var canScroll = max > 4;
            if (navs) navs.hidden = !canScroll;
            if (prev) prev.disabled = track.scrollLeft <= 2;
            if (next) next.disabled = track.scrollLeft >= max;
        }

        function scrollByCard(dir) {
            track.scrollBy({ left: dir * cardStep(), behavior: 'smooth' });
        }

        if (prev) prev.addEventListener('click', function () { scrollByCard(-1); });
        if (next) next.addEventListener('click', function () { scrollByCard(1); });
        track.addEventListener('scroll', updateNav, { passive: true });
        window.addEventListener('resize', updateNav);
        updateNav();
    }

    function initLightbox() {
        var lightbox = document.querySelector('[data-pd-lightbox]');
        var img = document.querySelector('[data-pd-lightbox-img]');
        var openBtn = document.querySelector('[data-pd-zoom]');
        var closeBtn = document.querySelector('[data-pd-lightbox-close]');
        var main = document.querySelector('[data-pd-main-img]');
        if (!lightbox || !img || !main) return;

        function open() {
            img.src = main.src;
            lightbox.hidden = false;
            document.body.style.overflow = 'hidden';
        }
        function close() {
            lightbox.hidden = true;
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', open);
        if (closeBtn) closeBtn.addEventListener('click', close);
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !lightbox.hidden) close();
        });
    }
})();
