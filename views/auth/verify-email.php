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

            <?php if (!empty($devOtp)): ?>
            <div style="background:#fffbeb;border:1.5px solid #f59e0b;border-radius:10px;padding:1rem 1.2rem;margin-bottom:1.2rem;text-align:center;">
                <p style="margin:0 0 .4rem;font-size:12px;font-weight:600;color:#92400e;text-transform:uppercase;letter-spacing:.5px;">&#9888; Local Dev — Email Not Sent</p>
                <p style="margin:0 0 .6rem;font-size:13px;color:#78350f;">Use this OTP to verify your account:</p>
                <div style="display:flex;align-items:center;justify-content:center;gap:10px;">
                    <span id="dev-otp-code" style="font-size:2rem;font-weight:700;letter-spacing:.35rem;color:#b45309;font-family:monospace;"><?= htmlspecialchars($devOtp) ?></span>
                    <button type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($devOtp) ?>');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500);" style="font-size:11px;padding:4px 10px;border:1px solid #f59e0b;background:#fef3c7;color:#92400e;border-radius:6px;cursor:pointer;">Copy</button>
                </div>
            </div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/index.php?url=verify-otp" method="POST" id="verify-otp-form">
                <?= csrf_field() ?>

                <div class="form-group fl-group">
                    <div class="fl-wrap">
                        <input type="email" id="email" name="email" class="form-control fl-input" placeholder=" " value="<?= htmlspecialchars($_SESSION['pending_verify_email'] ?? '') ?>" autocomplete="email" required>
                        <label for="email" class="fl-label">Email Address</label>
                    </div>
                </div>

                <div class="form-group fl-group">
                    <div class="fl-wrap">
                        <input type="text" id="otp" name="otp" class="form-control fl-input" placeholder=" " maxlength="6" autocomplete="one-time-code" required>
                        <label for="otp" class="fl-label">OTP Code</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent" style="display:block;margin:0 auto;min-width:180px;padding-left:2rem;padding-right:2rem;">
                    Verify OTP
                </button>
            </form>

            <details style="margin-top:1rem;">
                <summary style="font-size:13px;color:var(--text-muted);cursor:pointer;user-select:none;">Didn&rsquo;t receive an email? Resend</summary>
                <form action="<?= APP_URL ?>/index.php?url=resend-verification" method="POST" style="margin-top:.75rem;display:flex;flex-direction:column;gap:.6rem;">
                    <?= csrf_field() ?>
                    <div class="form-group fl-group" style="margin-bottom:0;">
                        <div class="fl-wrap">
                            <input type="email" id="resend-email" name="email" class="form-control fl-input" placeholder=" " value="<?= htmlspecialchars($_SESSION['pending_verify_email'] ?? '') ?>" autocomplete="email" required>
                            <label for="resend-email" class="fl-label">Confirm your email address</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline" style="display:block;margin:0 auto;min-width:220px;padding-left:2rem;padding-right:2rem;">Resend Verification Email</button>
                </form>
            </details>

            <div class="auth-footer">
                <p>Already verified? <a href="<?= APP_URL ?>/index.php?url=login">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
