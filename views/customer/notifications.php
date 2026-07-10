<?php
/* $pageTitle, $extraCss, $notifications, $unreadCount, $totalCount, $shownCount,
   $selectedNotifDate, $hasNotifDateFilter set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$readCount = max(0, ($totalCount ?? 0) - ($unreadCount ?? 0));
$iconMap = [
    'order_processing' => 'package',
    'order_shipped'    => 'truck',
    'order_delivered'  => 'check-circle',
    'order_cancelled'  => 'x-circle',
    'order_update'     => 'bell',
    'order'            => 'bell',
    'cancel_approved'  => 'check-circle',
    'cancel_rejected'  => 'x-circle',
    'return_requested' => 'rotate-ccw',
    'return_approved'  => 'rotate-ccw',
    'return_rejected'  => 'x-circle',
    'return_completed' => 'badge-check',
];
?>

<div class="container notifications-page">
    <section class="notifications-hero-panel">
        <div class="notifications-hero-copy">
            <div class="page-heading-row notifications-heading-row">
                <h1 class="page-heading">Notifications</h1>
                <?php if ($unreadCount > 0): ?>
                    <span class="page-heading-badge"><?= $unreadCount ?> unread</span>
                <?php endif; ?>
            </div>
            <p class="notifications-hero-subtitle">Stay updated on your orders, returns, and account activity in one place.</p>
        </div>

        <?php if (($totalCount ?? 0) > 0): ?>
            <div class="notifications-hero-stats" aria-label="Notification overview">
                <div class="notifications-stat-card">
                    <strong><?= $totalCount ?></strong>
                    <span>Total</span>
                </div>
                <div class="notifications-stat-card">
                    <strong><?= $unreadCount ?></strong>
                    <span>Unread</span>
                </div>
                <div class="notifications-stat-card">
                    <strong><?= $readCount ?></strong>
                    <span>Read</span>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="notifications-toolbar" aria-label="Notification filters">
        <form method="GET" action="<?= APP_URL ?>/index.php" class="notifications-filter-form">
            <input type="hidden" name="url" value="notifications">
            <label for="notifDate" class="notifications-filter-label">
                <i data-lucide="calendar" style="width:14px;height:14px"></i>
                Filter by date
            </label>
            <input
                type="date"
                id="notifDate"
                name="notif_date"
                class="form-control notifications-filter-input"
                value="<?= htmlspecialchars($selectedNotifDate ?? '') ?>"
            >
            <button type="submit" class="btn btn-accent btn-sm">Apply</button>
            <?php if (!empty($hasNotifDateFilter)): ?>
                <a href="<?= APP_URL ?>/index.php?url=notifications" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <div class="notifications-toolbar-actions">
            <div class="notifications-toolbar-summary">
                <?php if (!empty($hasNotifDateFilter)): ?>
                    <span><?= $shownCount ?> notification<?= $shownCount !== 1 ? 's' : '' ?> on <?= date('M d, Y', strtotime($selectedNotifDate)) ?></span>
                <?php elseif ($shownCount >= $totalCount): ?>
                    <span>All <?= $totalCount ?> notification<?= $totalCount !== 1 ? 's' : '' ?></span>
                <?php else: ?>
                    <span>Showing <?= $shownCount ?> of <?= $totalCount ?> notification<?= $totalCount !== 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>

            <?php if ($unreadCount > 0): ?>
                <a href="<?= APP_URL ?>/index.php?url=notifications/mark-all-read" class="btn btn-outline btn-sm">
                    <i data-lucide="check-check" style="width:15px;height:15px"></i>
                    Mark all read
                </a>
            <?php endif; ?>
        </div>
    </section>

    <section class="notifications-panel" aria-label="Notifications list">
        <div class="notifications-panel-head">
            <div>
                <h2 class="notifications-panel-title">Recent updates</h2>
                <p class="notifications-panel-subtitle">Scroll inside the box to browse your notifications.</p>
            </div>
            <?php if (!empty($notifications)): ?>
                <span class="notifications-panel-count">
                    <?= ($shownCount >= $totalCount) ? $totalCount . ' total' : $shownCount . ' shown' ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($notifications)): ?>
            <div class="notifications-list-scroll">
                <div class="notifications-list">
                    <?php foreach ($notifications as $idx => $notif): ?>
                        <?php $icon = $iconMap[$notif['type']] ?? 'bell'; ?>
                        <a href="<?= APP_URL ?>/index.php?url=notifications/read/<?= $notif['id'] ?>"
                           class="notif-card <?= $notif['is_read'] ? '' : 'notif-card--unread' ?>"
                           style="animation-delay: <?= $idx * 0.03 ?>s">
                            <div class="notif-icon notif-icon--<?= htmlspecialchars($notif['type']) ?>">
                                <i data-lucide="<?= $icon ?>"></i>
                            </div>
                            <div class="notif-content">
                                <div class="notif-title-row">
                                    <h4 class="notif-title"><?= htmlspecialchars($notif['title']) ?></h4>
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="notif-status-pill">New</span>
                                    <?php endif; ?>
                                </div>
                                <p class="notif-message"><?= htmlspecialchars($notif['message']) ?></p>
                                <span class="notif-time">
                                    <?php
                                    $dt = new DateTime($notif['created_at']);
                                    $now = new DateTime();
                                    $diff = $now->diff($dt);
                                    if ($diff->days == 0) {
                                        if ($diff->h > 0) echo $diff->h . 'h ago';
                                        elseif ($diff->i > 0) echo $diff->i . 'm ago';
                                        else echo 'Just now';
                                    } elseif ($diff->days == 1) {
                                        echo 'Yesterday';
                                    } elseif ($diff->days < 7) {
                                        echo $diff->days . 'd ago';
                                    } else {
                                        echo $dt->format('M j, Y');
                                    }
                                    ?>
                                </span>
                            </div>
                            <span class="notif-chevron" aria-hidden="true">
                                <i data-lucide="chevron-right"></i>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="notifications-empty-state">
                <?php if (!empty($hasNotifDateFilter)): ?>
                    <i data-lucide="calendar-x" class="notifications-empty-icon"></i>
                    <h3>No notifications on this date</h3>
                    <p>Try another date or clear the filter to see your full notification history.</p>
                    <a href="<?= APP_URL ?>/index.php?url=notifications" class="btn btn-accent btn-sm">Clear filter</a>
                <?php else: ?>
                    <i data-lucide="bell-off" class="notifications-empty-icon"></i>
                    <h3>No notifications yet</h3>
                    <p>You'll be notified when your orders are updated.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
