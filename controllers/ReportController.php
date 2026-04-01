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
require_once __DIR__ . '/../models/DamagedProduct.php';
require_once __DIR__ . '/../models/Payment.php';

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
            return $this->handleExport($_GET['export']);
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

    /* ==========================================================
     *  EXPORT HANDLER – routes to the right CSV builder
     * ========================================================== */
    private function handleExport($type) {
        switch ($type) {
            case 'sales_daily':    return $this->exportSalesDaily();
            case 'sales_monthly':  return $this->exportSalesMonthly();
            case 'sales_rows':     return $this->exportSalesRows();
            case 'current_inventory': return $this->exportCurrentInventory();
            case 'inventory_added':   return $this->exportInventoryAdded();
            case 'damaged_inventory': return $this->exportDamagedInventory();
            // legacy compat
            case 'sales':
                $period = $_GET['period'] ?? 'rows';
                if ($period === 'daily')  return $this->exportSalesDaily();
                if ($period === 'monthly') return $this->exportSalesMonthly();
                return $this->exportSalesRows();
            case 'inventory':
                return $this->exportCurrentInventory();
            default:
                flash('error', 'Unknown export type.');
                header('Location: ' . APP_URL . '/index.php?url=staff/reports');
                exit;
        }
    }

    /* ----------------------------------------------------------
     *  HELPER: start a CSV download
     * ---------------------------------------------------------- */
    private function startCsv($filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel opens correctly
        return fopen('php://output', 'w');
    }

    /* ==========================================================
     *  1. SALES – Daily Aggregated
     * ========================================================== */
    private function exportSalesDaily() {
        $sql = "SELECT DATE(o.created_at) as sale_date,
                       COUNT(DISTINCT o.id) as total_orders,
                       SUM(oi.quantity) as units_sold,
                       SUM(oi.subtotal) as gross_revenue,
                       SUM(CASE WHEN pay.status = 'refunded' THEN oi.subtotal ELSE 0 END) as refunded_amount,
                       SUM(CASE WHEN pay.status != 'refunded' AND o.status != 'cancelled' THEN oi.subtotal ELSE 0 END) as net_revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                LEFT JOIN payments pay ON pay.order_id = o.id
                WHERE o.status != 'cancelled'
                AND (pay.status IS NULL OR pay.status != 'refunded')
                GROUP BY sale_date
                ORDER BY sale_date DESC";
        $rows = $this->pdo->query($sql)->fetchAll();

        $out = $this->startCsv('SouthDev_Sales_Daily_' . date('Y-m-d') . '.csv');

        // Title rows
        fputcsv($out, ['SOUTHDEV HOME DEPOT']);
        fputcsv($out, ['Sales Report - Daily Summary']);
        fputcsv($out, ['Generated:', date('F d, Y h:i A')]);
        fputcsv($out, []); // blank row

        fputcsv($out, ['Date', 'Total Orders', 'Units Sold', 'Revenue (PHP)']);

        $grandOrders = 0; $grandUnits = 0; $grandRevenue = 0;
        foreach ($rows as $r) {
            $revenue = floatval($r['net_revenue'] ?: $r['gross_revenue']);
            fputcsv($out, [
                date('M d, Y', strtotime($r['sale_date'])),
                intval($r['total_orders']),
                intval($r['units_sold']),
                number_format($revenue, 2)
            ]);
            $grandOrders  += intval($r['total_orders']);
            $grandUnits   += intval($r['units_sold']);
            $grandRevenue += $revenue;
        }

        fputcsv($out, []); // blank row
        fputcsv($out, ['TOTAL', $grandOrders, $grandUnits, number_format($grandRevenue, 2)]);

        fclose($out);
        exit;
    }

    /* ==========================================================
     *  2. SALES – Monthly Aggregated
     * ========================================================== */
    private function exportSalesMonthly() {
        $sql = "SELECT DATE_FORMAT(o.created_at, '%Y-%m') as sale_month,
                       COUNT(DISTINCT o.id) as total_orders,
                       SUM(oi.quantity) as units_sold,
                       SUM(oi.subtotal) as revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                LEFT JOIN payments pay ON pay.order_id = o.id
                WHERE o.status != 'cancelled'
                AND (pay.status IS NULL OR pay.status != 'refunded')
                GROUP BY sale_month
                ORDER BY sale_month DESC";
        $rows = $this->pdo->query($sql)->fetchAll();

        $out = $this->startCsv('SouthDev_Sales_Monthly_' . date('Y-m-d') . '.csv');

        fputcsv($out, ['SOUTHDEV HOME DEPOT']);
        fputcsv($out, ['Sales Report - Monthly Summary']);
        fputcsv($out, ['Generated:', date('F d, Y h:i A')]);
        fputcsv($out, []);

        fputcsv($out, ['Month', 'Total Orders', 'Units Sold', 'Revenue (PHP)']);

        $grandOrders = 0; $grandUnits = 0; $grandRevenue = 0;
        foreach ($rows as $r) {
            $rev = floatval($r['revenue']);
            fputcsv($out, [
                date('F Y', strtotime($r['sale_month'] . '-01')),
                intval($r['total_orders']),
                intval($r['units_sold']),
                number_format($rev, 2)
            ]);
            $grandOrders  += intval($r['total_orders']);
            $grandUnits   += intval($r['units_sold']);
            $grandRevenue += $rev;
        }

        fputcsv($out, []);
        fputcsv($out, ['TOTAL', $grandOrders, $grandUnits, number_format($grandRevenue, 2)]);

        fclose($out);
        exit;
    }

    /* ==========================================================
     *  3. SALES – Detailed Rows (every order item)
     * ========================================================== */
    private function exportSalesRows() {
        $sql = "SELECT o.order_number, o.created_at, o.status as order_status,
                       u.first_name, u.last_name,
                       p.sku, p.name as product_name, c.name as category,
                       oi.quantity, oi.price, oi.subtotal,
                       o.total_amount,
                       pay.payment_method, pay.status as payment_status
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON o.user_id = u.id
                LEFT JOIN payments pay ON pay.order_id = o.id
                WHERE o.status != 'cancelled'
                AND (pay.status IS NULL OR pay.status != 'refunded')
                ORDER BY o.created_at DESC";
        $rows = $this->pdo->query($sql)->fetchAll();

        $out = $this->startCsv('SouthDev_Sales_Detailed_' . date('Y-m-d') . '.csv');

        fputcsv($out, ['SOUTHDEV HOME DEPOT']);
        fputcsv($out, ['Sales Report - Detailed Transactions']);
        fputcsv($out, ['Generated:', date('F d, Y h:i A')]);
        fputcsv($out, []);

        fputcsv($out, ['Order #', 'Date', 'Customer', 'Category', 'SKU', 'Product', 'Qty', 'Unit Price (PHP)', 'Subtotal (PHP)', 'Order Total (PHP)', 'Payment Method', 'Payment Status', 'Order Status']);

        $grandTotal = 0;
        foreach ($rows as $r) {
            $pm = strtolower($r['payment_method'] ?? '');
            if (str_contains($pm, 'gcash')) $pmLabel = 'GCash';
            elseif (str_contains($pm, 'card')) $pmLabel = 'Card';
            elseif (str_contains($pm, 'cod') || str_contains($pm, 'cash')) $pmLabel = 'COD';
            else $pmLabel = ucfirst($r['payment_method'] ?? 'N/A');

            fputcsv($out, [
                $r['order_number'],
                date('M d, Y h:i A', strtotime($r['created_at'])),
                trim($r['first_name'] . ' ' . $r['last_name']),
                $r['category'],
                $r['sku'] ?? '',
                $r['product_name'],
                intval($r['quantity']),
                number_format(floatval($r['price']), 2),
                number_format(floatval($r['subtotal']), 2),
                number_format(floatval($r['total_amount']), 2),
                $pmLabel,
                ucfirst($r['payment_status'] ?? 'N/A'),
                ucfirst($r['order_status'])
            ]);
            $grandTotal += floatval($r['subtotal']);
        }

        fputcsv($out, []);
        fputcsv($out, ['', '', '', '', '', '', '', 'GRAND TOTAL:', number_format($grandTotal, 2)]);

        fclose($out);
        exit;
    }

    /* ==========================================================
     *  4. CURRENT INVENTORY – snapshot of all products
     * ========================================================== */
    private function exportCurrentInventory() {
        $sql = "SELECT c.name as category, p.sku, p.name as product_name,
                       p.price as selling_price, p.cost as unit_cost,
                       COALESCE(i.quantity, 0) as current_stock,
                       COALESCE(i.reorder_level, 10) as reorder_level,
                       CASE
                           WHEN COALESCE(i.quantity, 0) <= 0 THEN 'Out of Stock'
                           WHEN COALESCE(i.quantity, 0) <= COALESCE(i.reorder_level, 10) THEN 'Low Stock'
                           ELSE 'In Stock'
                       END as stock_status,
                       COALESCE(i.quantity, 0) * COALESCE(p.cost, p.price) as inventory_value
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1
                ORDER BY c.name, p.name";
        $rows = $this->pdo->query($sql)->fetchAll();

        $out = $this->startCsv('SouthDev_Current_Inventory_' . date('Y-m-d') . '.csv');

        fputcsv($out, ['SOUTHDEV HOME DEPOT']);
        fputcsv($out, ['Current Inventory Report']);
        fputcsv($out, ['Generated:', date('F d, Y h:i A')]);
        fputcsv($out, []);

        fputcsv($out, ['Category', 'SKU', 'Product Name', 'Selling Price (PHP)', 'Unit Cost (PHP)', 'Current Stock', 'Reorder Level', 'Stock Status', 'Inventory Value (PHP)']);

        $totalUnits = 0; $totalValue = 0; $outCount = 0; $lowCount = 0;
        foreach ($rows as $r) {
            $cost = $r['unit_cost'] !== null ? floatval($r['unit_cost']) : floatval($r['selling_price']);
            $val  = floatval($r['inventory_value']);
            $qty  = intval($r['current_stock']);
            fputcsv($out, [
                $r['category'],
                $r['sku'] ?? '',
                $r['product_name'],
                number_format(floatval($r['selling_price']), 2),
                number_format($cost, 2),
                $qty,
                intval($r['reorder_level']),
                $r['stock_status'],
                number_format($val, 2)
            ]);
            $totalUnits += $qty;
            $totalValue += $val;
            if ($qty <= 0) $outCount++;
            elseif ($r['stock_status'] === 'Low Stock') $lowCount++;
        }

        fputcsv($out, []);
        fputcsv($out, ['SUMMARY']);
        fputcsv($out, ['Total Products:', count($rows)]);
        fputcsv($out, ['Total Stock Units:', number_format($totalUnits)]);
        fputcsv($out, ['Total Inventory Value:', 'PHP ' . number_format($totalValue, 2)]);
        fputcsv($out, ['Low Stock Items:', $lowCount]);
        fputcsv($out, ['Out of Stock Items:', $outCount]);

        fclose($out);
        exit;
    }

    /* ==========================================================
     *  5. INVENTORY ADDED – all stock-in movements (purchases)
     * ========================================================== */
    private function exportInventoryAdded() {
        $sql = "SELECT sm.created_at, sm.type,
                       p.sku, p.name as product_name, c.name as category,
                       sm.quantity, sm.notes,
                       CONCAT(u.first_name, ' ', u.last_name) as performed_by
                FROM stock_movements sm
                JOIN products p ON sm.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON sm.performed_by = u.id
                WHERE sm.quantity > 0
                ORDER BY sm.created_at DESC";
        $rows = $this->pdo->query($sql)->fetchAll();

        $out = $this->startCsv('SouthDev_Inventory_Added_' . date('Y-m-d') . '.csv');

        fputcsv($out, ['SOUTHDEV HOME DEPOT']);
        fputcsv($out, ['Inventory Added Report - Stock-In Movements']);
        fputcsv($out, ['Generated:', date('F d, Y h:i A')]);
        fputcsv($out, []);

        fputcsv($out, ['Date', 'Category', 'SKU', 'Product Name', 'Type', 'Quantity Added', 'Notes', 'Added By']);

        $totalAdded = 0;
        foreach ($rows as $r) {
            fputcsv($out, [
                date('M d, Y h:i A', strtotime($r['created_at'])),
                $r['category'],
                $r['sku'] ?? '',
                $r['product_name'],
                ucfirst($r['type']),
                '+' . intval($r['quantity']),
                $r['notes'] ?? '',
                $r['performed_by'] ?? 'System'
            ]);
            $totalAdded += intval($r['quantity']);
        }

        fputcsv($out, []);
        fputcsv($out, ['SUMMARY']);
        fputcsv($out, ['Total Stock-In Entries:', count($rows)]);
        fputcsv($out, ['Total Units Added:', number_format($totalAdded)]);

        fclose($out);
        exit;
    }

    /* ==========================================================
     *  6. DAMAGED INVENTORY – all damaged product records
     * ========================================================== */
    private function exportDamagedInventory() {
        $sql = "SELECT dp.created_at, dp.updated_at,
                       p.sku, p.name as product_name, c.name as category,
                       p.price as unit_price,
                       dp.quantity,
                       dp.quantity * p.price as estimated_loss,
                       rr.reason as return_reason,
                       dp.reason as damage_description,
                       dp.status as damage_status,
                       dp.admin_notes,
                       o.order_number,
                       CONCAT(u.first_name, ' ', u.last_name) as reported_by
                FROM damaged_products dp
                JOIN products p ON dp.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                JOIN orders o ON dp.order_id = o.id
                JOIN return_requests rr ON dp.return_request_id = rr.id
                LEFT JOIN users u ON dp.reported_by = u.id
                ORDER BY dp.created_at DESC";
        $rows = $this->pdo->query($sql)->fetchAll();

        $out = $this->startCsv('SouthDev_Damaged_Inventory_' . date('Y-m-d') . '.csv');

        fputcsv($out, ['SOUTHDEV HOME DEPOT']);
        fputcsv($out, ['Damaged Inventory Report']);
        fputcsv($out, ['Generated:', date('F d, Y h:i A')]);
        fputcsv($out, []);

        fputcsv($out, ['Date Reported', 'Order #', 'Category', 'SKU', 'Product Name', 'Qty', 'Unit Price (PHP)', 'Estimated Loss (PHP)', 'Return Reason', 'Damage Description', 'Status', 'Admin Notes', 'Reported By', 'Last Updated']);

        $totalQty = 0; $totalLoss = 0;
        $statusCounts = ['received' => 0, 'inspected' => 0, 'written_off' => 0, 'repaired' => 0];
        foreach ($rows as $r) {
            $loss = floatval($r['estimated_loss']);
            $qty  = intval($r['quantity']);
            $st   = $r['damage_status'];
            fputcsv($out, [
                date('M d, Y h:i A', strtotime($r['created_at'])),
                $r['order_number'],
                $r['category'],
                $r['sku'] ?? '',
                $r['product_name'],
                $qty,
                number_format(floatval($r['unit_price']), 2),
                number_format($loss, 2),
                $r['return_reason'] ?? '',
                $r['damage_description'] ?? '',
                ucfirst($st),
                $r['admin_notes'] ?? '',
                $r['reported_by'] ?? 'System',
                date('M d, Y', strtotime($r['updated_at']))
            ]);
            $totalQty  += $qty;
            $totalLoss += $loss;
            if (isset($statusCounts[$st])) $statusCounts[$st]++;
        }

        fputcsv($out, []);
        fputcsv($out, ['SUMMARY']);
        fputcsv($out, ['Total Damaged Records:', count($rows)]);
        fputcsv($out, ['Total Damaged Units:', $totalQty]);
        fputcsv($out, ['Total Estimated Loss:', 'PHP ' . number_format($totalLoss, 2)]);
        fputcsv($out, []);
        fputcsv($out, ['STATUS BREAKDOWN']);
        fputcsv($out, ['Received:', $statusCounts['received']]);
        fputcsv($out, ['Inspected:', $statusCounts['inspected']]);
        fputcsv($out, ['Written Off:', $statusCounts['written_off']]);
        fputcsv($out, ['Repaired:', $statusCounts['repaired']]);

        fclose($out);
        exit;
    }
}
