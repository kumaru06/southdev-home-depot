<?php
$pageTitle = 'Create Account';
require_once INCLUDES_PATH . '/header.php';
?>

<?php require_once INCLUDES_PATH . '/navbar.php'; ?>

<div class="register-split-wrapper">
    <div class="register-split">
        <!-- Card accent bar -->
        <div class="register-accent-bar">
            <div class="register-accent-brand">
                <img src="<?= APP_URL ?>/assets/uploads/images/png-icon/addaccount.png" alt="Create Account" class="auth-icon-img">
                <div>
                    <h2>Create Account</h2>
                    <p class="auth-tagline">Join <?= APP_NAME ?> today</p>
                </div>
            </div>
        </div>

        <div class="register-panel">
            <div class="register-form-shell">
                <form action="<?= APP_URL ?>/index.php?url=register" method="POST" id="register-form" class="register-form">
                    <?= csrf_field() ?>

                    <!-- Section: Personal Info -->
                    <div class="form-section-label">Personal Information</div>

                    <div class="form-row">
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input type="text" id="first_name" name="first_name" class="form-control form-control-sm fl-input" placeholder=" " autocomplete="given-name" required>
                                <label for="first_name" class="fl-label">First Name <span class="required">*</span></label>
                            </div>
                        </div>
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input type="text" id="last_name" name="last_name" class="form-control form-control-sm fl-input" placeholder=" " autocomplete="family-name" required>
                                <label for="last_name" class="fl-label">Last Name <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input type="text" id="phone" name="phone" class="form-control form-control-sm fl-input" placeholder=" " autocomplete="tel">
                                <label for="phone" class="fl-label">Phone Number</label>
                            </div>
                        </div>
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input type="text" id="birthdate_display" class="form-control form-control-sm fl-input date-display" placeholder=" " aria-label="Birthdate" autocomplete="bday" maxlength="10" required>
                                <label for="birthdate_display" class="fl-label">Birthdate <span class="required">*</span></label>
                            </div>
                            <input type="date" id="birthdate" name="birthdate" class="hidden-date-input" aria-hidden="true">
                        </div>
                    </div>

                    <!-- Section: Account Info -->
                    <div class="form-section-label" style="margin-top:6px;">Account Details</div>

                    <div class="form-row">
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input type="text" id="username" name="username" class="form-control form-control-sm fl-input" placeholder=" " autocomplete="username" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9._-]+">
                                <label for="username" class="fl-label">Username <span class="required">*</span></label>
                            </div>
                            <small id="username-feedback" class="username-feedback" style="display:none;"></small>
                        </div>
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input type="email" id="email" name="email" class="form-control form-control-sm fl-input" placeholder=" " autocomplete="email" required>
                                <label for="email" class="fl-label">Email Address <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input type="password" id="password" name="password" class="form-control form-control-sm fl-input" placeholder=" " autocomplete="new-password" minlength="8" required>
                                <label for="password" class="fl-label">Password <span class="required">*</span></label>
                                <button type="button" class="pwd-toggle" aria-label="Toggle password" data-target="password">
                                    <svg class="eye-show" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-hide" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <div class="pwd-strength-bar" id="pwd-strength-bar">
                                <div class="pwd-strength-fill" id="pwd-strength-fill"></div>
                            </div>
                            <small class="pwd-strength-text" id="pwd-strength-text"></small>
                        </div>
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-sm fl-input" placeholder=" " autocomplete="new-password" required>
                                <label for="confirm_password" class="fl-label">Confirm Password <span class="required">*</span></label>
                                <button type="button" class="pwd-toggle" aria-label="Toggle confirm password" data-target="confirm_password">
                                    <svg class="eye-show" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-hide" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <small class="pwd-match-text" id="pwd-match-text"></small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent btn-block" id="register-submit-btn">
                        Create Account
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? <a href="<?= APP_URL ?>/index.php?url=login">Sign in</a></p>
                </div>
            </div>
        </div><!-- .register-panel -->
    </div><!-- .register-split -->
</div><!-- .register-split-wrapper -->

<style>
/* ─── Layout reset ──────────────────────────────────────────────────────── */
.site-header { box-shadow: none; }
.site-header .main-nav { margin-bottom: 0; }
.footer { margin-top: 0; }

/* ─── Page wrapper ──────────────────────────────────────────────────────── */
.register-split-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 110px);
    padding: 20px 12px;
    background:
        linear-gradient(135deg, rgba(15,23,42,.55) 0%, rgba(15,23,42,.32) 100%),
        url('<?= APP_URL ?>/assets/uploads/images/image.png') center/cover no-repeat;
}

/* ─── Card ──────────────────────────────────────────────────────────────── */
.register-split {
    width: 100%;
    max-width: 780px;
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(15,23,42,.22), 0 4px 12px rgba(15,23,42,.10);
    animation: cardIn .35s cubic-bezier(.22,.68,0,1.2) both;
}

@keyframes cardIn {
    from { opacity: 0; transform: translateY(18px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ─── Accent header bar ─────────────────────────────────────────────────── */
.register-accent-bar {
    background: linear-gradient(110deg, #ea580c 0%, #f97316 55%, #fb923c 100%);
    padding: 12px 28px 10px;
    position: relative;
    overflow: hidden;
}

.register-accent-bar::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='30'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    pointer-events: none;
}

.register-accent-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.register-accent-brand .auth-icon-img {
    width: 34px;
    height: 34px;
    padding: 5px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    box-shadow: 0 0 0 2px rgba(255,255,255,.3);
    filter: brightness(0) invert(1);
}

.register-accent-brand h2 {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: -.03em;
    color: #fff;
    line-height: 1.1;
}

.register-accent-brand .auth-tagline {
    margin: 2px 0 0;
    color: rgba(255,255,255,.8);
    font-size: .72rem;
    font-weight: 500;
}

/* ─── Panel body ────────────────────────────────────────────────────────── */
.register-panel {
    padding: 12px 28px 12px;
    background: #fff;
}

/* ─── Form shell ────────────────────────────────────────────────────────── */
.register-form-shell {
    width: 100%;
}

.register-form {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

/* ─── Section labels ────────────────────────────────────────────────────── */
.form-section-label {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #f97316;
    margin-bottom: -2px;
    margin-top: 0;
}

/* ─── Form rows / cols ──────────────────────────────────────────────────── */
.register-panel .form-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 8px 16px;
    margin-bottom: 0;
}

.register-panel .form-col { min-width: 0; }
.register-panel .form-group { position: relative; margin-bottom: 0; }

/* ─── Labels ────────────────────────────────────────────────────────────── */
.register-panel label {
    display: block;
    margin-bottom: 5px;
    color: #374151;
    font-size: .73rem;
    font-weight: 600;
    letter-spacing: 0;
}

.required { color: #ef4444; }

/* ─── Input icon wrapper (used for password toggle) ─────────────────────── */
.input-icon-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

/* ─── Inputs ────────────────────────────────────────────────────────────── */
.register-panel .form-control-sm {
    width: 100%;
    min-height: 38px;
    padding: 8px 38px 8px 12px;
    border-radius: 7px;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    color: #0f172a;
    font-size: .82rem;
    box-shadow: none;
    transition: border-color .2s, box-shadow .2s, background .2s;
    outline: none;
}

.register-panel .form-control-sm::placeholder { color: #b0bac6; }

.register-panel .form-control-sm:hover {
    border-color: #cbd5e1;
    background: #f1f5f9;
}

.register-panel .form-control-sm:focus {
    background: #fff;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249,115,22,.13);
}

/* ─── Password toggle button ────────────────────────────────────────────── */
.pwd-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: #9ca3af;
    display: flex;
    align-items: center;
    transition: color .2s;
    z-index: 2;
}
.register-panel .fl-wrap { margin-top: 6px; }
.register-panel .fl-input {
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}
.fl-wrap .fl-input.has-toggle,
.fl-wrap:has(.pwd-toggle) .fl-input {
    padding-right: 38px !important;
}

.pwd-toggle:hover { color: #f97316; }

/* ─── Password strength ─────────────────────────────────────────────────── */
.pwd-strength-bar {
    margin-top: 5px;
    height: 3px;
    background: #e2e8f0;
    border-radius: 99px;
    overflow: hidden;
    display: none;
}

.pwd-strength-fill {
    height: 100%;
    width: 0;
    border-radius: 99px;
    transition: width .35s ease, background-color .35s ease;
}

.pwd-strength-text {
    display: block;
    margin-top: 3px;
    font-size: .68rem;
    font-weight: 600;
}

.pwd-match-text {
    display: block;
    margin-top: 3px;
    font-size: .68rem;
    font-weight: 600;
}

/* ─── Username feedback ─────────────────────────────────────────────────── */
.username-feedback { display: block; margin-top: 4px; font-size: .69rem; font-weight: 600; }
.username-feedback.taken    { color: #dc2626; }
.username-feedback.available{ color: #16a34a; }
.username-feedback.checking { color: #9ca3af; }
#username.input-taken      { border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220,38,38,.1) !important; }
#username.input-available  { border-color: #16a34a !important; box-shadow: 0 0 0 3px rgba(22,163,74,.1) !important; }

/* ─── Submit button ─────────────────────────────────────────────────────── */
.register-panel .btn-block {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 40px;
    margin-top: 4px;
    border-radius: 8px;
    font-size: .84rem;
    font-weight: 700;
    letter-spacing: .04em;
    border: 0;
    color: #fff;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    box-shadow: 0 4px 14px rgba(249,115,22,.35);
    transition: transform .15s, box-shadow .2s, filter .2s;
    cursor: pointer;
}

.register-panel .btn-block:hover {
    filter: brightness(1.05);
    box-shadow: 0 6px 20px rgba(249,115,22,.42);
    transform: translateY(-1px);
}

.register-panel .btn-block:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(249,115,22,.3);
}

/* ─── Footer link ───────────────────────────────────────────────────────── */
.register-panel .auth-footer {
    margin-top: 8px;
    text-align: center;
    font-size: .78rem;
    color: #64748b;
    border-top: 1px solid #f1f5f9;
    padding-top: 8px;
}

.register-panel .auth-footer a {
    color: #f97316;
    font-weight: 700;
    text-decoration: none;
}

.register-panel .auth-footer a:hover {
    color: #ea580c;
    text-decoration: underline;
}

/* ─── Hidden date input ─────────────────────────────────────────────────── */
.hidden-date-input { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none; }

/* ─── Responsive ────────────────────────────────────────────────────────── */
@media (max-width: 760px) {
    .register-split { max-width: 100%; }
    .register-panel { padding: 18px 16px 16px; }
    .register-accent-bar { padding: 16px 18px 14px; }
}

@media (max-width: 640px) {
    .register-split-wrapper { padding: 14px 8px 20px; }
    .register-panel .form-row { grid-template-columns: 1fr; gap: 10px; }
}

@media (max-height: 860px) and (min-width: 640px) {
    .register-split-wrapper { padding: 10px 12px; }
    .register-accent-bar { padding: 12px 28px 10px; }
    .register-accent-brand .auth-icon-img { width: 34px; height: 34px; }
    .register-accent-brand h2 { font-size: 1rem; }
    .register-panel { padding: 14px 28px 14px; }
    .register-form { gap: 10px; }
    .register-panel .form-control-sm { min-height: 36px; }
    .register-panel .btn-block { min-height: 38px; }
}
</style>

<script>
// ── Password show/hide toggles ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.pwd-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
            var input = document.getElementById(btn.dataset.target);
            var show  = btn.querySelector('.eye-show');
            var hide  = btn.querySelector('.eye-hide');
            if (!input) return;
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            show.style.display = isHidden ? 'none'  : '';
            hide.style.display = isHidden ? ''      : 'none';
        });
    });

    // ── Password strength meter ─────────────────────────────────────────
    var pwdInput   = document.getElementById('password');
    var bar        = document.getElementById('pwd-strength-bar');
    var fill       = document.getElementById('pwd-strength-fill');
    var strengthTx = document.getElementById('pwd-strength-text');

    function getStrength(v){
        var s = 0;
        if (v.length >= 8)  s++;
        if (v.length >= 12) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        return s;
    }

    var levels = [
        { label: '', color: '#e2e8f0', pct: 0 },
        { label: 'Very weak',  color: '#ef4444', pct: 20 },
        { label: 'Weak',       color: '#f97316', pct: 40 },
        { label: 'Fair',       color: '#eab308', pct: 60 },
        { label: 'Strong',     color: '#22c55e', pct: 80 },
        { label: 'Very strong',color: '#16a34a', pct: 100 },
    ];

    if (pwdInput && bar && fill && strengthTx) {
        pwdInput.addEventListener('input', function(){
            var v = pwdInput.value;
            if (!v) { bar.style.display = 'none'; strengthTx.textContent = ''; return; }
            bar.style.display = 'block';
            var s = Math.min(getStrength(v), 5);
            fill.style.width          = levels[s].pct + '%';
            fill.style.backgroundColor = levels[s].color;
            strengthTx.textContent    = levels[s].label;
            strengthTx.style.color    = levels[s].color;
        });
    }

    // ── Confirm password match ──────────────────────────────────────────
    var confirmInput = document.getElementById('confirm_password');
    var matchTx      = document.getElementById('pwd-match-text');

    function checkMatch(){
        if (!confirmInput.value) { matchTx.textContent = ''; return; }
        if (confirmInput.value === pwdInput.value) {
            matchTx.textContent = '✓ Passwords match';
            matchTx.style.color = '#16a34a';
        } else {
            matchTx.textContent = '✗ Passwords do not match';
            matchTx.style.color = '#ef4444';
        }
    }

    if (confirmInput && matchTx) {
        confirmInput.addEventListener('input', checkMatch);
        if (pwdInput) pwdInput.addEventListener('input', checkMatch);
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var display = document.getElementById('birthdate_display');
    var hidden  = document.getElementById('birthdate');
    if(!display || !hidden) return;

    var currentYear = new Date().getFullYear();
    var minYear = 1920;
    var maxYear = currentYear;

    function daysInMonth(m, y){
        return new Date(y, m, 0).getDate();
    }

    function setError(msg){
        display.setCustomValidity(msg);
        display.style.borderColor = '#ef4444';
    }
    function clearError(){
        display.setCustomValidity('');
        display.style.borderColor = '';
    }

    function syncHidden(mm, dd, yyyy){
        if(mm && dd && yyyy && String(yyyy).length === 4){
            hidden.value = yyyy + '-' + ('0'+mm).slice(-2) + '-' + ('0'+dd).slice(-2);
        } else {
            hidden.value = '';
        }
    }

    function validateFull(mm, dd, yyyy){
        var m = parseInt(mm,10), d = parseInt(dd,10), y = parseInt(yyyy,10);
        if(isNaN(m) || m < 1 || m > 12){
            setError('Month must be 01-12'); return false;
        }
        if(isNaN(y) || y < minYear || y > maxYear){
            setError('Year must be ' + minYear + '-' + maxYear); return false;
        }
        var maxD = daysInMonth(m, y);
        if(isNaN(d) || d < 1 || d > maxD){
            setError('Day must be 01-' + maxD + ' for month ' + ('0'+m).slice(-2)); return false;
        }
        // Check not in the future
        var entered = new Date(y, m - 1, d);
        if(entered > new Date()){
            setError('Birthdate cannot be in the future'); return false;
        }
        clearError();
        return true;
    }

    // Auto-format: only allow digits, auto-insert dashes as MM-DD-YYYY
    display.addEventListener('input', function(){
        var raw = display.value.replace(/[^\d]/g, '').slice(0, 8);
        var formatted = '';
        if(raw.length <= 2){
            formatted = raw;
        } else if(raw.length <= 4){
            formatted = raw.slice(0,2) + '-' + raw.slice(2);
        } else {
            formatted = raw.slice(0,2) + '-' + raw.slice(2,4) + '-' + raw.slice(4);
        }
        display.value = formatted;

        // Live partial validation
        var mm = raw.slice(0,2), dd = raw.slice(2,4), yyyy = raw.slice(4,8);
        if(mm.length === 2){
            var mNum = parseInt(mm,10);
            if(mNum < 1 || mNum > 12){ setError('Month must be 01-12'); return; }
        }
        if(dd.length === 2 && mm.length === 2){
            var dNum = parseInt(dd,10);
            if(dNum < 1 || dNum > 31){ setError('Day must be 01-31'); return; }
        }
        if(yyyy.length === 4){
            if(validateFull(mm, dd, yyyy)){
                syncHidden(mm, dd, yyyy);
            } else {
                hidden.value = '';
            }
            return;
        }
        clearError();
        hidden.value = '';
    });

    // On blur: full validation
    display.addEventListener('blur', function(){
        if(!display.value){ clearError(); hidden.value = ''; return; }
        var m = display.value.match(/^(\d{2})-(\d{2})-(\d{4})$/);
        if(!m){
            setError('Please enter date as MM-DD-YYYY (e.g. 06-07-2001)');
            display.reportValidity();
            return;
        }
        if(validateFull(m[1], m[2], m[3])){
            syncHidden(m[1], m[2], m[3]);
        } else {
            hidden.value = '';
            display.reportValidity();
        }
    });

    // If native picker is used, sync back
    hidden.addEventListener('change', function(){
        if(!hidden.value) return;
        var p = hidden.value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if(p){
            display.value = p[2] + '-' + p[3] + '-' + p[1];
            clearError();
        }
    });

    // On form submit: final gate
    var form = display.closest('form');
    if(form){
        form.addEventListener('submit', function(e){
            if(!display.value){ e.preventDefault(); setError('Please enter your birthdate'); display.reportValidity(); return; }
            var m = display.value.match(/^(\d{2})-(\d{2})-(\d{4})$/);
            if(!m){
                e.preventDefault();
                setError('Please enter date as MM-DD-YYYY (e.g. 06-07-2001)');
                display.reportValidity();
                return;
            }
            if(!validateFull(m[1], m[2], m[3])){
                e.preventDefault();
                display.reportValidity();
                return;
            }
            syncHidden(m[1], m[2], m[3]);
        });
    }
});
</script>

<script>
// Real-time username availability check
document.addEventListener('DOMContentLoaded', function(){
    var usernameInput = document.getElementById('username');
    var feedback = document.getElementById('username-feedback');
    var debounceTimer = null;

    if (!usernameInput || !feedback) return;

    usernameInput.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        var val = usernameInput.value.trim();

        // Reset state
        usernameInput.classList.remove('input-taken', 'input-available');
        feedback.style.display = 'none';
        feedback.className = 'username-feedback';

        if (val.length < 3) return;
        if (!/^[a-zA-Z0-9._-]+$/.test(val)) return;

        // Show checking state
        feedback.textContent = 'Checking...';
        feedback.className = 'username-feedback checking';
        feedback.style.display = 'block';

        debounceTimer = setTimeout(function(){
            fetch('<?= APP_URL ?>/index.php?url=check-username&username=' + encodeURIComponent(val))
                .then(function(r){ return r.json(); })
                .then(function(data){
                    feedback.style.display = 'block';
                    if (data.available) {
                        feedback.textContent = '\u2713 ' + data.message;
                        feedback.className = 'username-feedback available';
                        usernameInput.classList.remove('input-taken');
                        usernameInput.classList.add('input-available');
                        usernameInput.setCustomValidity('');
                    } else {
                        feedback.textContent = '\u2717 ' + data.message;
                        feedback.className = 'username-feedback taken';
                        usernameInput.classList.remove('input-available');
                        usernameInput.classList.add('input-taken');
                        usernameInput.setCustomValidity(data.message);
                    }
                })
                .catch(function(){
                    feedback.style.display = 'none';
                });
        }, 400);
    });

    // Also block form submission if username is taken
    var form = usernameInput.closest('form');
    if (form) {
        form.addEventListener('submit', function(e){
            if (usernameInput.classList.contains('input-taken')) {
                e.preventDefault();
                usernameInput.focus();
                feedback.style.display = 'block';
            }
        });
    }
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
