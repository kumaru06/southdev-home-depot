<?php
$pageTitle = 'Create Account';
require_once INCLUDES_PATH . '/header.php';
?>

<?php require_once INCLUDES_PATH . '/navbar.php'; ?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card card">
            <div class="auth-brand">
                <i data-lucide="grid-3x3" class="auth-icon"></i>
                <h2>Create Account</h2>
                <p class="auth-tagline">Join <?= APP_NAME ?> today</p>
            </div>

            <form action="<?= APP_URL ?>/index.php?url=register" method="POST" id="register-form">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group form-col">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="E.g. Christian John" required>
                    </div>
                    <div class="form-group form-col">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="E.g. Millanes" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <div class="input-icon-wrap">
                        <i data-lucide="mail" class="input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-icon-wrap">
                        <i data-lucide="phone" class="input-icon"></i>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="09XX XXX XXXX">
                    </div>
                </div>

                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <div class="input-icon-wrap">
                        <i data-lucide="calendar" class="input-icon"></i>
                            <input type="text" id="birthdate_display" class="form-control date-display" placeholder="mm/dd/yyyy" aria-label="Birthdate (mm/dd/yyyy)">
                            <input type="date" id="birthdate" name="birthdate" class="hidden-date-input" aria-hidden="true">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-col">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Min 8 characters" minlength="8" required>
                    </div>
                    <div class="form-group form-col">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent btn-block">
                    <i data-lucide="user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="<?= APP_URL ?>/index.php?url=login">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>

<style>
/* Visible text input styled with calendar icon on the right */
.input-icon-wrap .date-display{
    padding-right:40px;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239aa0a6' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='4' width='18' height='18' rx='2' ry='2'/><line x1='16' y1='2' x2='16' y2='6'/><line x1='8' y1='2' x2='8' y2='6'/><line x1='3' y1='10' x2='21' y1='10'/></svg>");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 18px 18px;
}
.input-icon-wrap .date-display{ cursor:text; }
.hidden-date-input{ position:absolute; left:-9999px; width:1px; height:1px; opacity:0; pointer-events:none; }

/* hide native picker indicator for hidden date input */
.input-icon-wrap input[type="date"]::-webkit-calendar-picker-indicator { display:none; }
.input-icon-wrap input[type="date"]::-ms-clear, .input-icon-wrap input[type="date"]::-ms-expand { display: none; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.input-icon-wrap').forEach(function(wrapper){
        var hidden = wrapper.querySelector('input[type="date"].hidden-date-input');
        var display = wrapper.querySelector('.date-display');
        if(!display) return;

        // clicking the wrapper (except directly on the visible input) opens the native date picker (hidden input)
        wrapper.addEventListener('click', function(e){
            // If user clicked into the visible input, allow normal typing
            if(e.target === display) return;
            // Otherwise (click on icon area / wrapper) open the native picker
            if(hidden){
                try{
                    if(typeof hidden.showPicker === 'function'){
                        hidden.showPicker();
                    } else {
                        hidden.focus();
                    }
                }catch(err){ hidden.focus(); }
            } else {
                display.focus();
            }
        });

        function expandTwoDigitYear(yy){
            var n = parseInt(yy,10);
            if(isNaN(n) || n < 0 || n > 99) return null;
            var currentYear = new Date().getFullYear();
            var currentTwo = currentYear % 100;
            if(n <= currentTwo) return 2000 + n;
            return 1900 + n;
        }

        function setHiddenFromParts(mm, dd, yyyy){
            if(!hidden) return;
            if(mm && dd && yyyy && String(yyyy).length === 4){
                hidden.value = yyyy + '-' + ('0'+mm).slice(-2) + '-' + ('0'+dd).slice(-2);
            } else {
                hidden.value = '';
            }
        }

        function validateYearParts(yyyy){
            if(!yyyy){ display.setCustomValidity(''); return true; }
            var ynum = null;
            if(String(yyyy).length === 2){
                ynum = expandTwoDigitYear(yyyy);
            } else {
                ynum = parseInt(yyyy,10);
            }
            var currentYear = new Date().getFullYear();
            if(isNaN(ynum) || ynum < 1960 || ynum > currentYear || String(ynum).length !== 4){
                display.setCustomValidity('Birth year must be between 1960 and ' + currentYear);
                return false;
            }
            display.setCustomValidity('');
            return true;
        }

        // Format typing into mm/dd/yyyy and sync hidden input
        display.addEventListener('input', function(){
            var raw = display.value || '';
            var digits = raw.replace(/\D/g,'').slice(0,8);
            var mm = '' , dd = '' , yyyy = '';
            if(digits.length <= 2) mm = digits;
            else if(digits.length <=4){ mm = digits.slice(0,2); dd = digits.slice(2); }
            else { mm = digits.slice(0,2); dd = digits.slice(2,4); yyyy = digits.slice(4,8); }
            var parts = [];
            if(mm) parts.push(mm);
            if(dd) parts.push(dd);
            if(yyyy) parts.push(yyyy);
            display.value = parts.join('/');
            if(yyyy.length === 4){
                if(validateYearParts(yyyy)) setHiddenFromParts(mm,dd,yyyy);
            } else if(yyyy.length === 2){
                var expanded = expandTwoDigitYear(yyyy);
                if(expanded && validateYearParts(String(expanded))) setHiddenFromParts(mm,dd,String(expanded));
            } else { display.setCustomValidity(''); setHiddenFromParts(); }
        });

        // If user selects via native picker, update display
        if(hidden){
            hidden.addEventListener('change', function(){
                if(!hidden.value) return;
                var m = hidden.value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if(m){ display.value = m[2] + '/' + m[3] + '/' + m[1]; validateYearParts(m[1]); setHiddenFromParts(m[2],m[3],m[1]); }
            });
        }

        display.addEventListener('blur', function(){
            if(!display.value){ display.setCustomValidity(''); return; }
            var m = display.value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if(m){
                var mm = m[1], dd = m[2], yyyy = m[3];
                if(!validateYearParts(yyyy)) display.reportValidity();
                else setHiddenFromParts(mm,dd,yyyy);
            } else {
                // check mm/dd/yy (2-digit year)
                var n = display.value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2})$/);
                if(n){
                    var mm2 = n[1], dd2 = n[2], yy2 = n[3];
                    var exp = expandTwoDigitYear(yy2);
                    if(!exp || !validateYearParts(String(exp))){ display.reportValidity(); }
                    else { setHiddenFromParts(mm2,dd2,String(exp)); display.value = mm2 + '/' + dd2 + '/' + String(exp); }
                } else {
                    display.setCustomValidity('Please enter date as mm/dd/yyyy');
                    display.reportValidity();
                }
            }
        });

        // Ensure hidden value is synced before form submit
        var form = display.closest('form');
        if(form){
            form.addEventListener('submit', function(e){
                // if display empty, allow
                if(!display.value) return;
                var mmddyyyy = display.value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                var mmddyy = display.value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2})$/);
                if(mmddyyyy){ setHiddenFromParts(mmddyyyy[1], mmddyyyy[2], mmddyyyy[3]); }
                else if(mmddyy){ var exp = expandTwoDigitYear(mmddyy[3]); if(exp) setHiddenFromParts(mmddyy[1], mmddyy[2], String(exp)); }
                else { e.preventDefault(); display.setCustomValidity('Please enter date as mm/dd/yyyy'); display.reportValidity(); }
            });
        }
    });
});
</script>
