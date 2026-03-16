<?php
/**
 * SouthDev Home Depot – Report Controller
 * Enhanced: Sales + Inventory + Returns reporting with tabbed view
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/ReturnRequest.php';
require_once __DIR__ . '/../models/StockMovement.php';

class ReportController {
    private $orderModel;
    private $orderItemModel;
    private $productModel;
    private $userModel;
    private $inventoryModel;
    private $returnModel;
    private $stockMovementModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->orderModel     = new Order($pdo);
        $this->orderItemModel = new OrderItem($pdo);
        $this->productModel   = new Product($pdo);
        $this->userModel      = new User($pdo);
        $this->inventoryModel = new Inventory($pdo);
        $this->returnModel    = new ReturnRequest($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
    }

    public function index() {
        AuthMiddleware::adminOrStaffOrInventory();

        // ===== SALES DATA =====
        $totalSales  = $this->orderModel->getTotalSales();
        $topProducts = $this->orderItemModel->getTopProducts(10);

        // Monthly sales data
        $stmt = $this->pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total FROM orders WHERE status != 'cancelled' GROUP BY month ORDER BY month DESC LIMIT 12");
        $monthlySales = $stmt->fetchAll();

        // Order status counts
        $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $orderStatusCounts = $stmt->fetchAll();

        // Total customers
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users WHERE role_id = 1");
        $totalCustomers = $stmt->fetch()['count'] ?? 0;

        // Total orders
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM orders");
        $totalOrders = $stmt->fetch()['count'] ?? 0;

        // ===== INVENTORY DATA =====
        $allInventory = $this->inventoryModel->getAll();
        $lowStockItems = $this->inventoryModel->getLowStock();
        $totalInventoryValue = 0;
        $totalStockUnits = 0;
        $outOfStockCount = 0;
        foreach ($allInventory as $inv) {
            $totalInventoryValue += floatval($inv['price']) * intval($inv['quantity']);
            $totalStockUnits += intval($inv['quantity']);
            if (intval($inv['quantity']) <= 0) {
                $outOfStockCount++;
            }
        }

        // Stock movement summary (last 30 days)
        $dateFrom = date('Y-m-d', strtotime('-30 days'));
        $dateTo = date('Y-m-d');
        $stockSummary = $this->stockMovementModel->getSummary($dateFrom, $dateTo);

        // ===== RETURNS DATA =====
        $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM return_requests GROUP BY status");
        $returnStatusCounts = $stmt->fetchAll();
        $totalReturns = 0;
        foreach ($returnStatusCounts as $r) {
            $totalReturns += intval($r['count']);
        }

        // Recent returns
        $stmt = $this->pdo->query("
            SELECT rr.*, o.order_number, u.first_name, u.last_name
            FROM return_requests rr
            JOIN orders o ON rr.order_id = o.id
            JOIN users u ON rr.user_id = u.id
            ORDER BY rr.created_at DESC LIMIT 10
        ");
        $recentReturns = $stmt->fetchAll();

        // Monthly returns trend
        $stmt = $this->pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM return_requests GROUP BY month ORDER BY month DESC LIMIT 12");
        $monthlyReturns = $stmt->fetchAll();

        $pageTitle = 'Reports';
        $isAdmin   = true;
        $extraCss  = ['admin.css', 'dashboard.css'];
        require_once VIEWS_PATH . '/staff/reports.php';
    }
}
