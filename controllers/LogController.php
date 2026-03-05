<?php
/**
 * SouthDev Home Depot – Log Controller
 */

require_once __DIR__ . '/../models/Log.php';

class LogController {
    private $logModel;

    public function __construct($pdo) {
        $this->logModel = new Log($pdo);
    }

    public function index() {
        AuthMiddleware::superAdmin();

        $page    = max(1, intval($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $filters = [];
        if (!empty($_GET['action']))    $filters['action']    = $_GET['action'];
        if (!empty($_GET['user_id']))   $filters['user_id']   = intval($_GET['user_id']);
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to']))   $filters['date_to']   = $_GET['date_to'];
        if (!empty($_GET['search']))    $filters['search']    = $_GET['search'];

        $logs        = $this->logModel->getAll($filters, $perPage, $offset);
        $totalLogs   = $this->logModel->count($filters);
        $totalPages  = ceil($totalLogs / $perPage);
        $currentPage = $page;
        $actionTypes = $this->logModel->getActionTypes();

        $pageTitle = 'System Logs';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/superadmin/logs.php';
    }
}
