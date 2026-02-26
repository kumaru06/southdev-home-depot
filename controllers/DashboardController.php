<?php
/**
 * SouthDev Home Depot – Dashboard Controller
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/CancelRequest.php';

class DashboardController {
    private $pdo;
    private $orderModel;
    private $productModel;
    private $userModel;
    private $inventoryModel;
    private $logModel;
    private $cancelModel;

    public function __construct($pdo) {
        $this->pdo            = $pdo;
        $this->orderModel     = new Order($pdo);
        $this->productModel   = new Product($pdo);
        $this->userModel      = new User($pdo);
        $this->inventoryModel = new Inventory($pdo);
        $this->logModel       = new Log($pdo);
        $this->cancelModel    = new CancelRequest($pdo);
    }

    public function index() {
        AuthMiddleware::adminOrStaff();

        // --- Stat cards ---
        $totalSales     = $this->orderModel->getTotalSales();
        $pendingOrders  = $this->orderModel->countByStatus('pending');
        $totalOrders    = $this->orderModel->countByStatus();
        $pendingCancels = $this->cancelModel->countPending();

        $stmt = $this->pdo->query("SELECT COUNT(*) as c FROM users WHERE role_id = 1");
        $totalCustomers = $stmt->fetch()['c'] ?? 0;

        $stmt2 = $this->pdo->query("SELECT COUNT(*) as c FROM products WHERE is_active = 1");
        $totalProducts = $stmt2->fetch()['c'] ?? 0;

        // --- Charts data ---
        // Monthly sales (last 12 months)
        $stmt = $this->pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total FROM orders WHERE status != 'cancelled' GROUP BY month ORDER BY month ASC LIMIT 12");
        $monthlySales = $stmt->fetchAll();
        $chartLabels = [];
        $chartData   = [];
        foreach ($monthlySales as $row) {
            $chartLabels[] = date('M Y', strtotime($row['month'] . '-01'));
            $chartData[]   = floatval($row['total']);
        }

        // Orders by status
        $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $orderStatusCounts = $stmt->fetchAll();
        $statusLabels = [];
        $statusData   = [];
        foreach ($orderStatusCounts as $row) {
            $statusLabels[] = ucfirst($row['status']);
            $statusData[]   = intval($row['count']);
        }

        // Top categories by revenue
        $stmt = $this->pdo->query("
            SELECT c.name, SUM(oi.subtotal) as revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status != 'cancelled'
            GROUP BY c.id
            ORDER BY revenue DESC
            LIMIT 5
        ");
        $categoryRevenue = $stmt->fetchAll();
        $catLabels = [];
        $catData   = [];
        foreach ($categoryRevenue as $row) {
            $catLabels[] = $row['name'];
            $catData[]   = floatval($row['revenue']);
        }

        // --- Recent activity ---
        $recentOrders = $this->orderModel->getRecentOrders(5);
        $recentLogs   = $this->logModel->getRecent(8);

        // --- Low stock alerts ---
        $lowStock = $this->inventoryModel->getLowStock();

        // --- Top selling products ---
        $stmt = $this->pdo->query("
            SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 5
        ");
        $topProducts = $stmt->fetchAll();

        $pageTitle = 'Dashboard';
        $isAdmin   = true;
        $extraCss  = ['admin.css', 'dashboard.css'];
        $extraJs   = ['charts.js'];

        if ($_SESSION['role_id'] == ROLE_SUPER_ADMIN) {
            require_once VIEWS_PATH . '/superadmin/dashboard.php';
        } else {
            require_once VIEWS_PATH . '/staff/dashboard.php';
        }
    }
}
