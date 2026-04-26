<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$profileImage = $user['profile_image'] ?? '';
$profileImageUrl = $profileImage ? (APP_URL . '/assets/uploads/profiles/' . rawurlencode($profileImage)) : '';
$initials = strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1));
$fullName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
$fullName = $fullName !== '' ? $fullName : 'Your Profile';
$addressReady = trim((string) ($user['address'] ?? '')) !== '' || trim((string) ($user['city'] ?? '')) !== '' || trim((string) ($user['state'] ?? '')) !== '' || trim((string) ($user['zip_code'] ?? '')) !== '';
$contactReady = trim((string) ($user['email'] ?? '')) !== '' && trim((string) ($user['phone'] ?? '')) !== '';
$profileFields = [
    $user['first_name'] ?? '',
    $user['last_name'] ?? '',
    $user['email'] ?? '',
    $user['phone'] ?? '',
    $user['address'] ?? '',
    $user['city'] ?? '',
    $user['state'] ?? '',
    $user['zip_code'] ?? '',
];
if (!empty($usernameEnabled)) {
    $profileFields[] = $user['username'] ?? '';
}
$completedProfileFields = count(array_filter($profileFields, static fn($value) => trim((string) $value) !== ''));
$profileCompletion = (int) round(($completedProfileFields / max(count($profileFields), 1)) * 100);
$locationLabel = trim((string) (($user['city'] ?? '') . (($user['city'] ?? '') && ($user['state'] ?? '') ? ', ' : '') . ($user['state'] ?? '')));
$locationLabel = $locationLabel !== '' ? $locationLabel : 'No delivery area saved yet';
?>

<div class="container profile-page">
    <div class="profile-hero">
        <div class="profile-hero-bg"></div>
        <div class="profile-hero-content">
            <div class="profile-hero-main">
                <div class="profile-hero-avatar">
                    <div class="profile-photo profile-photo--lg">
                        <?php if (!empty($profileImage)): ?>
                            <img src="<?= $profileImageUrl ?>" alt="Profile photo" class="profile-avatar-img">
                        <?php else: ?>
                            <div class="profile-avatar-fallback" aria-label="Profile initials"><?= htmlspecialchars($initials) ?></div>
                        <?php endif; ?>
                    </div>
                    <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" enctype="multipart/form-data" id="photo-form" class="profile-photo-upload-btn">
                        <?= csrf_field() ?>
                        <input type="hidden" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                        <input type="hidden" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        <?php if (!empty($usernameEnabled)): ?>
                        <input type="hidden" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                        <?php endif; ?>
                        <input type="hidden" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        <input type="hidden" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        <input type="hidden" name="state" value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                        <input type="hidden" name="zip_code" value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>">
                        <label class="profile-camera-btn" title="Change photo">
                            &#128247;
                            <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="this.form.submit()">
                        </label>
                    </form>
                </div>
                <div class="profile-hero-info">
                    <span class="profile-hero-kicker">Customer profile</span>
                    <h1 class="profile-hero-name"><?= htmlspecialchars($fullName) ?></h1>
                    <div class="profile-hero-meta">
                        <?php if (!empty($user['username'])): ?>
                            <span class="profile-meta-tag">@<?= htmlspecialchars($user['username']) ?></span>
                        <?php endif; ?>
                        <span class="profile-meta-tag">
                            <?= htmlspecialchars($user['email']) ?>
                            <?php if (!empty($user['email_verified_at'])): ?>
                                <span class="profile-email-badge profile-email-badge--verified" title="Email verified">&#10003; Verified</span>
                            <?php else: ?>
                                <span class="profile-email-badge profile-email-badge--unverified" title="Email not verified">&#10007; Unverified</span>
                            <?php endif; ?>
                        </span>
                        <?php if (!empty($user['phone'])): ?>
                            <span class="profile-meta-tag"><?= htmlspecialchars($user['phone']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="profile-hero-badges">
                        <span class="profile-hero-badge <?= $contactReady ? 'is-ready' : '' ?>">
                            <span class="profile-status-dot"></span>
                            <?= $contactReady ? 'Contact ready' : 'Add a phone number' ?>
                        </span>
                        <span class="profile-hero-badge <?= $addressReady ? 'is-ready' : '' ?>">
                            <span class="profile-status-dot"></span>
                            <?= $addressReady ? 'Delivery address saved' : 'Set your delivery address' ?>
                        </span>
                    </div>
                    <div class="profile-hero-actions" aria-label="Quick profile actions">
                        <button type="button" class="profile-hero-action is-primary" data-profile-jump="personal-section">Edit profile</button>
                        <button type="button" class="profile-hero-action" data-profile-jump="address-section">Update address</button>
                    </div>
                </div>
            </div>

            <div class="profile-hero-panels">
                <div class="profile-overview-card">
                    <div class="profile-ring-row">
                        <?php $circ = 289.03; $ringOffset = round($circ * (1 - $profileCompletion / 100), 2); ?>
                        <div class="profile-ring-wrap">
                            <svg class="profile-ring-svg" viewBox="0 0 120 120" aria-hidden="true">
                                <defs>
                                    <linearGradient id="profileRingGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#f97316"/>
                                        <stop offset="100%" stop-color="#fb923c"/>
                                    </linearGradient>
                                </defs>
                                <circle cx="60" cy="60" r="46" class="profile-ring-track"/>
                                <circle cx="60" cy="60" r="46" class="profile-ring-fill"
                                        stroke-dasharray="<?= $circ ?>"
                                        stroke-dashoffset="<?= $ringOffset ?>"/>
                            </svg>
                            <span class="profile-ring-pct"><?= $profileCompletion ?>%</span>
                        </div>
                        <div class="profile-ring-info">
                            <span class="profile-overview-label">Profile</span>
                            <p class="profile-ring-title">Completion</p>
                            <p class="profile-ring-desc">Complete personal &amp; delivery details for a smoother checkout.</p>
                        </div>
                    </div>
                </div>
                <div class="profile-overview-grid" aria-label="Profile overview">
                    <div class="profile-overview-item">
                        <strong><?= $contactReady ? 'Ready' : 'Pending' ?></strong>
                        <span>Contact setup</span>
                    </div>
                    <div class="profile-overview-item">
                        <strong><?= $addressReady ? 'Saved' : 'Pending' ?></strong>
                        <span>Address book</span>
                    </div>
                </div>
                <div class="profile-delivery-card">
                    <span>Default delivery area</span>
                    <strong><?= htmlspecialchars($locationLabel) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-nav-shell">
        <div class="profile-nav-copy">
            <span class="profile-nav-kicker">Account workspace</span>
            <h2>Manage every detail in one polished place.</h2>
        </div>
        <div class="profile-nav-tabs" role="tablist" aria-label="Profile sections">
            <button class="profile-tab active" data-target="personal-section" type="button">Personal Info</button>
            <button class="profile-tab" data-target="address-section" type="button">Address</button>
            <button class="profile-tab" data-target="security-section" type="button">Security</button>
        </div>
    </div>

    <div class="profile-content-grid">
        <div class="profile-main-column">

            <div class="profile-sections">
                <!-- Personal Information -->
                <div class="profile-section-block" id="personal-section">
                    <div class="card profile-section-card">
                        <div class="profile-section-header">
                            <div>
                                <span class="profile-section-chip">Core details</span>
                                <h3>Personal Information</h3>
                                <p>Update your name, username, and contact details.</p>
                            </div>
                        </div>
                        <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="profile-form">
                            <?= csrf_field() ?>
                            <!-- Carry address fields as hidden so they don't get blanked -->
                            <input type="hidden" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                            <input type="hidden" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                            <input type="hidden" name="state" value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                            <input type="hidden" name="zip_code" value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>">

                            <div class="form-row">
                                <div class="form-group form-col">
                                    <label for="first_name">First Name <span class="required">*</span></label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                </div>
                                <div class="form-group form-col">
                                    <label for="last_name">Last Name <span class="required">*</span></label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                </div>
                            </div>

                            <?php if (!empty($usernameEnabled)): ?>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <div class="input-icon-wrap">
                                    <span class="input-icon-prefix">@</span>
                                    <input type="text" id="username" name="username" class="form-control form-control--prefixed" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                                </div>
                                <small class="text-muted">Letters, numbers, underscore, dash or dot (3–30 chars).</small>
                            </div>
                            <?php endif; ?>

                            <div class="form-row">
                                <div class="form-group form-col">
                                    <label for="email">Email <span class="required">*</span></label>
                                    <div class="input-with-action">
                                        <input type="text" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required readonly aria-readonly="true">
                                        <button type="button" id="change-email-btn" class="btn btn-outline small">Change</button>
                                    </div>
                                    <small class="text-muted">Email updates require verification via an OTP sent to your current email.</small>
                                </div>
                                <div class="form-group form-col">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="e.g. 09123456789">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-accent">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="profile-section-block" id="address-section">
                    <div class="card profile-section-card">
                        <div class="profile-section-header">
                            <div>
                                <span class="profile-section-chip">Delivery setup</span>
                                <h3>Shipping Address</h3>
                                <p>Manage your default delivery address.</p>
                                <p class="profile-address-hint">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;flex-shrink:0;color:#16a34a;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Save once here &mdash; we&rsquo;ll auto-fill it every time you check out.
                                </p>
                            </div>
                        </div>
                        <?php
                        $returnAfterSave = '';
                        $rawReturn = trim((string)($_GET['return'] ?? ''));
                        if (preg_match('/^[a-z0-9\-\/]+$/i', $rawReturn) && !str_starts_with($rawReturn, '/') && !str_contains($rawReturn, '..')) {
                            $returnAfterSave = $rawReturn;
                        }
                        ?>
                        <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="address-form">
                            <?= csrf_field() ?>
                            <?php if ($returnAfterSave !== ''): ?>
                            <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnAfterSave) ?>">
                            <?php endif; ?>
                            <!-- Carry personal info fields as hidden so they don't get blanked -->
                            <input type="hidden" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>">
                            <input type="hidden" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                            <?php if (!empty($usernameEnabled)): ?>
                            <input type="hidden" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                            <?php endif; ?>
                            <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">

                            <div class="form-group">
                                <label for="address">Street Address</label>
                                <textarea id="address" name="address" class="form-control" rows="2" placeholder="House/Unit No., Street, Barangay"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group form-col">
                                    <label for="city">City / Municipality</label>
                                    <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="e.g. Davao">
                                </div>
                                <div class="form-group form-col">
                                    <label for="state">Province</label>
                                    <input type="text" id="state" name="state" class="form-control" value="<?= htmlspecialchars($user['state'] ?? '') ?> " placeholder="e.g. Davao Del Sur">
                                </div>
                                <div class="form-group form-col">
                                    <label for="zip_code">Zip Code</label>
                                    <input type="text" id="zip_code" name="zip_code" class="form-control" value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>" placeholder="e.g. 1000">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-accent">Save Address</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security -->
                <div class="profile-section-block" id="security-section">
                    <div class="card profile-section-card">
                        <div class="profile-section-header">
                            <div>
                                <span class="profile-section-chip">Protection</span>
                                <h3>Security</h3>
                                <p>Change your password to keep your account secure.</p>
                            </div>
                        </div>
                        <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="password-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="change_password">

                            <div class="form-group">
                                <label for="current_password">Current Password <span class="required">*</span></label>
                                <div class="input-password-wrap">
                                    <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                                    <button type="button" class="password-toggle" data-target="current_password" tabindex="-1">Show</button>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group form-col">
                                    <label for="new_password">New Password <span class="required">*</span></label>
                                    <div class="input-password-wrap">
                                        <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" minlength="8" required>
                                        <button type="button" class="password-toggle" data-target="new_password" tabindex="-1">Show</button>
                                    </div>
                                </div>
                                <div class="form-group form-col">
                                    <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                                    <div class="input-password-wrap">
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8" required>
                                        <button type="button" class="password-toggle" data-target="confirm_password" tabindex="-1">Show</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-outline">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <aside class="profile-side-rail">
            <div class="profile-side-card">
                <span class="profile-side-kicker">Quick snapshot</span>
                <h3>Account highlights</h3>
                <ul class="profile-side-list">
                    <li>
                        <strong>Delivery area</strong>
                        <span><?= htmlspecialchars($locationLabel) ?></span>
                    </li>
                    <li>
                        <strong>Email status</strong>
                        <?php if (!empty($user['email_verified_at'])): ?>
                            <span class="profile-side-verified">&#10003; Verified</span>
                        <?php elseif (!empty($user['email'])): ?>
                            <span class="profile-side-unverified">&#10007; Not verified</span>
                        <?php else: ?>
                            <span>Not yet added</span>
                        <?php endif; ?>
                    </li>
                    <li>
                        <strong>Profile progress</strong>
                        <span><?= $profileCompletion ?>% completed</span>
                    </li>
                </ul>
            </div>

            <div class="profile-side-card profile-side-card--accent">
                <span class="profile-side-kicker">Polish checklist</span>
                <h3>Make checkout feel effortless.</h3>
                <ul class="profile-checklist-simple">
                    <li class="profile-check-row <?= $contactReady ? 'is-done' : 'is-pending' ?>">
                        <span class="profile-check-dot" aria-hidden="true"></span>
                        <div class="profile-check-copy">
                            <strong>Add a reachable contact number</strong>
                            <p><?= $contactReady ? 'Your contact details are ready for order updates.' : 'Save a phone number for delivery updates and call confirmation.' ?></p>
                        </div>
                    </li>
                    <li class="profile-check-row <?= $addressReady ? 'is-done' : 'is-pending' ?>">
                        <span class="profile-check-dot" aria-hidden="true"></span>
                        <div class="profile-check-copy">
                            <strong>Save your preferred delivery address</strong>
                            <p><?= $addressReady ? 'Your address is already saved for faster checkout.' : 'Add your full street, city, and zip code for smoother order processing.' ?></p>
                        </div>
                    </li>
                    <li class="profile-check-row is-done">
                        <span class="profile-check-dot" aria-hidden="true"></span>
                        <div class="profile-check-copy">
                            <strong>Keep your password fresh</strong>
                            <p>Update your password anytime in the security tab for extra protection.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<!-- Profile Tab Navigation & Password Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Tab navigation
    var tabs = document.querySelectorAll('.profile-tab');
    var sections = document.querySelectorAll('.profile-section-block');
    function activateTab(target) {
        tabs.forEach(function(t){
            t.classList.toggle('active', t.dataset.target === target);
        });
        sections.forEach(function(s){
            var active = s.id === target;
            s.style.display = active ? '' : 'none';
            s.classList.toggle('fade-in', active);
        });
    }

    var activeTab = document.querySelector('.profile-tab.active');
    if (activeTab) {
        activateTab(activeTab.dataset.target);
    }

    tabs.forEach(function(tab){
        tab.addEventListener('click', function(){
            activateTab(this.dataset.target);
        });
    });

    document.querySelectorAll('[data-profile-jump]').forEach(function(button){
        button.addEventListener('click', function(){
            var target = this.getAttribute('data-profile-jump');
            activateTab(target);
            var nav = document.querySelector('.profile-nav-shell');
            if (nav) {
                nav.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Password visibility toggle
    document.querySelectorAll('.password-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
            var input = document.getElementById(this.dataset.target);
            if (!input) return;
            var isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            this.textContent = isPass ? 'Hide' : 'Show';
        });
    });
});
</script>

<!-- Email change modal (hidden by default) -->
<div id="email-change-modal" class="ec-modal" aria-hidden="true" style="display:none;">
    <div class="ec-modal-overlay" data-role="overlay"></div>
    <div class="ec-modal-card" role="dialog" aria-modal="true" aria-labelledby="ec-modal-title">
        <div class="ec-modal-header">
            <h3 id="ec-modal-title">Change Email</h3>
            <p class="ec-modal-sub">We'll verify your identity before updating your email.</p>
        </div>
        <div class="ec-modal-body">
            <div data-step="authorize" class="ec-step">
                <p>Click below to send an OTP to your current email to authorize the change.</p>
                <div class="ec-actions">
                    <button class="btn btn-outline" data-action="cancel">Cancel</button>
                    <button class="btn btn-accent" data-action="send-authorize">Send OTP</button>
                </div>
            </div>

            <div data-step="authorize-verify" class="ec-step" style="display:none;">
                <p>An OTP was sent to your current email. Enter it below to authorize.</p>
                <input type="text" class="form-control" id="ec-otp-authorize" placeholder="Enter OTP">
                <div class="ec-actions">
                    <button class="btn btn-outline" data-action="back-to-authorize">Back</button>
                    <button class="btn btn-accent" data-action="verify-authorize">Verify</button>
                </div>
            </div>

            <div data-step="new-email" class="ec-step" style="display:none;">
                <p>Enter the new email you want to use.</p>
                <input type="email" class="form-control" id="ec-new-email" placeholder="you@example.com">
                <div class="ec-actions">
                    <button class="btn btn-outline" data-action="back-to-authorize-done">Back</button>
                    <button class="btn btn-accent" data-action="send-new">Send OTP to New Email</button>
                </div>
            </div>

            <div data-step="new-verify" class="ec-step" style="display:none;">
                <p>An OTP was sent to your new email. Enter it below to confirm the change.</p>
                <input type="text" class="form-control" id="ec-otp-new" placeholder="Enter OTP">
                <div class="ec-actions">
                    <button class="btn btn-outline" data-action="back-to-new">Back</button>
                    <button class="btn btn-accent" data-action="verify-new">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>

<style>
/* Minimal modal styles scoped to this view */
.ec-modal { position: fixed; inset: 0; display:flex; align-items:center; justify-content:center; z-index:1200; }
.ec-modal-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.35); backdrop-filter: blur(4px); opacity:0; transition: opacity .22s ease; }
.ec-modal.open .ec-modal-overlay { opacity:1; }
.ec-modal-card { position:relative; background:#fff; border-radius:12px; max-width:520px; width:92%; box-shadow:0 12px 40px rgba(0,0,0,0.3); padding:18px; z-index:1; transform: translateY(8px) scale(0.98); opacity:0; transition: transform .28s cubic-bezier(.2,.9,.3,1), opacity .18s ease; }
.ec-modal.open .ec-modal-card { transform: translateY(0) scale(1); opacity:1; }
.ec-modal-close { position:absolute; right:10px; top:10px; border:none; background:transparent; font-size:20px; cursor:pointer; }
.ec-modal-header h3 { margin:0 0 6px 0; }
.ec-modal-sub { margin:0 0 12px 0; color:#666; font-size:13px }
.ec-modal-body .ec-step { margin-top:6px; }
.ec-modal-body .ec-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:12px; }
.ec-modal-body input.form-control { width:100%; box-sizing:border-box; padding:8px 10px; margin-top:8px; }

/* Button click animation */
.btn-press-anim { transform: translateY(1px) scale(0.985); transition: transform .12s ease; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var btn = document.getElementById('change-email-btn');
    if (!btn) return;
    var modal = document.getElementById('email-change-modal');
    var overlay = modal && modal.querySelector('[data-role="overlay"]');
    var csrf = function(){ var el = document.querySelector('#profile-form input[name="csrf_token"]'); return el ? el.value : ''; };

    function showStep(step){
        var steps = modal.querySelectorAll('.ec-step');
        steps.forEach(function(s){ s.style.display = (s.getAttribute('data-step') === step) ? '' : 'none'; });
    }

    function openModal(){
        modal.style.display = 'flex';
        // trigger CSS open animation
        setTimeout(function(){ modal.classList.add('open'); }, 10);
        modal.setAttribute('aria-hidden','false');
        showStep('authorize');
        document.body.style.overflow = 'hidden';
        // focus the primary action after animation
        setTimeout(function(){ var el = modal.querySelector('[data-action="send-authorize"]'); if (el) el.focus(); }, 220);
    }
    function closeModal(){
        // animate out then hide
        modal.classList.remove('open');
        setTimeout(function(){ modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; }, 240);
    }

    function disableBtn(el){ if (!el) return; el.disabled = true; el.setAttribute('aria-busy','true'); }
    function enableBtn(el){ if (!el) return; el.disabled = false; el.removeAttribute('aria-busy'); }

    // Wire close actions
    if (overlay) overlay.addEventListener('click', closeModal);

    btn.addEventListener('click', function(){
        // brief click animation on the Change button
        btn.classList.add('btn-press-anim');
        setTimeout(function(){ btn.classList.remove('btn-press-anim'); }, 140);
        openModal();
    });

    // Delegated actions inside modal
    modal.addEventListener('click', function(e){
        var action = e.target.getAttribute('data-action');
        if (!action) return;
        e.preventDefault();

        if (action === 'cancel') { closeModal(); }
        else if (action === 'back-to-authorize') { showStep('authorize'); }
        else if (action === 'send-authorize') {
            var sendBtn = modal.querySelector('[data-action="send-authorize"]');
            disableBtn(sendBtn);
            fetch('<?= APP_URL ?>/index.php?url=profile-send-email-otp', {
                method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ csrf_token: csrf() })
            }).then(r=>r.json()).then(function(res){
                if (!res || !res.success) { showToast('error', (res && res.message) ? res.message : 'Failed to send OTP'); enableBtn(sendBtn); return; }
                if (res.emailed === false) showToast('info', 'OTP could not be emailed. Check storage/mails.');
                showStep('authorize-verify');
            }).catch(function(){ alert('Network error'); }).finally(function(){ enableBtn(sendBtn); });
        }

        else if (action === 'verify-authorize') {
            var vBtn = modal.querySelector('[data-action="verify-authorize"]');
            var otp = (document.getElementById('ec-otp-authorize')||{}).value || '';
            if (!otp.trim()) { alert('Enter OTP'); return; }
            disableBtn(vBtn);
            fetch('<?= APP_URL ?>/index.php?url=profile-verify-email-otp', {
                method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ csrf_token: csrf(), otp: otp.trim() })
            }).then(r=>r.json()).then(function(res){
                if (!res || !res.success) { showToast('error', (res && res.message) ? res.message : 'Authorization failed'); enableBtn(vBtn); return; }
                showStep('new-email');
            }).catch(function(){ alert('Network error'); }).finally(function(){ enableBtn(vBtn); });
        }

        else if (action === 'send-new') {
            var sBtn = modal.querySelector('[data-action="send-new"]');
            var newEmailEl = document.getElementById('ec-new-email');
            var newEmail = newEmailEl ? newEmailEl.value.trim() : '';
            if (!newEmail || newEmail.indexOf('@') === -1) { alert('Enter a valid email'); return; }
            disableBtn(sBtn);
            fetch('<?= APP_URL ?>/index.php?url=profile-send-new-email-otp', {
                method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ csrf_token: csrf(), new_email: newEmail })
            }).then(r=>r.json()).then(function(res){
                if (!res || !res.success) { showToast('error', (res && res.message) ? res.message : 'Failed to send OTP to new email'); enableBtn(sBtn); return; }
                if (res.emailed === false) showToast('info', 'OTP to new email could not be sent by SMTP. Check storage/mails.');
                // move to verify new
                showStep('new-verify');
            }).catch(function(){ alert('Network error'); }).finally(function(){ enableBtn(sBtn); });
        }

        else if (action === 'verify-new') {
            var fBtn = modal.querySelector('[data-action="verify-new"]');
            var otp2 = (document.getElementById('ec-otp-new')||{}).value || '';
            var newEmail = (document.getElementById('ec-new-email')||{}).value || '';
            if (!otp2.trim()) { alert('Enter OTP'); return; }
            disableBtn(fBtn);
            fetch('<?= APP_URL ?>/index.php?url=profile-verify-new-email-otp', {
                method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ csrf_token: csrf(), otp: otp2.trim(), new_email: newEmail })
            }).then(r=>r.json()).then(function(res){
                if (!res || !res.success) { showToast('error', (res && res.message) ? res.message : 'Verification failed'); enableBtn(fBtn); return; }
                var emailEl = document.getElementById('email'); if (emailEl) emailEl.value = res.email || newEmail;
                showToast('success', 'Email updated successfully.');
                closeModal();
                // Give the toast a moment to appear, then refresh so UI reflects server state
                setTimeout(function(){ try { location.reload(); } catch(e){} }, 900);
            }).catch(function(){ alert('Network error'); }).finally(function(){ enableBtn(fBtn); });
        }

        // allow back navigation
        else if (action === 'back-to-authorize-done') { showStep('authorize'); }
        else if (action === 'back-to-new') { showStep('new-email'); }
    });
});
</script>

<!-- Toast container -->
<div id="ec-toast-container" aria-live="polite" style="position:fixed;top:18px;right:18px;z-index:1400"></div>

<style>
/* Toast styles */
.ec-toast { min-width:300px; max-width:420px; background:#fff; border-left:4px solid #38b000; box-shadow:0 8px 20px rgba(0,0,0,0.12); border-radius:8px; padding:12px 14px; margin-bottom:12px; overflow:hidden; position:relative; }
.ec-toast--info { border-left-color:#1e90ff; }
.ec-toast--error { border-left-color:#e63946; }
.ec-toast h4 { margin:0 0 4px 0; font-size:15px; }
.ec-toast p { margin:0; color:#444; font-size:13px; }
.ec-toast .ec-toast-close { position:absolute; right:8px; top:8px; border:none; background:transparent; font-size:14px; cursor:pointer; }
.ec-toast .ec-toast-progress { position:absolute; left:0; bottom:0; height:4px; background:rgba(0,0,0,0.06); width:100%; }
.ec-toast .ec-toast-progress > i { display:block; height:100%; width:100%; background:linear-gradient(90deg,#2dd4bf,#06b6d4); transform-origin:left; transform:scaleX(1); transition:transform linear; }
</style>

<script>
// Simple toast helper
function showToast(type, message, title) {
    title = title || (type === 'success' ? 'Success' : (type === 'error' ? 'Error' : 'Notice'));
    var container = document.getElementById('ec-toast-container');
    if (!container) return alert(message);

    var el = document.createElement('div'); el.className = 'ec-toast ec-toast--' + (type === 'info' ? 'info' : (type === 'error' ? 'error' : 'success'));
    var h = document.createElement('h4'); h.textContent = title; el.appendChild(h);
    var p = document.createElement('p'); p.textContent = message; el.appendChild(p);
    var closeBtn = document.createElement('button'); closeBtn.className = 'ec-toast-close'; closeBtn.innerHTML = '×'; el.appendChild(closeBtn);
    var progressWrap = document.createElement('div'); progressWrap.className = 'ec-toast-progress'; var progressBar = document.createElement('i'); progressWrap.appendChild(progressBar); el.appendChild(progressWrap);

    container.appendChild(el);

    var duration = 4200;
    // animate progress bar
    // set initial transform scaleX(1) then shrink to 0
    setTimeout(function(){ progressBar.style.transition = 'transform ' + duration + 'ms linear'; progressBar.style.transform = 'scaleX(0)'; }, 30);

    var auto = setTimeout(function(){ if (el.parentNode) el.parentNode.removeChild(el); }, duration + 220);
    // pause on hover
    el.addEventListener('mouseenter', function(){ clearTimeout(auto); progressBar.style.transition = ''; });
    el.addEventListener('mouseleave', function(){ var remaining = duration; progressBar.style.transition = 'transform ' + remaining + 'ms linear'; progressBar.style.transform = 'scaleX(0)'; auto = setTimeout(function(){ if (el.parentNode) el.parentNode.removeChild(el); }, remaining); });
    closeBtn.addEventListener('click', function(){ clearTimeout(auto); if (el.parentNode) el.parentNode.removeChild(el); });
}
</script>
