<?php
/**
 * SouthDev Home Depot – Notification Controller
 */

require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $notificationModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->notificationModel = new Notification($pdo);
    }

    /**
     * Show all notifications page
     */
    public function index() {
        AuthMiddleware::handle();
        $pageTitle = 'Notifications';
        $notifications = $this->notificationModel->getByUserId($_SESSION['user_id'], 100);
        $unreadCount = $this->notificationModel->getUnreadCount($_SESSION['user_id']);
        $extraCss = ['customer.css'];
        require_once VIEWS_PATH . '/customer/notifications.php';
    }

    /**
     * Mark single notification as read and redirect to its link
     */
    public function read($id) {
        AuthMiddleware::handle();
        $this->notificationModel->markAsRead($id, $_SESSION['user_id']);

        // Get the notification to find its link
        $notifications = $this->notificationModel->getByUserId($_SESSION['user_id'], 200);
        $link = null;
        foreach ($notifications as $n) {
            if ($n['id'] == $id && !empty($n['link'])) {
                $link = $n['link'];
                break;
            }
        }

        if ($link) {
            header('Location: ' . $link);
        } else {
            header('Location: ' . APP_URL . '/index.php?url=notifications');
        }
        exit;
    }

    /**
     * Mark all notifications as read (AJAX or redirect)
     */
    public function markAllRead() {
        AuthMiddleware::handle();
        $this->notificationModel->markAllAsRead($_SESSION['user_id']);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: ' . APP_URL . '/index.php?url=notifications');
        exit;
    }

    /**
     * AJAX: get unread count + recent notifications (for bell dropdown)
     */
    public function apiUnread() {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $count = $this->notificationModel->getUnreadCount($_SESSION['user_id']);
        $recent = $this->notificationModel->getByUserId($_SESSION['user_id'], 10);

        echo json_encode([
            'count' => $count,
            'notifications' => array_map(function($n) {
                return [
                    'id' => $n['id'],
                    'title' => $n['title'],
                    'message' => $n['message'],
                    'type' => $n['type'],
                    'link' => $n['link'],
                    'is_read' => (int)$n['is_read'],
                    'created_at' => $n['created_at'],
                    'time_ago' => $this->timeAgo($n['created_at'])
                ];
            }, $recent)
        ]);
        exit;
    }

    private function timeAgo($datetime) {
        $now = new \DateTime();
        $past = new \DateTime($datetime);
        $diff = $now->diff($past);

        if ($diff->y > 0) return $diff->y . 'y ago';
        if ($diff->m > 0) return $diff->m . 'mo ago';
        if ($diff->d > 0) return $diff->d . 'd ago';
        if ($diff->h > 0) return $diff->h . 'h ago';
        if ($diff->i > 0) return $diff->i . 'm ago';
        return 'Just now';
    }
}
