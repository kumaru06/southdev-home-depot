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
        var priceEl = document.querySelector('[data-pd-price]');
        var stockText = document.querySelector('[data-pd-stock-text]');
        var stockBadge = document.querySelector('[data-pd-stock-badge]');
        var qty = document.querySelector('[data-pd-qty]');
        var addBtn = document.querySelector('[data-pd-add-cart]');

        // Size picker is optional — products without sizes must still add to cart
        if (root) {
            if (addBtn) {
                addBtn.setAttribute('data-size-id', '');
                addBtn.disabled = false;
            }
            if (qty) {
                qty.max = 1;
                qty.value = 1;
            }
            var buttons = root.querySelectorAll('[data-pd-size]');
            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    buttons.forEach(function (b) { b.classList.remove('is-selected'); });
                    btn.classList.add('is-selected');

                    var price = btn.getAttribute('data-price') || '0';
                    var stock = parseInt(btn.getAttribute('data-stock') || '0', 10);
                    var sizeId = btn.getAttribute('data-size-id') || '';
                    var label = btn.getAttribute('data-label') || '';

                    if (priceEl) priceEl.textContent = Number(price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    if (stockText) stockText.textContent = stock > 0 ? (stock + ' stock') : 'Out of stock';
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
        }

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var requiresSize = addBtn.getAttribute('data-requires-size') === '1' || !!root;
                var productId = addBtn.getAttribute('data-product-id');
                var sizeId = (addBtn.getAttribute('data-size-id') || '').trim();
                var quantity = qty ? qty.value : 1;

                if (requiresSize && (!sizeId || sizeId === '0')) {
                    if (typeof showNotification === 'function') {
                        showNotification('Please select a size first', 'warning');
                    } else {
                        alert('Please select a size first');
                    }
                    if (root) {
                        root.classList.add('pd-sizes--need-select');
                        root.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        setTimeout(function () {
                            root.classList.remove('pd-sizes--need-select');
                        }, 1600);
                    }
                    return;
                }

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
        var track = root.querySelector('[data-pd-thumbs-track]');
        var prevBtn = root.querySelector('[data-pd-thumbs-prev]');
        var nextBtn = root.querySelector('[data-pd-thumbs-next]');
        var viewport = root.querySelector('.pd-thumbs-viewport');
        var perPage = 4;
        var offset = 0;
        var gap = 12;

        function layoutThumbs() {
            if (!viewport || !thumbs.length) return 0;
            var vpWidth = viewport.getBoundingClientRect().width;
            var visible = Math.min(perPage, thumbs.length);
            var thumbW = (vpWidth - gap * (visible - 1)) / visible;
            thumbs.forEach(function (t) {
                t.style.flexBasis = thumbW + 'px';
                t.style.width = thumbW + 'px';
                t.style.maxWidth = thumbW + 'px';
            });
            return thumbW + gap;
        }

        function maxOffset() {
            return Math.max(0, thumbs.length - perPage);
        }

        function applyCarousel() {
            if (!track) return;
            var step = layoutThumbs();
            if (thumbs.length <= perPage) {
                track.style.transform = '';
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = true;
                return;
            }
            track.style.transform = 'translateX(-' + (offset * step) + 'px)';
            if (prevBtn) prevBtn.disabled = offset <= 0;
            if (nextBtn) nextBtn.disabled = offset >= maxOffset();
        }

        function ensureVisible(index) {
            if (thumbs.length <= perPage) return;
            if (index < offset) offset = index;
            else if (index > offset + perPage - 1) offset = index - (perPage - 1);
            applyCarousel();
        }

        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', function () {
                offset = Math.max(0, offset - 1);
                applyCarousel();
            });
            nextBtn.addEventListener('click', function () {
                offset = Math.min(maxOffset(), offset + 1);
                applyCarousel();
            });
        }
        if (track) {
            window.addEventListener('resize', applyCarousel);
            applyCarousel();
        }

        thumbs.forEach(function (thumb, index) {
            thumb.addEventListener('click', function () {
                var src = thumb.getAttribute('data-src');
                if (!src || !main) return;
                main.src = src;
                thumbs.forEach(function (t) { t.classList.remove('is-active'); });
                thumb.classList.add('is-active');
                ensureVisible(index);
            });
        });
    }

    function openReviewsTab() {
        var tabBtn = document.querySelector('[data-pd-tab="reviews"]');
        if (tabBtn) tabBtn.click();
        var tabs = document.querySelector('[data-pd-tabs]');
        if (tabs) tabs.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function initReviews() {
        document.querySelectorAll('[data-pd-open-reviews]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                openReviewsTab();
            });
        });
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
