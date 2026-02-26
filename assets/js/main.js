/**
 * SouthDev Home Depot – Main JavaScript
 * Enterprise UI interactions, animations, and utilities
 */

(function () {
    'use strict';

    // Debug wrappers removed in production.
    /* ========== CSRF Token ========== */
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const CSRF_TOKEN = csrfMeta ? csrfMeta.content : '';

    function csrfHeaders() {
        return {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': CSRF_TOKEN
        };
    }

    function csrfBody(data) {
        const token = `csrf_token=${encodeURIComponent(CSRF_TOKEN)}`;
        return data ? `${token}&${data}` : token;
    }

    /* Expose globally */
    window.CSRF_TOKEN = CSRF_TOKEN;
    window.csrfHeaders = csrfHeaders;
    window.csrfBody = csrfBody;

    /* ========== Page Fade-In ========== */
    document.body.style.opacity = '0';
    document.addEventListener('DOMContentLoaded', function () {
        requestAnimationFrame(() => {
            document.body.style.transition = 'opacity .35s ease';
            document.body.style.opacity = '1';
        });
    });

    /* ========== Sidebar Toggle ========== */
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.querySelector('.sidebar');
        const toggles = document.querySelectorAll('.sidebar-toggle, .sidebar-toggle-btn, #sidebarToggleTop');
        const backdrop = document.querySelector('.sidebar-backdrop');

        if (sidebar && toggles.length) {
            toggles.forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    /* Desktop: collapse/expand | Mobile: overlay */
                    if (window.innerWidth > 992) {
                        sidebar.classList.toggle('collapsed');
                        localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
                    } else {
                        sidebar.classList.toggle('mobile-open');
                        if (backdrop) backdrop.classList.toggle('active');
                    }
                });
            });

            /* Restore desktop sidebar preference */
            if (window.innerWidth > 992 && localStorage.getItem('sidebar-collapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
        }

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                if (!sidebar) return;
                sidebar.classList.remove('mobile-open');
                backdrop.classList.remove('active');
            });
        }

        /* ========== Mobile Nav Toggle ========== */
        const mobileToggle = document.querySelector('.mobile-toggle');
        const navLinks = document.querySelector('.nav-links');
        if (mobileToggle && navLinks) {
            mobileToggle.addEventListener('click', function () {
                navLinks.classList.toggle('mobile-open');
            });
        }

        /* ========== Alert Auto-Dismiss ========== */
        document.querySelectorAll('.alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity .3s, transform .3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-8px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        /* ========== Delete Confirmations ========== */
        document.querySelectorAll('.btn-delete, .action-btn.delete').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                var proceed = function () {
                    // Anchor
                    if (btn.tagName === 'A' && btn.href) {
                        window.location.href = btn.href;
                        return;
                    }
                    // Submit button inside a form
                    var form = btn.closest('form');
                    if (form) {
                        form.submit();
                    }
                };

                if (typeof window.confirmDialog === 'function') {
                    window.confirmDialog({
                        title: 'Delete item?',
                        message: 'Are you sure you want to delete this item? This action cannot be undone.',
                        confirmText: 'Delete',
                        confirmVariant: 'danger'
                    }).then(function (ok) {
                        if (ok) proceed();
                    });
                } else {
                    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                        proceed();
                    }
                }
            });
        });

        /* ========== Cancel Reason (Other) Toggle ========== */
        document.querySelectorAll('.js-cancel-reason-select').forEach(function (select) {
            var form = select.closest('form');
            if (!form) return;

            var otherWrap = form.querySelector('.js-cancel-reason-other');
            var otherInput = otherWrap ? otherWrap.querySelector('textarea, input') : null;

            var sync = function () {
                var isOther = select.value === 'other';
                if (otherWrap) otherWrap.style.display = isOther ? '' : 'none';
                if (otherInput) {
                    otherInput.required = isOther;
                    if (!isOther) otherInput.value = '';
                }
            };

            select.addEventListener('change', sync);
            sync();
        });

        /* ========== Cancel / Cancel Request Confirmations ========== */
        document.querySelectorAll('form.js-cancel-order-form, form.js-cancel-request-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                // If confirm dialog already handled submission, don't intercept.
                if (form.dataset.confirmed === '1') return;

                e.preventDefault();

                var isRequest = form.classList.contains('js-cancel-request-form');
                var title = isRequest ? 'Submit cancellation request?' : 'Cancel order?';
                var message = isRequest
                    ? 'Your request will be reviewed by staff/admin.'
                    : 'This will cancel your order and restore stock.';
                var confirmText = isRequest ? 'Submit request' : 'Cancel order';

                var proceed = function () {
                    form.dataset.confirmed = '1';
                    form.submit();
                };

                if (typeof window.confirmDialog === 'function') {
                    window.confirmDialog({
                        title: title,
                        message: message,
                        confirmText: confirmText,
                        cancelText: 'Back',
                        confirmVariant: 'danger'
                    }).then(function (ok) {
                        if (ok) proceed();
                        else delete form.dataset.confirmed;
                    });
                } else {
                    if (confirm(title + "\n\n" + message)) proceed();
                    else delete form.dataset.confirmed;
                }
            });
        });

        /* ========== Logout Confirmation ========== */
        document.addEventListener('click', function (e) {
            var link = findClosestAnchor(e.target);
            if (!link) return;

            var href = link.getAttribute('href') || '';
            var isLogout = link.classList.contains('nav-logout')
                || link.classList.contains('sidebar-logout')
                || /\burl=logout\b/.test(href)
                || /\/(?:index\.php)?\?url=logout\b/.test(href)
                || /\/logout\b/.test(href);

            if (!isLogout) return;

            e.preventDefault();

            var go = function () {
                window.location.href = href;
            };

            if (typeof window.confirmDialog === 'function') {
                window.confirmDialog({
                    title: 'Log out?',
                    message: 'Are you sure you want to log out?',
                    confirmText: 'Log out',
                    cancelText: 'Cancel',
                    confirmVariant: 'danger'
                }).then(function (ok) {
                    if (ok) go();
                });
            } else {
                if (confirm('Are you sure you want to log out?')) go();
            }
        });

        /* ========== Button Ripple Effect ========== */
        document.querySelectorAll('.btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                btn.style.setProperty('--ripple-x', x + 'px');
                btn.style.setProperty('--ripple-y', y + 'px');
                btn.classList.remove('ripple');
                void btn.offsetWidth; /* trigger reflow */
                btn.classList.add('ripple');
            });
        });

        /* ========== Animated Counters ========== */
        initAnimatedCounters();

        /* ========== Modals ========== */
        initModals();
    });

    /* ========== Themed Confirm Dialog ========== */
    window.confirmDialog = function (opts) {
        opts = opts || {};

        var title = opts.title || 'Confirm';
        var message = opts.message || 'Are you sure?';
        var confirmText = opts.confirmText || 'OK';
        var cancelText = opts.cancelText || 'Cancel';
        var confirmVariant = opts.confirmVariant || 'accent'; // accent | danger

        return new Promise(function (resolve) {
            var overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.tabIndex = -1;

            var dialog = document.createElement('div');
            dialog.className = 'confirm-dialog';

            dialog.innerHTML =
                '<div class="confirm-header">' +
                    '<div class="confirm-title">' + escapeHtml(title) + '</div>' +
                    '<button type="button" class="confirm-close" aria-label="Close">&times;</button>' +
                '</div>' +
                '<div class="confirm-body">' + escapeHtml(message) + '</div>' +
                '<div class="confirm-actions">' +
                    '<button type="button" class="btn btn-outline confirm-cancel">' + escapeHtml(cancelText) + '</button>' +
                    '<button type="button" class="btn ' + (confirmVariant === 'danger' ? 'btn-danger' : 'btn-accent') + ' confirm-ok">' + escapeHtml(confirmText) + '</button>' +
                '</div>';

            overlay.appendChild(dialog);
            document.body.appendChild(overlay);

            // Prevent background scroll
            var prevOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';

            var cleanup = function (result) {
                document.body.style.overflow = prevOverflow;
                overlay.remove();
                resolve(result);
            };

            var cancelBtn = dialog.querySelector('.confirm-cancel');
            var okBtn = dialog.querySelector('.confirm-ok');
            var closeBtn = dialog.querySelector('.confirm-close');

            cancelBtn.addEventListener('click', function () { cleanup(false); });
            closeBtn.addEventListener('click', function () { cleanup(false); });
            okBtn.addEventListener('click', function () { cleanup(true); });

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) cleanup(false);
            });

            var onKeydown = function (e) {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', onKeydown);
                    cleanup(false);
                }
            };
            document.addEventListener('keydown', onKeydown);

            // Focus
            setTimeout(function () {
                okBtn.focus();
            }, 0);
        });
    };

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function findClosestAnchor(node) {
        var el = node;
        while (el && el !== document && el !== document.documentElement) {
            if (el.tagName && el.tagName.toLowerCase() === 'a') return el;
            if (el.parentNode) {
                el = el.parentNode;
            } else {
                break;
            }
        }
        return null;
    }

    /* ---------- Animated Counters ---------- */
    function initAnimatedCounters() {
        const counters = document.querySelectorAll('[data-counter], [data-target], [data-count]');
        if (!counters.length) return;

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        counters.forEach(c => observer.observe(c));
    }

    function animateCounter(el) {
        const raw = el.getAttribute('data-counter') ?? el.getAttribute('data-target') ?? el.getAttribute('data-count');
        const target = parseInt(raw, 10);
        const prefix = el.getAttribute('data-prefix') || '';
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 1200;
        const steps = 40;
        const stepDuration = duration / steps;
        let current = 0;

        if (Number.isNaN(target)) return;

        const timer = setInterval(function () {
            current += Math.ceil(target / steps);
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = prefix + current.toLocaleString() + suffix;
        }, stepDuration);
    }

    /* ---------- Modal System ---------- */
    function initModals() {
        /* Open modal */
        document.querySelectorAll('[data-modal]').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.getAttribute('data-modal');
                const overlay = document.getElementById(id);
                if (overlay) overlay.classList.add('active');
            });
        });

        /* Close modal */
        document.querySelectorAll('.modal-close, .modal-overlay').forEach(function (el) {
            el.addEventListener('click', function (e) {
                if (e.target === this) {
                    this.closest('.modal-overlay').classList.remove('active');
                }
            });
        });

        /* Escape key closes */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
            }
        });
    }

    /* ========== Utilities ========== */
    window.formatCurrency = function (amount) {
        return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };

    window.showLoading = function () {
        const ol = document.getElementById('loading-overlay');
        if (ol) ol.style.display = 'flex';
    };

    window.hideLoading = function () {
        const ol = document.getElementById('loading-overlay');
        if (ol) ol.style.display = 'none';
    };

    /* Toast Notification */
        /* Toast Notification: single reusable toast that replaces previous one */
        window.showNotification = function (message, type) {
            type = type || 'info';
            const typeClass = type === 'success' ? 'alert-success'
                : type === 'error' ? 'alert-danger'
                    : type === 'warning' ? 'alert-warning' : 'alert-info';

            var toast = document.getElementById('site-toast');
            var isNew = false;
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'site-toast';
                isNew = true;
            }

            toast.className = 'alert ' + typeClass;
            toast.textContent = message;

            // Position toast centered below the navbar if present, otherwise top center
            const nav = document.querySelector('.navbar');
            var topPos = 20;
            if (nav) {
                var rect = nav.getBoundingClientRect();
                topPos = Math.max(12, rect.bottom + 12 + window.scrollY);
            }

            toast.style.position = 'fixed';
            toast.style.left = '50%';
            toast.style.top = topPos + 'px';
            toast.style.right = 'auto';
            toast.style.zIndex = '9999';
            toast.style.minWidth = '280px';
            toast.style.maxWidth = '90%';
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 10px 30px rgba(0,0,0,.12)';

            // Use inline transition for opacity & translateY so translateX(-50%) remains applied
            toast.style.animation = 'none';
            toast.style.transition = 'opacity .25s ease, transform .25s ease';
            // start slightly up and transparent
            toast.style.transform = 'translateX(-50%) translateY(-8px)';
            toast.style.opacity = '0';

            if (isNew) document.body.appendChild(toast);

            // trigger enter transition on next frame so transition animates
            requestAnimationFrame(function () {
                toast.style.transform = 'translateX(-50%) translateY(0)';
                toast.style.opacity = '1';
            });

            // Reset any existing removal timer and set a new one
            if (window._siteToastTimeout) {
                clearTimeout(window._siteToastTimeout);
                window._siteToastTimeout = null;
            }

            window._siteToastTimeout = setTimeout(function () {
                if (!toast) return;
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(-8px)';
                setTimeout(function () {
                    if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
                    window._siteToastTimeout = null;
                }, 300);
            }, 3500);
    };

    /* ========== Dropdown Menus ========== */
    document.addEventListener('click', function (e) {
        const toggle = e.target.closest('[data-dropdown]');
        if (toggle) {
            e.preventDefault();
            const menu = toggle.nextElementSibling;
            if (menu) menu.classList.toggle('open');
        } else {
            document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
        }
    });

})();
