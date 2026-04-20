<?php
/* $pageTitle, $extraCss, $notifications, $unreadCount set by controller */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <div class="page-heading-row">
        <h1 class="page-heading">Notifications</h1>
        <?php if ($unreadCount > 0): ?>
            <span class="page-heading-badge"><?= $unreadCount ?> unread</span>
        <?php endif; ?>
    </div>

    <?php if ($unreadCount > 0): ?>
        <div class="notif-actions-bar">
            <a href="<?= APP_URL ?>/index.php?url=notifications/mark-all-read" class="btn btn-outline btn-sm">
                <i data-lucide="check-check" style="width:16px;height:16px"></i> Mark all as read
            </a>
        </div>
    <?php endif; ?>

    <?php if (!empty($notifications)): ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $idx => $notif): ?>
                <a href="<?= APP_URL ?>/index.php?url=notifications/read/<?= $notif['id'] ?>" 
                   class="notif-card <?= $notif['is_read'] ? '' : 'notif-card--unread' ?>"
                   style="animation-delay: <?= $idx * 0.03 ?>s">
                    <div class="notif-icon notif-icon--<?= htmlspecialchars($notif['type']) ?>">
                        <?php
                        $iconMap = [
                            'order_processing' => 'package',
                            'order_shipped'    => 'truck',
                            'order_delivered'  => 'check-circle',
                            'order_cancelled'  => 'x-circle',
                            'order_update'     => 'bell',
                            'order'            => 'bell',
                            'cancel_approved'  => 'check-circle',
                            'cancel_rejected'  => 'x-circle',
                            'return_approved'  => 'rotate-ccw',
                            'return_rejected'  => 'x-circle',
                        ];
                        $icon = $iconMap[$notif['type']] ?? 'bell';
                        ?>
                        <i data-lucide="<?= $icon ?>"></i>
                    </div>
                    <div class="notif-content">
                        <h4 class="notif-title"><?= htmlspecialchars($notif['title']) ?></h4>
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
                    <?php if (!$notif['is_read']): ?>
                        <span class="notif-dot"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i data-lucide="bell-off" style="width:48px;height:48px;color:var(--text-light);margin-bottom:12px"></i>
            <h3>No notifications yet</h3>
            <p>You'll be notified when your orders are updated.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
