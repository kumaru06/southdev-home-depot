<?php
/**
 * Complete Google account — required profile details for new Google sign-ups.
 * @var array $pendingGoogle
 */
$pageTitle = $pageTitle ?? 'Complete your account';

$firstName = htmlspecialchars($pendingGoogle['first_name'] ?? '');
$lastName  = htmlspecialchars($pendingGoogle['last_name'] ?? '');
$email     = htmlspecialchars($pendingGoogle['email'] ?? '');
$username  = htmlspecialchars($pendingGoogle['suggested_username'] ?? '');
$avatar    = $pendingGoogle['avatar'] ?? null;
$initial   = strtoupper(substr($pendingGoogle['first_name'] ?? 'U', 0, 1));

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card card google-complete-card">
            <div class="auth-panel">
                <div class="google-complete-header">
                    <?php if ($avatar): ?>
                        <img
                            class="google-complete-avatar"
                            src="<?= htmlspecialchars($avatar) ?>"
                            alt="<?= $firstName ?>"
                            width="56"
                            height="56"
                            referrerpolicy="no-referrer"
                        >
                    <?php else: ?>
                        <div class="google-complete-avatar google-complete-avatar-fallback" aria-hidden="true"><?= htmlspecialchars($initial) ?></div>
                    <?php endif; ?>
                    <div>
                        <h2>Complete your account</h2>
                        <p class="auth-tagline">One more step — confirm your details to finish signing up with Google.</p>
                    </div>
                </div>

                <?php if (has_flash('error')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars(flash('error'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <div class="google-complete-email">
                    <span class="google-complete-email-label">Google account</span>
                    <strong><?= $email ?></strong>
                </div>

                <form
                    action="<?= APP_URL ?>/index.php?url=complete-google-account"
                    method="POST"
                    id="complete-google-form"
                    class="google-complete-form"
                    novalidate
                >
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    class="form-control fl-input"
                                    placeholder=" "
                                    value="<?= $firstName ?>"
                                    autocomplete="given-name"
                                    required
                                >
                                <label for="first_name" class="fl-label">First Name <span class="required">*</span></label>
                            </div>
                        </div>
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    class="form-control fl-input"
                                    placeholder=" "
                                    value="<?= $lastName ?>"
                                    autocomplete="family-name"
                                    required
                                >
                                <label for="last_name" class="fl-label">Last Name <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group fl-group">
                        <div class="fl-wrap">
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control fl-input"
                                placeholder=" "
                                value="<?= $username ?>"
                                autocomplete="username"
                                required
                                minlength="3"
                                maxlength="30"
                                pattern="[a-zA-Z0-9._-]+"
                            >
                            <label for="username" class="fl-label">Username <span class="required">*</span></label>
                        </div>
                        <small class="field-hint">Letters, numbers, dots, dashes, underscores. 3–30 characters.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap phone-fl-wrap">
                                <span class="phone-prefix-fl">+63</span>
                                <input
                                    type="text"
                                    id="phone_number"
                                    class="fl-input"
                                    placeholder=" "
                                    autocomplete="tel"
                                    inputmode="numeric"
                                    maxlength="12"
                                >
                                <label for="phone_number" class="fl-label phone-fl-label">Phone Number</label>
                                <input type="hidden" id="phone" name="phone">
                            </div>
                            <small class="field-hint">Optional · e.g. 912 345 6789</small>
                        </div>
                        <div class="form-group form-col fl-group">
                            <div class="fl-wrap">
                                <input
                                    type="date"
                                    id="birthdate"
                                    name="birthdate"
                                    class="form-control fl-input fl-input--date"
                                    placeholder=" "
                                    required
                                    min="1960-01-01"
                                    max="<?= date('Y-m-d') ?>"
                                >
                                <label for="birthdate" class="fl-label">Birthdate <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent btn-block" id="complete-google-submit">
                        Create account
                    </button>
                </form>

                <div class="auth-footer">
                    <p>
                        Wrong Google account?
                        <a href="<?= APP_URL ?>/index.php?url=google-auth">Choose another</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var numberInput = document.getElementById('phone_number');
    var hiddenPhone = document.getElementById('phone');
    var form = document.getElementById('complete-google-form');
    if (!numberInput || !hiddenPhone) return;

    function getDigits(val) {
        return String(val || '').replace(/\D/g, '').slice(0, 10);
    }

    function formatPhone(digits) {
        if (digits.length > 6) return digits.slice(0, 3) + ' ' + digits.slice(3, 6) + ' ' + digits.slice(6);
        if (digits.length > 3) return digits.slice(0, 3) + ' ' + digits.slice(3);
        return digits;
    }

    function syncPhone() {
        var digits = getDigits(numberInput.value);
        numberInput.value = formatPhone(digits);
        hiddenPhone.value = digits ? '+63' + digits : '';
    }

    numberInput.addEventListener('input', syncPhone);

    if (form) {
        form.addEventListener('submit', function () {
            syncPhone();
        });
    }
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
