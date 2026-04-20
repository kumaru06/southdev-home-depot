<?php
$pageTitle = 'Verify Email';
require_once INCLUDES_PATH . '/header.php';
?>

<?php require_once INCLUDES_PATH . '/navbar.php'; ?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card card">
            <div class="auth-brand">
                <h2>Verify Your Email</h2>
                <p class="auth-tagline">Check your inbox for the verification link</p>
            </div>

            <div class="card" style="background:var(--neutral); padding:1rem; margin-bottom:1rem;">
                <p style="margin-bottom:.5rem; color:var(--text-secondary);">
                    We sent you a verification link and a one-time passcode (OTP).
                </p>
                <p style="margin-bottom:0; color:var(--text-secondary);">
                    You can verify using either the link or the OTP below.
                </p>
            </div>

            <form action="<?= APP_URL ?>/index.php?url=verify-otp" method="POST" id="verify-otp-form">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-icon-wrap">
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($_SESSION['pending_verify_email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="otp">OTP Code</label>
                    <div class="input-icon-wrap">
                        <input type="text" id="otp" name="otp" class="form-control" placeholder="Enter 6-digit code" maxlength="6" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent btn-block">
                    Verify OTP
                </button>
            </form>

            <!-- Dev OTP display removed to ensure OTPs are only delivered via email -->

            <form action="<?= APP_URL ?>/index.php?url=resend-verification" method="POST" style="margin-top:1rem;">
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['pending_verify_email'] ?? '') ?>">
                <button type="submit" class="btn btn-outline btn-block">
                    Resend Verification Email
                </button>
            </form>

            <div class="auth-footer">
                <p>Already verified? <a href="<?= APP_URL ?>/index.php?url=login">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
