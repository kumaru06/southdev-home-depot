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

        // --- CSV export handler ---
        if (isset($_GET['export'])) {
            $export = $_GET['export'];
            $dateFrom = !empty($_GET['date_from']) ? $_GET['date_from'] : null;
            $dateTo = !empty($_GET['date_to']) ? $_GET['date_to'] : null;

            // Inventory export: categorized list with Current Stock and Added Stock
            if ($export === 'inventory') {
                // We'll produce a CSV where each row is: Category, SKU, Product, Current Stock, Added Stock
                // 'Added Stock' is the sum of positive stock_movements.quantity (added) optionally within a date range.
                $dateCondition = '';
                $params = [];
                if ($dateFrom && $dateTo) {
                    // subquery will filter by created_at between supplied dates
                    $dateCondition = ' AND smf.created_at BETWEEN ? AND ? ';
                    $params[] = $dateFrom;
                    $params[] = $dateTo;
                }

                $sql = "SELECT c.name as category, p.sku, p.name as product_name, COALESCE(i.quantity,0) as current_stock,
                               COALESCE(smf.added_qty,0) as added_stock
                        FROM products p
                        JOIN categories c ON p.category_id = c.id
                        LEFT JOIN inventory i ON p.id = i.product_id
                        LEFT JOIN (
                            SELECT product_id, SUM(quantity) as added_qty
                            FROM stock_movements smf
                            WHERE smf.quantity > 0 " . $dateCondition . "
                            GROUP BY product_id
                        ) smf ON smf.product_id = p.id
                        WHERE p.is_active = 1
                        ORDER BY c.name, p.name";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll();

                $filename = 'inventory_by_category_' . date('Ymd_His') . '.csv';
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
                $out = fopen('php://output', 'w');

                // Header
                fputcsv($out, ['Category', 'SKU', 'Product', 'Current Stock', 'Added Stock']);

                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r['category'] ?? '',
                        $r['sku'] ?? '',
                        $r['product_name'] ?? '',
                        intval($r['current_stock']),
                        intval($r['added_stock'])
                    ]);
                }

                fclose($out);
                exit;
            }

            // Sales export: support raw rows or aggregated daily/monthly
            if ($export === 'sales') {
                $period = $_GET['period'] ?? 'rows'; // 'rows'|'daily'|'monthly'
                if ($period === 'daily' || $period === 'monthly') {
                    if ($period === 'daily') {
                        $groupExpr = "DATE(o.created_at)";
                        $label = 'Date';
                        $orderBy = "DATE(o.created_at) DESC";
                    } else {
                        $groupExpr = "DATE_FORMAT(o.created_at, '%Y-%m')";
                        $label = 'Month';
                        $orderBy = "DATE_FORMAT(o.created_at, '%Y-%m') DESC";
                    }
                    $sql = "SELECT {$groupExpr} as period, COUNT(DISTINCT o.id) as orders_count, SUM(oi.quantity) as units_sold, SUM(oi.subtotal) as revenue
                            FROM order_items oi
                            JOIN orders o ON oi.order_id = o.id
                            LEFT JOIN payments pay ON pay.order_id = o.id
                            WHERE o.status NOT IN ('cancelled')
                            AND (pay.status IS NULL OR pay.status != 'refunded')";
                    $params = [];
                    if ($dateFrom && $dateTo) {
                        $sql .= " AND o.created_at BETWEEN ? AND ?";
                        $params[] = $dateFrom;
                        $params[] = $dateTo;
                    }
                    $sql .= " GROUP BY period ORDER BY {$orderBy}";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($params);
                    $rows = $stmt->fetchAll();

                    $filename = 'sales_aggregated_' . $period . '_' . date('Ymd_His') . '.csv';
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    echo "\xEF\xBB\xBF";
                    $out = fopen('php://output', 'w');
                    fputcsv($out, [$label, 'Orders', 'Units Sold', 'Revenue']);
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r['period'],
                            intval($r['orders_count']),
                            intval($r['units_sold']),
                            number_format(floatval($r['revenue']), 2)
                        ]);
                    }
                    fclose($out);
                    exit;
                } else {
                    // default: raw order-item rows (existing behavior)
                    $sql = "SELECT o.order_number, o.created_at, u.first_name, u.last_name,
                                   p.sku, p.name as product_name, oi.quantity, oi.price, oi.subtotal, o.total_amount, o.status
                            FROM order_items oi
                            JOIN orders o ON oi.order_id = o.id
                            JOIN products p ON oi.product_id = p.id
                            JOIN users u ON o.user_id = u.id
                            LEFT JOIN payments pay ON pay.order_id = o.id
                            WHERE o.status NOT IN ('cancelled')
                            AND (pay.status IS NULL OR pay.status != 'refunded')";
                    $params = [];
                    if ($dateFrom && $dateTo) {
                        $sql .= " AND o.created_at BETWEEN ? AND ?";
                        $params[] = $dateFrom;
                        $params[] = $dateTo;
                    }
                    $sql .= " ORDER BY o.created_at DESC";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($params);
                    $rows = $stmt->fetchAll();

                    $filename = 'sales_report_' . date('Ymd_His') . '.csv';
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    echo "\xEF\xBB\xBF";
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['Order #', 'Order Date', 'Customer', 'SKU', 'Product', 'Qty', 'Price', 'Subtotal', 'Order Total', 'Status']);
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r['order_number'],
                            $r['created_at'],
                            trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                            $r['sku'] ?? '',
                            $r['product_name'] ?? '',
                            intval($r['quantity']),
                            number_format(floatval($r['price']), 2),
                            number_format(floatval($r['subtotal']), 2),
                            number_format(floatval($r['total_amount']), 2),
                            $r['status']
                        ]);
                    }
                    fclose($out);
                    exit;
                }
            }
        }

        // ===== SALES DATA =====
        $totalSales  = $this->orderModel->getTotalSales();
        $topProducts = $this->orderItemModel->getTopProducts(10);

        // Monthly sales data
        $stmt = $this->pdo->query("
            SELECT DATE_FORMAT(o.created_at, '%Y-%m') as month, SUM(o.total_amount) as total
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.status != 'cancelled'
            AND (p.status IS NULL OR p.status != 'refunded')
            GROUP BY month ORDER BY month DESC LIMIT 12
        ");
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
            // Prefer `cost` for accounting/valuation when available; fall back to selling `price` otherwise.
            $unitVal = 0.0;
            if (isset($inv['cost']) && $inv['cost'] !== null && $inv['cost'] !== '') {
                $unitVal = floatval($inv['cost']);
            } else {
                $unitVal = floatval($inv['price']);
            }
            $totalInventoryValue += $unitVal * intval($inv['quantity']);
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
