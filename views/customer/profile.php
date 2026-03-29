<?php
/* $pageTitle, $extraCss set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <h1 class="page-heading"><i data-lucide="user"></i> My Profile</h1>

    <div class="profile-grid">
        <!-- Left column: Profile card -->
        <div class="profile-card-sidebar card">
                <div class="profile-card-photo-area">
                <?php
                    $profileImage = $user['profile_image'] ?? '';
                    $profileImageUrl = $profileImage ? (APP_URL . '/assets/uploads/profiles/' . rawurlencode($profileImage)) : '';
                    $initials = strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1));
                ?>
                <div class="profile-photo profile-photo--lg">
                    <?php if (!empty($profileImage)): ?>
                        <img src="<?= $profileImageUrl ?>" alt="Profile photo" class="profile-avatar-img">
                    <?php else: ?>
                        <div class="profile-avatar-fallback" aria-label="Profile initials"><?= htmlspecialchars($initials) ?></div>
                    <?php endif; ?>
                </div>
                <h3 class="profile-card-name"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h3>
                <?php if (!empty($user['username'])): ?>
                    <p class="profile-card-username"><i data-lucide="hash"></i> <?= htmlspecialchars($user['username']) ?></p>
                <?php endif; ?>
                <p class="profile-card-email"><i data-lucide="mail"></i> <?= htmlspecialchars($user['email']) ?></p>
                <?php if (!empty($user['phone'])): ?>
                    <p class="profile-card-phone"><i data-lucide="phone"></i> <?= htmlspecialchars($user['phone']) ?></p>
                <?php endif; ?>
            </div>
            <div class="profile-card-upload">
                <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" enctype="multipart/form-data" id="photo-form">
                    <?= csrf_field() ?>
                    <label class="profile-upload-label">
                        <i data-lucide="camera"></i>
                        <span>Change Photo</span>
                        <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="this.form.submit()">
                    </label>
                    <p class="profile-upload-hint">JPG, PNG or WebP. Max 2MB.</p>
                </form>
            </div>
        </div>

        <!-- Right column: Forms -->
        <div class="profile-forms">
            <div class="card profile-section-card">
                <div class="profile-section-header">
                    <i data-lucide="user-circle"></i>
                    <div>
                        <h3>Personal Information</h3>
                        <p>Update your personal details and contact information.</p>
                    </div>
                </div>
                <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="profile-form">
                    <?= csrf_field() ?>

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
                        <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                        <small class="text-muted">Letters, numbers, underscore, dash or dot (3-30 chars).</small>
                    </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group form-col">
                            <label for="email">Email <span class="required">*</span></label>
                            <div class="input-with-action" style="display:flex;gap:8px;align-items:center;">
                                <input type="text" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required readonly aria-readonly="true">
                                <button type="button" id="change-email-btn" class="btn btn-outline small">Change</button>
                            </div>
                            <small class="text-muted">Email updates require verification via an OTP sent to your current email.</small>
                        </div>
                        <div class="form-group form-col">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <div class="form-section-label"><i data-lucide="map-pin"></i> Shipping Address</div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-col">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        </div>
                        <div class="form-group form-col">
                            <label for="state">Province</label>
                            <input type="text" id="state" name="state" class="form-control" value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                        </div>
                        <div class="form-group form-col">
                            <label for="zip_code">Zip Code</label>
                            <input type="text" id="zip_code" name="zip_code" class="form-control" value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-accent"><i data-lucide="save"></i> Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="card profile-section-card">
                <div class="profile-section-header">
                    <i data-lucide="shield"></i>
                    <div>
                        <h3>Security</h3>
                        <p>Change your password to keep your account secure.</p>
                    </div>
                </div>
                <form action="<?= APP_URL ?>/index.php?url=profile" method="POST" id="password-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Current Password <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-col">
                            <label for="new_password">New Password <span class="required">*</span></label>
                            <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" minlength="8" required>
                        </div>
                        <div class="form-group form-col">
                            <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-outline"><i data-lucide="key"></i> Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
