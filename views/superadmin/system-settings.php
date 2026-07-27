<?php
$pageTitle = 'System Settings';
$extraCss = ['admin.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';

$general  = $general ?? [
    'items_per_page' => ITEMS_PER_PAGE,
];
$payments = $payments ?? [
    'cod'   => true,
    'gcash' => true,
    'card'  => true,
    'qrph'  => true,
];

$enabledCount = count(array_filter($payments));
$passwordUpdated = isset($_GET['pw']) && (string) $_GET['pw'] === '1';
$paymentMeta = [
    'cod' => [
        'label' => 'Cash on Delivery',
        'desc'  => 'Pay when the order arrives',
        'icon'  => 'banknote',
    ],
    'gcash' => [
        'label' => 'GCash',
        'desc'  => 'PayMongo e-wallet checkout',
        'icon'  => 'smartphone',
    ],
    'card' => [
        'label' => 'Credit / Debit Card',
        'desc'  => 'Visa, Mastercard, JCB, Amex',
        'icon'  => 'credit-card',
    ],
    'qrph' => [
        'label' => 'QRPh',
        'desc'  => 'Scan to pay via InstaPay',
        'icon'  => 'qr-code',
    ],
];
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>System Settings</h2>
        </div>
    </div>

    <div class="page-content ss-page">

        <div class="ss-hero">
            <div class="ss-hero-copy">
                <p class="ss-eyebrow">Configuration</p>
                <h1 class="ss-title">Store preferences</h1>
                <p class="ss-subtitle">Control catalog pagination, checkout payment options, and account security from one place.</p>
            </div>
            <div class="ss-hero-meta">
                <div class="ss-meta-pill">
                    <span class="ss-meta-label">Payments active</span>
                    <strong><?= (int) $enabledCount ?> / 4</strong>
                </div>
                <div class="ss-meta-pill">
                    <span class="ss-meta-label">Catalog page size</span>
                    <strong><?= (int) $general['items_per_page'] ?> items</strong>
                </div>
            </div>
        </div>

        <div class="ss-layout">
            <div class="ss-main">

                <!-- General -->
                <section class="ss-panel">
                    <header class="ss-panel-head">
                        <div class="ss-panel-icon"><i data-lucide="layout-grid"></i></div>
                        <div>
                            <h3>Catalog display</h3>
                            <p>Set how many products appear on each catalog page.</p>
                        </div>
                    </header>
                    <form action="<?= APP_URL ?>/index.php?url=admin/settings/update" method="POST" class="ss-form">
                        <?= csrf_field() ?>
                        <div class="ss-field">
                            <label class="ss-label" for="items_per_page">Items per page</label>
                            <div class="ss-input-row">
                                <input
                                    type="number"
                                    id="items_per_page"
                                    name="items_per_page"
                                    class="ss-input"
                                    value="<?= (int) $general['items_per_page'] ?>"
                                    min="5"
                                    max="100"
                                    required
                                >
                                <span class="ss-input-hint">Allowed range: 5–100</span>
                            </div>
                        </div>
                        <div class="ss-actions">
                            <button type="submit" class="ss-btn ss-btn-primary">
                                <i data-lucide="check"></i>
                                Save changes
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Payments -->
                <section class="ss-panel">
                    <header class="ss-panel-head">
                        <div class="ss-panel-icon"><i data-lucide="wallet"></i></div>
                        <div>
                            <h3>Payment methods</h3>
                            <p>Toggle which options customers can use at checkout. Keep at least one enabled.</p>
                        </div>
                    </header>
                    <form action="<?= APP_URL ?>/index.php?url=admin/settings/payment" method="POST" class="ss-form">
                        <?= csrf_field() ?>
                        <div class="ss-pay-grid">
                            <?php foreach ($paymentMeta as $key => $meta): ?>
                                <?php $on = !empty($payments[$key]); ?>
                                <label class="ss-pay-card<?= $on ? ' is-on' : '' ?>">
                                    <input
                                        type="checkbox"
                                        name="<?= htmlspecialchars($key) ?>_enabled"
                                        value="1"
                                        class="ss-pay-check"
                                        <?= $on ? 'checked' : '' ?>
                                    >
                                    <span class="ss-pay-icon"><i data-lucide="<?= htmlspecialchars($meta['icon']) ?>"></i></span>
                                    <span class="ss-pay-copy">
                                        <strong><?= htmlspecialchars($meta['label']) ?></strong>
                                        <small><?= htmlspecialchars($meta['desc']) ?></small>
                                    </span>
                                    <span class="ss-pay-switch" aria-hidden="true">
                                        <span class="ss-pay-knob"></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="ss-actions">
                            <button type="submit" class="ss-btn ss-btn-primary">
                                <i data-lucide="check"></i>
                                Save payment settings
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Security -->
                <section class="ss-panel" id="account-security">
                    <header class="ss-panel-head">
                        <div class="ss-panel-icon"><i data-lucide="shield"></i></div>
                        <div>
                            <h3>Account security</h3>
                            <p>Update your SuperAdmin password. Use at least 8 characters with mixed case and a number.</p>
                        </div>
                    </header>
                    <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" class="ss-form" id="ss-password-form" autocomplete="off" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="return_url" value="admin/settings">

                        <?php if (!empty($passwordUpdated)): ?>
                        <div class="ss-success-banner" id="ss-password-success" role="status">
                            <div class="ss-success-icon"><i data-lucide="check-circle-2"></i></div>
                            <div>
                                <strong>Password updated</strong>
                                <p>Your new password is active. The form has been cleared for security.</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="ss-field">
                            <label class="ss-label" for="current_password">Current password</label>
                            <div class="ss-pass-wrap">
                                <input type="password" id="current_password" name="current_password" class="ss-input" autocomplete="off" required value="">
                                <button type="button" class="ss-pass-toggle" data-target="current_password" aria-label="Show password">Show</button>
                            </div>
                        </div>

                        <div class="ss-field-grid ss-field-grid-2">
                            <div class="ss-field">
                                <label class="ss-label" for="new_password">New password</label>
                                <div class="ss-pass-wrap">
                                    <input type="password" id="new_password" name="new_password" class="ss-input" autocomplete="new-password" minlength="8" required value="">
                                    <button type="button" class="ss-pass-toggle" data-target="new_password" aria-label="Show password">Show</button>
                                </div>
                                <div class="ss-strength" id="ss-strength" hidden>
                                    <div class="ss-strength-bar"><span id="ss-strength-fill"></span></div>
                                    <span class="ss-strength-label" id="ss-strength-label">Strength</span>
                                </div>
                                <ul class="ss-req-list" id="ss-req-list" aria-live="polite">
                                    <li data-req="length">At least 8 characters</li>
                                    <li data-req="lower">One lowercase letter</li>
                                    <li data-req="upper">One uppercase letter</li>
                                    <li data-req="number">One number</li>
                                </ul>
                            </div>
                            <div class="ss-field">
                                <label class="ss-label" for="confirm_password">Confirm new password</label>
                                <div class="ss-pass-wrap">
                                    <input type="password" id="confirm_password" name="confirm_password" class="ss-input" autocomplete="new-password" minlength="8" required value="">
                                    <button type="button" class="ss-pass-toggle" data-target="confirm_password" aria-label="Show password">Show</button>
                                </div>
                                <p class="ss-match" id="ss-match" hidden></p>
                            </div>
                        </div>

                        <div class="ss-actions">
                            <button type="submit" class="ss-btn ss-btn-primary" id="ss-password-submit">
                                <i data-lucide="key-round"></i>
                                Update password
                            </button>
                        </div>
                    </form>
                </section>

            </div>

            <aside class="ss-aside">
                <section class="ss-panel ss-panel-aside">
                    <header class="ss-panel-head">
                        <div class="ss-panel-icon ss-panel-icon-muted"><i data-lucide="server"></i></div>
                        <div>
                            <h3>Environment</h3>
                            <p>Read-only system details</p>
                        </div>
                    </header>
                    <ul class="ss-info-list">
                        <li>
                            <span>PHP</span>
                            <strong><?= htmlspecialchars(phpversion()) ?></strong>
                        </li>
                        <li>
                            <span>Server</span>
                            <strong><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') ?></strong>
                        </li>
                        <li>
                            <span>Database</span>
                            <strong>MySQL / MariaDB</strong>
                        </li>
                        <li>
                            <span>App version</span>
                            <strong><?= htmlspecialchars(defined('APP_VERSION') ? APP_VERSION : '1.0.0') ?></strong>
                        </li>
                    </ul>
                </section>

                <section class="ss-note">
                    <div class="ss-note-icon"><i data-lucide="info"></i></div>
                    <div>
                        <strong>Tip</strong>
                        <p>Disabled payment methods are hidden from checkout immediately after you save.</p>
                    </div>
                </section>
            </aside>
        </div>

    </div>
</div>

<script>
document.querySelectorAll('.ss-pay-card').forEach(function (card) {
    var input = card.querySelector('.ss-pay-check');
    if (!input) return;
    input.addEventListener('change', function () {
        card.classList.toggle('is-on', input.checked);
    });
});

(function () {
    var form = document.getElementById('ss-password-form');
    if (!form) return;

    var current = document.getElementById('current_password');
    var next = document.getElementById('new_password');
    var confirm = document.getElementById('confirm_password');
    var strengthWrap = document.getElementById('ss-strength');
    var strengthFill = document.getElementById('ss-strength-fill');
    var strengthLabel = document.getElementById('ss-strength-label');
    var matchEl = document.getElementById('ss-match');
    var reqList = document.getElementById('ss-req-list');

    document.querySelectorAll('.ss-pass-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-target');
            var input = document.getElementById(id);
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    function scorePassword(value) {
        var score = 0;
        if (value.length >= 8) score += 1;
        if (value.length >= 12) score += 1;
        if (/[a-z]/.test(value)) score += 1;
        if (/[A-Z]/.test(value)) score += 1;
        if (/[0-9]/.test(value)) score += 1;
        if (/[^A-Za-z0-9]/.test(value)) score += 1;
        return score;
    }

    function updateRequirements(value) {
        if (!reqList) return;
        var checks = {
            length: value.length >= 8,
            lower: /[a-z]/.test(value),
            upper: /[A-Z]/.test(value),
            number: /[0-9]/.test(value)
        };
        reqList.querySelectorAll('[data-req]').forEach(function (li) {
            var key = li.getAttribute('data-req');
            li.classList.toggle('is-met', !!checks[key]);
        });
        return checks.length && checks.lower && checks.upper && checks.number;
    }

    function updateStrength() {
        var value = next.value || '';
        var valid = updateRequirements(value);

        if (!value) {
            strengthWrap.hidden = true;
            strengthFill.style.width = '0%';
            strengthWrap.className = 'ss-strength';
            return valid;
        }

        strengthWrap.hidden = false;
        var score = scorePassword(value);
        var level = 'weak';
        var width = '33%';
        var label = 'Weak';

        if (score >= 5) {
            level = 'strong';
            width = '100%';
            label = 'Strong';
        } else if (score >= 3) {
            level = 'fair';
            width = '66%';
            label = 'Fair';
        }

        strengthWrap.className = 'ss-strength is-' + level;
        strengthFill.style.width = width;
        strengthLabel.textContent = label;
        return valid;
    }

    function updateMatch() {
        var a = next.value || '';
        var b = confirm.value || '';
        if (!b) {
            matchEl.hidden = true;
            matchEl.className = 'ss-match';
            matchEl.textContent = '';
            return false;
        }
        matchEl.hidden = false;
        if (a === b) {
            matchEl.className = 'ss-match is-ok';
            matchEl.textContent = 'Passwords match';
            return true;
        }
        matchEl.className = 'ss-match is-bad';
        matchEl.textContent = 'Passwords do not match';
        return false;
    }

    next.addEventListener('input', function () {
        updateStrength();
        updateMatch();
    });
    confirm.addEventListener('input', updateMatch);

    form.addEventListener('submit', function (e) {
        var validRules = updateStrength();
        var matched = updateMatch();
        var sameAsCurrent = current.value !== '' && current.value === next.value;

        if (!current.value || !next.value || !confirm.value) {
            e.preventDefault();
            return;
        }
        if (!validRules) {
            e.preventDefault();
            strengthWrap.hidden = false;
            return;
        }
        if (!matched) {
            e.preventDefault();
            return;
        }
        if (sameAsCurrent) {
            e.preventDefault();
            matchEl.hidden = false;
            matchEl.className = 'ss-match is-bad';
            matchEl.textContent = 'New password must be different from current password';
        }
    });

    function clearPasswordForm() {
        current.value = '';
        next.value = '';
        confirm.value = '';
        current.type = 'password';
        next.type = 'password';
        confirm.type = 'password';
        document.querySelectorAll('.ss-pass-toggle').forEach(function (btn) {
            btn.textContent = 'Show';
            btn.setAttribute('aria-label', 'Show password');
        });
        if (strengthWrap) {
            strengthWrap.hidden = true;
            strengthWrap.className = 'ss-strength';
        }
        if (strengthFill) strengthFill.style.width = '0%';
        if (matchEl) {
            matchEl.hidden = true;
            matchEl.textContent = '';
            matchEl.className = 'ss-match';
        }
        if (reqList) {
            reqList.querySelectorAll('[data-req]').forEach(function (li) {
                li.classList.remove('is-met');
            });
        }
    }

    <?php if (!empty($passwordUpdated)): ?>
    clearPasswordForm();
    // Beat browser autofill that may refill after paint
    setTimeout(clearPasswordForm, 50);
    setTimeout(clearPasswordForm, 300);
    var securityPanel = document.getElementById('account-security');
    if (securityPanel) {
        securityPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    // Clean URL so refresh does not keep showing the banner forever
    if (window.history && window.history.replaceState) {
        var cleanUrl = window.location.href.replace(/([?&])pw=1(&)?/, function (_, a, b) {
            return b ? a : '';
        }).replace(/[?&]$/, '');
        window.history.replaceState({}, document.title, cleanUrl);
    }
    <?php endif; ?>
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
