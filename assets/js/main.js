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

        /* ========== Toast Auto-Dismiss ========== */
        document.querySelectorAll('.toast[data-auto-dismiss]').forEach(function (toast) {
            var duration = parseInt(toast.getAttribute('data-auto-dismiss')) || 5000;
            // Set progress bar animation duration
            var bar = toast.querySelector('.toast-progress-bar');
            if (bar) bar.style.animationDuration = duration + 'ms';
            // Close button
            var closeBtn = toast.querySelector('.toast-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    dismissToast(toast);
                });
            }
            // Auto dismiss
            setTimeout(function () { dismissToast(toast); }, duration);
        });

        function dismissToast(el) {
            if (!el || el.classList.contains('toast--leaving')) return;
            el.classList.add('toast--leaving');
            setTimeout(function () { if (el.parentNode) el.remove(); }, 300);
        }

        /* Legacy .alert auto-dismiss (for inline alerts) */
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
                        title: 'Delete user?',
                        message: 'Are you sure you want to delete this user? This action cannot be undone.',
                        confirmText: 'Delete',
                        confirmVariant: 'danger'
                    }).then(function (ok) {
                        if (ok) proceed();
                    });
                } else {
                    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
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

        /* ========== Login Modal (blur overlay like logout) ========== */
        (function initLoginModal() {
            var overlay = document.getElementById('loginModalOverlay');
            if (!overlay) return; // user is logged in, no modal present

            var closeBtn = document.getElementById('loginModalClose');
            var form     = document.getElementById('loginModalForm');
            var errorBox = document.getElementById('loginModalError');
            var submitBtn= document.getElementById('loginModalSubmit');
            var emailIn  = document.getElementById('loginModalEmail');

            // Intercept all "Login" nav links that go to ?url=login
            document.addEventListener('click', function (e) {
                var link = findClosestAnchor(e.target);
                if (!link) return;

                var href = link.getAttribute('href') || '';
                var isLogin = /\burl=login\b/.test(href) && !link.classList.contains('btn');
                if (!isLogin) return;

                e.preventDefault();
                openLoginModal();
            });

            function openLoginModal() {
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                // Render lucide icons inside the modal
                if (window.lucide) lucide.createIcons({ nodes: [overlay] });
                setTimeout(function () { emailIn && emailIn.focus(); }, 100);
            }

            // Auto-open modal when page loads with ?url=login or ?login_modal=1 (used after logout)
            if (overlay && (/\burl=login\b/.test(window.location.search) || /\blogin_modal=1\b/.test(window.location.search))) {
                // small timeout to allow page render and icons to initialize
                setTimeout(openLoginModal, 60);
            }

            function closeLoginModal() {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
                if (errorBox) { errorBox.style.display = 'none'; errorBox.textContent = ''; }
            }

            // Close button
            if (closeBtn) closeBtn.addEventListener('click', closeLoginModal);

            // Click outside modal
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeLoginModal();
            });

            // Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('active')) {
                    closeLoginModal();
                }
            });

            // AJAX form submission
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    var email    = form.querySelector('[name="email"]').value.trim();
                    var password = form.querySelector('[name="password"]').value;
                    var csrf     = form.querySelector('[name="csrf_token"]').value;

                    if (!email || !password) {
                        showLoginError('Please fill in all fields.');
                        return;
                    }

                    // Disable button
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i data-lucide="loader" class="spin"></i> Signing in...';
                        if (window.lucide) lucide.createIcons({ nodes: [submitBtn] });
                    }

                    var body = 'csrf_token=' + encodeURIComponent(csrf)
                             + '&email=' + encodeURIComponent(email)
                             + '&password=' + encodeURIComponent(password);

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: body
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            showLoginError(data.message || 'Invalid email or password.');
                            resetSubmitBtn();
                            if (data.redirect) {
                                setTimeout(function () { window.location.href = data.redirect; }, 1500);
                            }
                        }
                    })
                    .catch(function () {
                        showLoginError('Something went wrong. Please try again.');
                        resetSubmitBtn();
                    });
                });
            }

            function showLoginError(msg) {
                if (!errorBox) return;
                errorBox.textContent = msg;
                errorBox.style.display = 'block';
            }

            function resetSubmitBtn() {
                if (!submitBtn) return;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-lucide="log-in"></i> Sign In';
                if (window.lucide) lucide.createIcons({ nodes: [submitBtn] });
            }
        })();

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

            var iconClass = confirmVariant === 'danger' ? 'confirm-header-icon--danger' : 'confirm-header-icon--info';
            var iconName = confirmVariant === 'danger' ? 'alert-triangle' : 'help-circle';

            dialog.innerHTML =
                '<div class="confirm-header">' +
                    '<div class="confirm-header-icon ' + iconClass + '"><i data-lucide="' + iconName + '"></i></div>' +
                    '<div class="confirm-title">' + escapeHtml(title) + '</div>' +
                    '<button type="button" class="confirm-close" aria-label="Close"><i data-lucide="x"></i></button>' +
                '</div>' +
                '<div class="confirm-body">' + escapeHtml(message) + '</div>' +
                '<div class="confirm-actions">' +
                    '<button type="button" class="btn btn-outline confirm-cancel">' + escapeHtml(cancelText) + '</button>' +
                    '<button type="button" class="btn ' + (confirmVariant === 'danger' ? 'btn-danger' : 'btn-accent') + ' confirm-ok">' + escapeHtml(confirmText) + '</button>' +
                '</div>';

            overlay.appendChild(dialog);
            document.body.appendChild(overlay);

            // Render Lucide icons inside the dialog
            if (window.lucide) lucide.createIcons({ nodes: [dialog] });

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
        /* Toast Notification: modern toast that stacks in top-right container */
        window.showNotification = function (message, type) {
            type = type || 'info';
            var typeMap = {
                success: { cls: 'toast--success', icon: 'check-circle', title: 'Success' },
                error:   { cls: 'toast--error',   icon: 'alert-circle', title: 'Error' },
                warning: { cls: 'toast--warning',  icon: 'alert-triangle', title: 'Warning' },
                info:    { cls: 'toast--info',     icon: 'info',         title: 'Info' }
            };
            var t = typeMap[type] || typeMap.info;
            var duration = type === 'error' ? 6000 : 4000;

            // Ensure container exists
            var container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }

            // Remove previous JS toast if any
            var prev = document.getElementById('site-toast');
            if (prev && prev.parentNode) prev.remove();

            // Build toast element
            var toast = document.createElement('div');
            toast.id = 'site-toast';
            toast.className = 'toast ' + t.cls;
            toast.setAttribute('data-auto-dismiss', duration);
            toast.innerHTML =
                '<div class="toast-icon"><i data-lucide="' + t.icon + '"></i></div>' +
                '<div class="toast-body">' +
                    '<span class="toast-title">' + t.title + '</span>' +
                    '<span class="toast-message">' + message + '</span>' +
                '</div>' +
                '<button class="toast-close" aria-label="Close"><i data-lucide="x"></i></button>' +
                '<div class="toast-progress"><div class="toast-progress-bar" style="animation-duration:' + duration + 'ms"></div></div>';

            container.appendChild(toast);

            // Render Lucide icons inside the new toast
            if (window.lucide) lucide.createIcons({ nodes: [toast] });

            // Close button
            var closeBtn = toast.querySelector('.toast-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    dismissJsToast(toast);
                });
            }

            // Auto dismiss
            if (window._siteToastTimeout) clearTimeout(window._siteToastTimeout);
            window._siteToastTimeout = setTimeout(function () {
                dismissJsToast(toast);
            }, duration);
        };

        function dismissJsToast(el) {
            if (!el || el.classList.contains('toast--leaving')) return;
            el.classList.add('toast--leaving');
            setTimeout(function () {
                if (el && el.parentNode) el.remove();
            }, 300);
            window._siteToastTimeout = null;
        }

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
