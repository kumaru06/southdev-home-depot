<?php
/**
 * Brief branded bridge after Google OAuth success.
 * @var array $signingIn
 */
$pageTitle = $pageTitle ?? 'Signing you in';
$firstName = htmlspecialchars($signingIn['first_name'] ?? 'there');
$avatar    = $signingIn['avatar'] ?? null;
$isNew     = !empty($signingIn['is_new']);
$redirect  = $signingIn['redirect'] ?? (APP_URL . '/index.php?url=products');
$message   = $signingIn['message']
    ?? ($isNew
        ? ('Account created. Welcome to ' . APP_NAME . '!')
        : ('Welcome back, ' . ($signingIn['first_name'] ?? 'there') . '!'));
$initial   = strtoupper(substr($signingIn['first_name'] ?? 'U', 0, 1));

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<main class="google-signing-page" aria-live="polite">
    <div class="google-signing-card">
        <?php if ($avatar): ?>
            <img
                class="google-signing-avatar"
                src="<?= htmlspecialchars($avatar) ?>"
                alt="<?= $firstName ?>"
                width="72"
                height="72"
                referrerpolicy="no-referrer"
            >
        <?php else: ?>
            <div class="google-signing-avatar google-signing-avatar-fallback" aria-hidden="true"><?= htmlspecialchars($initial) ?></div>
        <?php endif; ?>

        <div class="google-signing-spinner" aria-hidden="true"></div>

        <h1><?= $isNew ? 'Setting up your account' : 'Signing you in' ?></h1>
        <p>
            <?= $isNew
                ? 'Welcome, ' . $firstName . '. We&rsquo;re finishing your Southdev account…'
                : 'Welcome back, ' . $firstName . '. Taking you to where you left off…' ?>
        </p>
        <div class="google-signing-brand"><?= htmlspecialchars(APP_NAME) ?></div>
    </div>
</main>

<script>
(function () {
    var redirectTo = <?= json_encode($redirect, JSON_UNESCAPED_SLASHES) ?>;
    var welcomeMsg = <?= json_encode($message, JSON_UNESCAPED_UNICODE) ?>;

    try {
        localStorage.setItem('__pendingToast', JSON.stringify({
            message: welcomeMsg,
            type: 'success'
        }));
    } catch (err) {}

    setTimeout(function () {
        window.location.replace(redirectTo);
    }, 1200);
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
