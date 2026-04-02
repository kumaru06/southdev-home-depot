<?php
$pageTitle = 'Create Account';
require_once INCLUDES_PATH . '/header.php';
?>

<?php require_once INCLUDES_PATH . '/navbar.php'; ?>

<div class="register-split-wrapper">
    <div class="register-split">
        <!-- Left: Hero Image -->
        <div class="register-media" style="--register-img: url('<?= APP_URL ?>/assets/uploads/images/image.png');">
            <div class="register-media-overlay"></div>
            <div class="register-media-content">
                <div class="register-media-badge">Join Us</div>
                <div class="register-media-title"><?= APP_NAME ?></div>
                <div class="register-media-subtitle"><?= APP_TAGLINE ?? 'Quality tiles & building materials' ?></div>
            </div>
        </div>

        <!-- Right: Form -->
        <div class="register-panel">
            <div class="auth-brand">
                <img src="<?= APP_URL ?>/assets/uploads/images/png-icon/addaccount.png" alt="Create Account" class="auth-icon-img">
                <div>
                    <h2>Create Account</h2>
                    <p class="auth-tagline">Join <?= APP_NAME ?> today</p>
                </div>
            </div>

            <form action="<?= APP_URL ?>/index.php?url=register" method="POST" id="register-form">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group form-col">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control form-control-sm" placeholder="E.g. Christian John" required>
                    </div>
                    <div class="form-group form-col">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control form-control-sm" placeholder="E.g. Millanes" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-col">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" class="form-control form-control-sm" placeholder="e.g. burnok123" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9._-]+">
                        <small id="username-feedback" class="username-feedback" style="display:none;"></small>
                    </div>
                    <div class="form-group form-col">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control form-control-sm" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-col">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control form-control-sm" placeholder="09XX XXX XXXX">
                    </div>
                    <div class="form-group form-col">
                        <label for="birthdate">Birthdate</label>
                        <input type="text" id="birthdate_display" class="form-control form-control-sm date-display" placeholder="e.g. 06-07-2001" aria-label="Birthdate" maxlength="10">
                        <input type="date" id="birthdate" name="birthdate" class="hidden-date-input" aria-hidden="true">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-col">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" class="form-control form-control-sm" placeholder="Min 8 characters" minlength="8" required>
                    </div>
                    <div class="form-group form-col">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-sm" placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent btn-block">
                    <i data-lucide="user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="<?= APP_URL ?>/index.php?url=login">Sign in</a></p>
            </div>
        </div><!-- .register-panel -->
    </div><!-- .register-split -->
</div><!-- .register-split-wrapper -->

<style>
/* ========== Register Split Layout ========== */
.register-split-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 140px);
    padding: 20px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}
.register-split {
    display: grid;
    grid-template-columns: 0.9fr 1.1fr;
    width: 100%;
    max-width: 1100px;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.04);
}
/* Left: Image Panel */
.register-media {
    position: relative;
    background:
        linear-gradient(135deg, rgba(234,88,12,.75) 0%, rgba(249,115,22,.55) 60%, rgba(251,146,60,.4) 100%),
        var(--register-img, #f97316);
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: flex-end;
    padding: 32px;
}
.register-media-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(0,0,0,.25) 100%);
}
.register-media-content {
    position: relative;
    z-index: 1;
    color: #fff;
}
.register-media-badge {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.25);
    font-weight: 800;
    font-size: 12px;
    letter-spacing: .4px;
    text-transform: uppercase;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.register-media-title {
    margin-top: 12px;
    font-size: 28px;
    font-weight: 900;
    letter-spacing: -.4px;
    text-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.register-media-subtitle {
    margin-top: 6px;
    color: rgba(255,255,255,.85);
    font-size: 14px;
    font-weight: 500;
}
/* Right: Form Panel */
.register-panel {
    padding: 28px 32px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.register-panel .auth-brand {
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.register-panel .auth-brand h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -.3px;
    color: #1f2937;
}
.register-panel .auth-tagline {
    margin: 2px 0 0;
    color: #6b7280;
    font-size: 12px;
}
.register-panel .auth-footer {
    margin-top: 14px;
    text-align: center;
}
.register-panel .form-group {
    margin-bottom: 10px;
}
.register-panel .form-row {
    gap: 12px;
    margin-bottom: 0;
}
.register-panel label {
    font-size: 11px;
    margin-bottom: 3px;
}
.register-panel .form-control-sm {
    padding: 8px 12px;
    font-size: 13px;
}
.register-panel .input-icon-wrap .form-control-sm {
    padding-left: 38px;
}
.register-panel .btn-block {
    margin-top: 14px;
    padding: 10px;
    font-size: 14px;
}
.register-panel .auth-footer a {
    color: var(--accent);
    font-weight: 700;
}

/* Responsive */
@media (max-width: 900px) {
    .register-split {
        grid-template-columns: 1fr;
        max-width: 520px;
    }
    .register-media {
        min-height: 200px;
    }
    .register-panel {
        padding: 28px 24px;
    }
}
@media (max-width: 480px) {
    .register-split-wrapper { padding: 16px 12px; }
    .register-media { min-height: 160px; padding: 20px; }
    .register-media-title { font-size: 22px; }
    .register-panel { padding: 22px 18px; }
}

.hidden-date-input{ position:absolute; left:-9999px; width:1px; height:1px; opacity:0; pointer-events:none; }

/* Username availability feedback */
.username-feedback { display: block; margin-top: 4px; font-size: 12px; font-weight: 600; }
.username-feedback.taken { color: #dc2626; }
.username-feedback.available { color: #16a34a; }
.username-feedback.checking { color: #9ca3af; }
#username.input-taken { border-color: #dc2626 !important; }
#username.input-available { border-color: #16a34a !important; }
</style>

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
            if(!display.value) return; // optional field, allow empty
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
