<?php
/**
 * SouthDev Home Depot – System Log Model
 * Stores all auditable actions for admin review
 */

class Log {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Record a system log entry
     */
    public function create($action, $description = '', $userId = null) {
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $stmt = $this->pdo->prepare(
            "INSERT INTO logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$userId, $action, $description, $ip]);
    }

    /**
     * Get all logs with optional filters
     */
    public function getAll($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT l.*, u.first_name, u.last_name, u.email
                FROM logs l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['action'])) {
            $sql .= " AND l.action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND l.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND l.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (l.description LIKE ? OR l.action LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count logs with filters
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) FROM logs l WHERE 1=1";
        $params = [];

        if (!empty($filters['action'])) {
            $sql .= " AND l.action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = ?";
            $params[] = $filters['user_id'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Get distinct action types for filter dropdown
     */
    public function getActionTypes() {
        return $this->pdo->query("SELECT DISTINCT action FROM logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get recent activity for dashboard
     */
    public function getRecent($limit = 10) {
        $stmt = $this->pdo->prepare(
            "SELECT l.*, u.first_name, u.last_name
             FROM logs l LEFT JOIN users u ON l.user_id = u.id
             ORDER BY l.created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
