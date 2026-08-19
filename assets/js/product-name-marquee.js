(function () {
    'use strict';

    var PX_PER_SEC = 38;
    var MIN_DURATION = 8;

    function prefersReducedMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function resetTrack(el, track) {
        track.querySelectorAll('.product-name-clone').forEach(function (node) {
            node.remove();
        });
        el.classList.remove('is-scrolling');
        track.style.animationDuration = '';
    }

    function setupName(el) {
        var viewport = el.querySelector('.product-name-viewport');
        var track = el.querySelector('.product-name-track');
        var text = el.querySelector('.product-name-text:not(.product-name-clone)');
        if (!viewport || !track || !text) return;

        resetTrack(el, track);

        if (prefersReducedMotion()) return;

        var overflow = text.scrollWidth - viewport.clientWidth;
        if (overflow <= 2) return;

        var clone = text.cloneNode(true);
        clone.classList.add('product-name-clone');
        clone.setAttribute('aria-hidden', 'true');
        track.appendChild(clone);
        el.classList.add('is-scrolling');

        var distance = text.offsetWidth;
        var duration = Math.max(MIN_DURATION, distance / PX_PER_SEC);
        track.style.animationDuration = duration.toFixed(2) + 's';
    }

    function init(root) {
        var scope = root || document;
        scope.querySelectorAll('.product-card .product-name').forEach(setupName);
    }

    var resizeTimer;
    function onResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            init();
        }, 120);
    }

    function boot() {
        init();
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function () { init(); }).catch(function () {});
        }
        window.addEventListener('resize', onResize);
        if (typeof ResizeObserver !== 'undefined') {
            document.querySelectorAll('.product-card .product-name-viewport').forEach(function (vp) {
                var ro = new ResizeObserver(function () {
                    var name = vp.closest('.product-name');
                    if (name) setupName(name);
                });
                ro.observe(vp);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
