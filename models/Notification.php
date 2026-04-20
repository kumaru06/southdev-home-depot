<?php
/**
 * SouthDev Home Depot – Notification Model
 */

class Notification {
    private $pdo;
    private $tableExists = null;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Auto-create the notifications table if it doesn't exist
     */
    private function ensureTable() {
        if ($this->tableExists !== null) return $this->tableExists;
        try {
            $this->pdo->query("SELECT 1 FROM notifications LIMIT 1");
            $this->tableExists = true;
        } catch (\Exception $e) {
            try {
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS notifications (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        message TEXT NOT NULL,
                        type VARCHAR(50) NOT NULL DEFAULT 'order',
                        link VARCHAR(500) DEFAULT NULL,
                        is_read TINYINT(1) NOT NULL DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_user_read (user_id, is_read),
                        INDEX idx_user_created (user_id, created_at DESC)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
                $this->tableExists = true;
            } catch (\Exception $ex) {
                $this->tableExists = false;
            }
        }
        return $this->tableExists;
    }

    /**
     * Create a notification for a user
     */
    public function create($userId, $title, $message, $type = 'order', $link = null) {
        if (!$this->ensureTable()) return false;
        $stmt = $this->pdo->prepare(
            "INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$userId, $title, $message, $type, $link]);
    }

    /**
     * Get all notifications for a user (latest first), with optional limit
     */
    public function getByUserId($userId, $limit = 50) {
        if (!$this->ensureTable()) return [];
        $stmt = $this->pdo->prepare(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId) {
        if (!$this->ensureTable()) return 0;
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0"
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get recent unread notifications (for dropdown preview)
     */
    public function getRecentUnread($userId, $limit = 5) {
        if (!$this->ensureTable()) return [];
        $stmt = $this->pdo->prepare(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 
             ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead($id, $userId) {
        if (!$this->ensureTable()) return false;
        $stmt = $this->pdo->prepare(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?"
        );
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        if (!$this->ensureTable()) return false;
        $stmt = $this->pdo->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0"
        );
        return $stmt->execute([$userId]);
    }

    /**
     * Delete old notifications (cleanup — keep last 90 days)
     */
    public function cleanup($days = 90) {
        if (!$this->ensureTable()) return false;
        $stmt = $this->pdo->prepare(
            "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        return $stmt->execute([$days]);
    }

    /**
     * Helper: create order-related notification with appropriate icon/message
     */
    public static function orderStatusMessage($status, $orderNumber) {
        $map = [
            'processing' => [
                'title' => 'Order Confirmed',
                'message' => "Your order #{$orderNumber} has been confirmed and is being processed.",
                'type' => 'order_processing'
            ],
            'shipped' => [
                'title' => 'Order Shipped',
                'message' => "Your order #{$orderNumber} has been shipped! It's on its way.",
                'type' => 'order_shipped'
            ],
            'delivered' => [
                'title' => 'Order Delivered',
                'message' => "Your order #{$orderNumber} has been delivered. Enjoy your purchase!",
                'type' => 'order_delivered'
            ],
            'cancelled' => [
                'title' => 'Order Cancelled',
                'message' => "Your order #{$orderNumber} has been cancelled.",
                'type' => 'order_cancelled'
            ],
        ];
        return $map[$status] ?? [
            'title' => 'Order Updated',
            'message' => "Your order #{$orderNumber} status changed to: {$status}.",
            'type' => 'order_update'
        ];
    }
}
