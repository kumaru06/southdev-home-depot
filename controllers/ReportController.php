<?php
/**
 * SouthDev Home Depot – Report Controller
 * Enhanced: Sales + Inventory + Returns reporting with tabbed view
 */

use Dompdf\Dompdf;
use Dompdf\Options;

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

        // ===== DATE RANGE FILTER =====
        $preset   = $_GET['preset'] ?? 'all';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo   = $_GET['date_to']   ?? '';

        // Resolve preset to actual dates
        switch ($preset) {
            case 'today':
                $dateFrom = date('Y-m-d');
                $dateTo   = date('Y-m-d');
                break;
            case 'week':
                $dateFrom = date('Y-m-d', strtotime('monday this week'));
                $dateTo   = date('Y-m-d');
                break;
            case 'month':
                $dateFrom = date('Y-m-01');
                $dateTo   = date('Y-m-d');
                break;
            case 'last_month':
                $dateFrom = date('Y-m-01', strtotime('first day of last month'));
                $dateTo   = date('Y-m-t', strtotime('last day of last month'));
                break;
            case 'year':
                $dateFrom = date('Y-01-01');
                $dateTo   = date('Y-m-d');
                break;
            case 'custom':
                // use whatever was passed in date_from / date_to (validated below)
                break;
            default: // 'all'
                $dateFrom = '';
                $dateTo   = '';
        }

        // Sanitise custom dates
        if ($dateFrom && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = '';
        if ($dateTo   && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = '';
        // Swap if reversed
        if ($dateFrom && $dateTo && $dateFrom > $dateTo) [$dateFrom, $dateTo] = [$dateTo, $dateFrom];

        // WHERE clause for queries that JOIN orders as alias `o`
        $dateWhereOrders  = '';
        $dateBindOrders   = [];
        if ($dateFrom && $dateTo) {
            $dateWhereOrders = " AND DATE(o.created_at) BETWEEN ? AND ?";
            $dateBindOrders  = [$dateFrom, $dateTo];
        } elseif ($dateFrom) {
            $dateWhereOrders = " AND DATE(o.created_at) >= ?";
            $dateBindOrders  = [$dateFrom];
        } elseif ($dateTo) {
            $dateWhereOrders = " AND DATE(o.created_at) <= ?";
            $dateBindOrders  = [$dateTo];
        }

        // WHERE clause for direct queries on `orders` table (no alias)
        $dateWhereOrdersRaw  = '';
        $dateBindOrdersRaw   = [];
        if ($dateFrom && $dateTo) {
            $dateWhereOrdersRaw = " AND DATE(created_at) BETWEEN ? AND ?";
            $dateBindOrdersRaw  = [$dateFrom, $dateTo];
        } elseif ($dateFrom) {
            $dateWhereOrdersRaw = " AND DATE(created_at) >= ?";
            $dateBindOrdersRaw  = [$dateFrom];
        } elseif ($dateTo) {
            $dateWhereOrdersRaw = " AND DATE(created_at) <= ?";
            $dateBindOrdersRaw  = [$dateTo];
        }

        // WHERE clause for return_requests (no alias — single-table queries)
        $dateWhereReturns = '';
        $dateBindReturns  = [];
        if ($dateFrom && $dateTo) {
            $dateWhereReturns = " AND DATE(created_at) BETWEEN ? AND ?";
            $dateBindReturns  = [$dateFrom, $dateTo];
        } elseif ($dateFrom) {
            $dateWhereReturns = " AND DATE(created_at) >= ?";
            $dateBindReturns  = [$dateFrom];
        } elseif ($dateTo) {
            $dateWhereReturns = " AND DATE(created_at) <= ?";
            $dateBindReturns  = [$dateTo];
        }

        // WHERE clause for return_requests aliased as `rr` (JOIN queries)
        $dateWhereReturnsRr = '';
        if ($dateFrom && $dateTo) {
            $dateWhereReturnsRr = " AND DATE(rr.created_at) BETWEEN ? AND ?";
        } elseif ($dateFrom) {
            $dateWhereReturnsRr = " AND DATE(rr.created_at) >= ?";
        } elseif ($dateTo) {
            $dateWhereReturnsRr = " AND DATE(rr.created_at) <= ?";
        }

        // ===== SALES DATA =====
        // Total sales (filtered)
        $totalSalesSql = "SELECT COALESCE(SUM(o.total_amount), 0) as total
                          FROM orders o LEFT JOIN payments p ON p.order_id = o.id
                          WHERE o.status != 'cancelled'
                          AND (p.status IS NULL OR p.status != 'refunded')"
                          . $dateWhereOrders;
        $stmtTs = $this->pdo->prepare($totalSalesSql);
        $stmtTs->execute($dateBindOrders);
        $totalSales = floatval($stmtTs->fetchColumn());

        // Top products (filtered)
        $topProductsSql = "SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue
                           FROM order_items oi
                           JOIN orders o ON oi.order_id = o.id
                           JOIN products p ON oi.product_id = p.id
                           WHERE o.status != 'cancelled'"
                           . $dateWhereOrders .
                           " GROUP BY p.id ORDER BY total_sold DESC LIMIT 10";
        $stmtTp = $this->pdo->prepare($topProductsSql);
        $stmtTp->execute($dateBindOrders);
        $topProducts = $stmtTp->fetchAll();

        // Monthly sales chart data (always show last 12 months trend, ignore date filter for chart continuity)
        $stmt = $this->pdo->query("
            SELECT DATE_FORMAT(o.created_at, '%Y-%m') as month, SUM(o.total_amount) as total
            FROM orders o LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.status != 'cancelled'
            AND (p.status IS NULL OR p.status != 'refunded')
            GROUP BY month ORDER BY month DESC LIMIT 12
        ");
        $monthlySales = $stmt->fetchAll();

        // Order status counts (filtered)
        $statusSql = "SELECT status, COUNT(*) as count FROM orders WHERE 1=1" . $dateWhereOrdersRaw . " GROUP BY status";
        $stmtSt = $this->pdo->prepare($statusSql);
        $stmtSt->execute($dateBindOrdersRaw);
        $orderStatusCounts = $stmtSt->fetchAll();

        // Total customers (all-time – not date-filtered, customers don't belong to a period)
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users WHERE role_id = 1");
        $totalCustomers = $stmt->fetch()['count'] ?? 0;

        // Total orders (filtered)
        $stmtTo = $this->pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE 1=1" . $dateWhereOrdersRaw);
        $stmtTo->execute($dateBindOrdersRaw);
        $totalOrders = $stmtTo->fetch()['count'] ?? 0;

        // ===== INVENTORY DATA (snapshot – not date-filtered) =====
        $allInventory = $this->inventoryModel->getAll();
        $lowStockItems = $this->inventoryModel->getLowStock();
        $totalInventoryValue = 0;
        $totalStockUnits = 0;
        $outOfStockCount = 0;
        foreach ($allInventory as $inv) {
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

        // Stock movement summary (use date filter if set, else last 30 days)
        $smFrom = $dateFrom ?: date('Y-m-d', strtotime('-30 days'));
        $smTo   = $dateTo   ?: date('Y-m-d');
        $stockSummary = $this->stockMovementModel->getSummary($smFrom, $smTo);

        // ===== RETURNS DATA (filtered) =====
        $retStatusSql = "SELECT status, COUNT(*) as count FROM return_requests WHERE 1=1" . $dateWhereReturns . " GROUP BY status";
        $stmtRs = $this->pdo->prepare($retStatusSql);
        $stmtRs->execute($dateBindReturns);
        $returnStatusCounts = $stmtRs->fetchAll();
        $totalReturns = array_sum(array_column($returnStatusCounts, 'count'));

        // Recent returns (filtered)
        $retRecentSql = "SELECT rr.*, o.order_number, u.first_name, u.last_name
                         FROM return_requests rr
                         JOIN orders o ON rr.order_id = o.id
                         JOIN users u ON rr.user_id = u.id
                         WHERE 1=1" . $dateWhereReturnsRr . "
                         ORDER BY rr.created_at DESC LIMIT 10";
        $stmtRr = $this->pdo->prepare($retRecentSql);
        $stmtRr->execute($dateBindReturns);
        $recentReturns = $stmtRr->fetchAll();

        // Monthly returns trend (always last 12 months for chart)
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
            case 'inventory_added':      return $this->exportInventoryAdded();
            case 'inventory_combined':   return $this->exportInventoryCombined();
            case 'damaged_inventory':    return $this->exportDamagedInventory();
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
     *  HELPER: get export date range (defaults to today)
     *  Returns [$from, $to, $label, $whereOrders, $bindOrders,
     *           $whereRaw, $bindRaw, $whereRr, $whereSm, $bindSm]
     * ---------------------------------------------------------- */
    private function getExportDateRange(): array {
        $today = date('Y-m-d');
        $from  = $_GET['date_from'] ?? $today;
        $to    = $_GET['date_to']   ?? $today;
        // sanitise
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = $today;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = $today;
        if ($from > $to) [$from, $to] = [$to, $from];

        $label       = ($from === $to) ? date('M d, Y', strtotime($from))
                                       : date('M d, Y', strtotime($from)) . ' – ' . date('M d, Y', strtotime($to));
        // for queries with orders aliased as `o`
        $whereOrders = " AND DATE(o.created_at) BETWEEN ? AND ?";
        $bindOrders  = [$from, $to];
        // for direct orders table queries (no alias)
        $whereRaw    = " AND DATE(created_at) BETWEEN ? AND ?";
        $bindRaw     = [$from, $to];
        // for return_requests aliased as `rr`
        $whereRr     = " AND DATE(rr.created_at) BETWEEN ? AND ?";
        // for stock_movements aliased as `sm`
        $whereSm     = " AND DATE(sm.created_at) BETWEEN ? AND ?";
        $bindSm      = [$from, $to];

        return [$from, $to, $label, $whereOrders, $bindOrders, $whereRaw, $bindRaw, $whereRr, $whereSm, $bindSm];
    }

    /* ----------------------------------------------------------
     *  HELPER: render HTML as PDF and stream to browser
     * ---------------------------------------------------------- */
    private function renderPdf(string $filename, string $html): void {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /* ----------------------------------------------------------
     *  HELPER: shared PDF HTML wrapper
     * ---------------------------------------------------------- */
    private function pdfWrap(string $title, string $subtitle, string $body): string {
        $generated = date('D, d M Y, h:i A');
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #222; margin: 0; padding: 0; }
  .header { background: #1a3c5e; color: #fff; padding: 12px 16px; margin-bottom: 14px; }
  .header h1 { margin: 0; font-size: 16px; letter-spacing: 1px; }
  .header p  { margin: 2px 0 0; font-size: 9px; opacity: .85; }
  table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  thead tr { background: #1a3c5e; color: #fff; }
  thead th { padding: 6px 5px; text-align: left; font-size: 9px; }
  tbody tr:nth-child(even) { background: #f4f7fb; }
  tbody td { padding: 5px 6px; border-bottom: 1px solid #e0e0e0; font-size: 9px; }
  .total-row td { font-weight: bold; background: #e8f0fe; }
  .summary { margin-top: 14px; background: #f4f7fb; padding: 8px 12px; border-left: 4px solid #1a3c5e; }
  .summary h3 { margin: 0 0 6px; font-size: 11px; color: #1a3c5e; }
  .summary table { margin: 0; }
  .summary td { padding: 2px 8px 2px 0; font-size: 9px; border: none; background: none; }
  .summary td:first-child { font-weight: bold; width: 200px; }
  .right { text-align: right; padding-right: 8px; }
  thead th.right { text-align: right; padding-right: 8px; }
</style>
</head>
<body>
<div class="header">
  <h1>SOUTHDEV HOME DEPOT</h1>
  <p>{$subtitle} &nbsp;|&nbsp; Generated: {$generated}</p>
</div>
{$body}
</body>
</html>
HTML;
    }

    /* ==========================================================
     *  1. SALES – Daily Aggregated
     * ========================================================== */
    private function exportSalesDaily() {
        [, , $label, $whereOrders, $bindOrders] = $this->getExportDateRange();
        $sql = "SELECT DATE(o.created_at) as sale_date,
                       COUNT(DISTINCT o.id) as total_orders,
                       SUM(oi.quantity) as units_sold,
                       SUM(oi.subtotal) as gross_revenue,
                       SUM(CASE WHEN pay.status = 'refunded' THEN oi.subtotal ELSE 0 END) as refunded_amount,
                       SUM(CASE WHEN (pay.status IS NULL OR pay.status != 'refunded') THEN oi.subtotal ELSE 0 END) as net_revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                LEFT JOIN payments pay ON pay.order_id = o.id
                WHERE o.status != 'cancelled'
                {$whereOrders}
                GROUP BY sale_date
                ORDER BY sale_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindOrders);
        $rows = $stmt->fetchAll();

        $grandOrders = 0; $grandUnits = 0; $grandGross = 0.0; $grandRefunds = 0.0; $grandNet = 0.0;
        $tbody = '';
        foreach ($rows as $r) {
            $gross   = floatval($r['gross_revenue']);
            $refunds = floatval($r['refunded_amount']);
            $net     = floatval($r['net_revenue'] ?: ($gross - $refunds));
            $tbody .= '<tr>'
                . '<td>' . htmlspecialchars(date('M d, Y', strtotime($r['sale_date']))) . '</td>'
                . '<td class="right">' . intval($r['total_orders']) . '</td>'
                . '<td class="right">' . intval($r['units_sold']) . '</td>'
                . '<td class="right">PHP ' . number_format($gross, 2) . '</td>'
                . '<td class="right">PHP ' . number_format($refunds, 2) . '</td>'
                . '<td class="right">PHP ' . number_format($net, 2) . '</td>'
                . '</tr>';
            $grandOrders  += intval($r['total_orders']);
            $grandUnits   += intval($r['units_sold']);
            $grandGross   += $gross;
            $grandRefunds += $refunds;
            $grandNet     += $net;
        }
        $tbody .= '<tr class="total-row">'
            . '<td>TOTAL</td>'
            . '<td class="right">' . $grandOrders . '</td>'
            . '<td class="right">' . $grandUnits . '</td>'
            . '<td class="right">PHP ' . number_format($grandGross, 2) . '</td>'
            . '<td class="right">PHP ' . number_format($grandRefunds, 2) . '</td>'
            . '<td class="right">PHP ' . number_format($grandNet, 2) . '</td>'
            . '</tr>';

        $body = '<table><thead><tr>'
            . '<th>Date</th><th>Total Orders</th><th>Units Sold</th>'
            . '<th>Gross Revenue (PHP)</th><th>Refunds (PHP)</th><th>Net Revenue (PHP)</th>'
            . '</tr></thead><tbody>' . $tbody . '</tbody></table>';

        $html = $this->pdfWrap('Sales Report - Daily Summary', 'Sales Report — Daily Summary &nbsp;|&nbsp; ' . $label, $body);
        $this->renderPdf('SouthDev_Sales_Daily_' . date('Y-m-d') . '.pdf', $html);
    }

    /* ==========================================================
     *  2. SALES – Monthly Aggregated
     * ========================================================== */
    private function exportSalesMonthly() {
        [, , $label, $whereOrders, $bindOrders] = $this->getExportDateRange();
        $sql = "SELECT DATE_FORMAT(o.created_at, '%Y-%m') as sale_month,
                       COUNT(DISTINCT o.id) as total_orders,
                       SUM(oi.quantity) as units_sold,
                       SUM(oi.subtotal) as gross_revenue,
                       SUM(CASE WHEN pay.status = 'refunded' THEN oi.subtotal ELSE 0 END) as refunded_amount,
                       SUM(CASE WHEN (pay.status IS NULL OR pay.status != 'refunded') THEN oi.subtotal ELSE 0 END) as net_revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                LEFT JOIN payments pay ON pay.order_id = o.id
                WHERE o.status != 'cancelled'
                {$whereOrders}
                GROUP BY sale_month
                ORDER BY sale_month DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindOrders);
        $rows = $stmt->fetchAll();

        $grandOrders = 0; $grandUnits = 0; $grandGross = 0.0; $grandRefunds = 0.0; $grandNet = 0.0;
        $tbody = '';
        foreach ($rows as $r) {
            $gross   = floatval($r['gross_revenue']);
            $refunds = floatval($r['refunded_amount']);
            $net     = floatval($r['net_revenue'] ?: ($gross - $refunds));
            $tbody .= '<tr>'
                . '<td>' . htmlspecialchars(date('F Y', strtotime($r['sale_month'] . '-01'))) . '</td>'
                . '<td class="right">' . intval($r['total_orders']) . '</td>'
                . '<td class="right">' . intval($r['units_sold']) . '</td>'
                . '<td class="right">PHP ' . number_format($gross, 2) . '</td>'
                . '<td class="right">PHP ' . number_format($refunds, 2) . '</td>'
                . '<td class="right">PHP ' . number_format($net, 2) . '</td>'
                . '</tr>';
            $grandOrders  += intval($r['total_orders']);
            $grandUnits   += intval($r['units_sold']);
            $grandGross   += $gross;
            $grandRefunds += $refunds;
            $grandNet     += $net;
        }
        $tbody .= '<tr class="total-row">'
            . '<td>TOTAL</td>'
            . '<td class="right">' . $grandOrders . '</td>'
            . '<td class="right">' . $grandUnits . '</td>'
            . '<td class="right">PHP ' . number_format($grandGross, 2) . '</td>'
            . '<td class="right">PHP ' . number_format($grandRefunds, 2) . '</td>'
            . '<td class="right">PHP ' . number_format($grandNet, 2) . '</td>'
            . '</tr>';

        $body = '<table><thead><tr>'
            . '<th>Month</th><th>Total Orders</th><th>Units Sold</th>'
            . '<th>Gross Revenue (PHP)</th><th>Refunds (PHP)</th><th>Net Revenue (PHP)</th>'
            . '</tr></thead><tbody>' . $tbody . '</tbody></table>';

        $html = $this->pdfWrap('Sales Report - Monthly Summary', 'Sales Report — Monthly Summary &nbsp;|&nbsp; ' . $label, $body);
        $this->renderPdf('SouthDev_Sales_Monthly_' . date('Y-m-d') . '.pdf', $html);
    }

    /* ==========================================================
     *  3. SALES – Detailed Rows (every order item)
     * ========================================================== */
    private function exportSalesRows() {
        [, , $label, $whereOrders, $bindOrders] = $this->getExportDateRange();
        $sql = "SELECT o.order_number, o.created_at, o.status as order_status,
                       u.first_name, u.last_name,
                       p.sku, p.name as product_name, c.name as category,
                       oi.quantity, oi.price, oi.subtotal,
                       COALESCE(p.cost, 0) as unit_cost,
                       o.total_amount,
                       pay.payment_method, pay.status as payment_status
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON o.user_id = u.id
                LEFT JOIN payments pay ON pay.order_id = o.id
                WHERE o.status != 'cancelled'
                {$whereOrders}
                ORDER BY o.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindOrders);
        $rows = $stmt->fetchAll();

        $grandSubtotal = 0.0; $grandProfit = 0.0; $grandCost = 0.0;
        $tbody = '';
        foreach ($rows as $r) {
            $pm = strtolower($r['payment_method'] ?? '');
            if (str_contains($pm, 'gcash')) $pmLabel = 'GCash';
            elseif (str_contains($pm, 'card')) $pmLabel = 'Card';
            elseif (str_contains($pm, 'cod') || str_contains($pm, 'cash')) $pmLabel = 'COD';
            else $pmLabel = ucfirst($r['payment_method'] ?? 'N/A');

            $rawPayStatus = $r['payment_status'] ?? 'N/A';
            if ($rawPayStatus === 'pending' && str_contains($pm, 'cod') && $r['order_status'] === 'delivered') {
                $payStatusLabel = 'Completed (COD)';
            } else {
                $payStatusLabel = ucfirst($rawPayStatus);
            }

            $qty      = intval($r['quantity']);
            $price    = floatval($r['price']);
            $cost     = floatval($r['unit_cost']);
            $subtotal = floatval($r['subtotal']);
            $profit   = ($price - $cost) * $qty;

            $tbody .= '<tr>'
                . '<td>' . htmlspecialchars($r['order_number']) . '</td>'
                . '<td>' . date('M d, Y', strtotime($r['created_at'])) . '</td>'
                . '<td>' . htmlspecialchars(trim($r['first_name'] . ' ' . $r['last_name'])) . '</td>'
                . '<td>' . htmlspecialchars($r['category']) . '</td>'
                . '<td>' . htmlspecialchars($r['sku'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['product_name']) . '</td>'
                . '<td class="right">' . $qty . '</td>'
                . '<td class="right">PHP ' . number_format($price, 2) . '</td>'
                . '<td class="right">PHP ' . number_format($cost, 2) . '</td>'
                . '<td class="right">PHP ' . number_format($subtotal, 2) . '</td>'
                . '<td class="right">PHP ' . number_format($profit, 2) . '</td>'
                . '<td>' . htmlspecialchars($pmLabel) . '</td>'
                . '<td>' . htmlspecialchars($payStatusLabel) . '</td>'
                . '<td>' . htmlspecialchars(ucfirst($r['order_status'])) . '</td>'
                . '</tr>';
            $grandSubtotal += $subtotal;
            $grandCost     += $cost * $qty;
            $grandProfit   += $profit;
        }
        $tbody .= '<tr class="total-row">'
            . '<td colspan="9" class="right">GRAND TOTAL</td>'
            . '<td class="right">PHP ' . number_format($grandSubtotal, 2) . '</td>'
            . '<td class="right">PHP ' . number_format($grandProfit, 2) . '</td>'
            . '<td colspan="3"></td>'
            . '</tr>';

        $body = '<table><thead><tr>'
            . '<th>Order #</th><th>Date</th><th>Customer</th><th>Category</th><th>SKU</th>'
            . '<th>Product</th><th>Qty</th><th>Unit Price</th><th>Unit Cost</th>'
            . '<th>Subtotal</th><th>Profit</th><th>Payment</th><th>Pay Status</th><th>Order Status</th>'
            . '</tr></thead><tbody>' . $tbody . '</tbody></table>';

        $html = $this->pdfWrap('Sales Report - Detailed', 'Sales Report — Detailed Transactions &nbsp;|&nbsp; ' . $label, $body);
        $this->renderPdf('SouthDev_Sales_Detailed_' . date('Y-m-d') . '.pdf', $html);
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

        $totalUnits = 0; $totalValue = 0.0; $outCount = 0; $lowCount = 0;
        $tbody = '';
        foreach ($rows as $r) {
            $cost = $r['unit_cost'] !== null ? floatval($r['unit_cost']) : floatval($r['selling_price']);
            $val  = floatval($r['inventory_value']);
            $qty  = intval($r['current_stock']);
            $statusColor = $r['stock_status'] === 'Out of Stock' ? 'color:#c0392b;font-weight:bold'
                         : ($r['stock_status'] === 'Low Stock' ? 'color:#e67e22;font-weight:bold' : 'color:#27ae60');
            $tbody .= '<tr>'
                . '<td>' . htmlspecialchars($r['category']) . '</td>'
                . '<td>' . htmlspecialchars($r['sku'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['product_name']) . '</td>'
                . '<td class="right">PHP ' . number_format(floatval($r['selling_price']), 2) . '</td>'
                . '<td class="right">PHP ' . number_format($cost, 2) . '</td>'
                . '<td class="right">' . $qty . '</td>'
                . '<td class="right">' . intval($r['reorder_level']) . '</td>'
                . '<td style="' . $statusColor . '">' . $r['stock_status'] . '</td>'
                . '<td class="right">PHP ' . number_format($val, 2) . '</td>'
                . '</tr>';
            $totalUnits += $qty;
            $totalValue += $val;
            if ($qty <= 0) $outCount++;
            elseif ($r['stock_status'] === 'Low Stock') $lowCount++;
        }

        $summary = '<div class="summary"><h3>Summary</h3><table>'
            . '<tr><td>Total Products:</td><td>' . count($rows) . '</td></tr>'
            . '<tr><td>Total Stock Units:</td><td>' . number_format($totalUnits) . '</td></tr>'
            . '<tr><td>Total Inventory Value:</td><td>PHP ' . number_format($totalValue, 2) . '</td></tr>'
            . '<tr><td>Low Stock Items:</td><td>' . $lowCount . '</td></tr>'
            . '<tr><td>Out of Stock Items:</td><td>' . $outCount . '</td></tr>'
            . '</table></div>';

        $body = '<table><thead><tr>'
            . '<th>Category</th><th>SKU</th><th>Product Name</th><th>Selling Price</th>'
            . '<th>Unit Cost</th><th>Current Stock</th><th>Reorder Level</th><th>Status</th><th>Inventory Value</th>'
            . '</tr></thead><tbody>' . $tbody . '</tbody></table>' . $summary;

        $html = $this->pdfWrap('Current Inventory Report', 'Current Inventory Report', $body);
        $this->renderPdf('SouthDev_Current_Inventory_' . date('Y-m-d') . '.pdf', $html);
    }

    /* ==========================================================
     *  5. INVENTORY ADDED – all stock-in movements (purchases)
     * ========================================================== */
    private function exportInventoryAdded() {
        [, , $label, , , , , , $whereSm, $bindSm] = $this->getExportDateRange();
        $sql = "SELECT sm.created_at, sm.type,
                       p.sku, p.name as product_name, c.name as category,
                       sm.quantity, sm.notes,
                       CONCAT(u.first_name, ' ', u.last_name) as performed_by
                FROM stock_movements sm
                JOIN products p ON sm.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON sm.performed_by = u.id
                WHERE sm.quantity > 0
                {$whereSm}
                ORDER BY sm.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindSm);
        $rows = $stmt->fetchAll();

        $totalAdded = 0;
        $tbody = '';
        foreach ($rows as $r) {
            $qty = intval($r['quantity']);
            $tbody .= '<tr>'
                . '<td>' . date('M d, Y h:i A', strtotime($r['created_at'])) . '</td>'
                . '<td>' . htmlspecialchars($r['category']) . '</td>'
                . '<td>' . htmlspecialchars($r['sku'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['product_name']) . '</td>'
                . '<td>' . ucfirst($r['type']) . '</td>'
                . '<td class="right" style="color:#27ae60;font-weight:bold">+' . $qty . '</td>'
                . '<td>' . htmlspecialchars($r['notes'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['performed_by'] ?? 'System') . '</td>'
                . '</tr>';
            $totalAdded += $qty;
        }

        $summary = '<div class="summary"><h3>Summary</h3><table>'
            . '<tr><td>Total Stock-In Entries:</td><td>' . count($rows) . '</td></tr>'
            . '<tr><td>Total Units Added:</td><td>' . number_format($totalAdded) . '</td></tr>'
            . '</table></div>';

        $body = '<table><thead><tr>'
            . '<th>Date</th><th>Category</th><th>SKU</th><th>Product Name</th>'
            . '<th>Type</th><th>Qty Added</th><th>Notes</th><th>Added By</th>'
            . '</tr></thead><tbody>' . $tbody . '</tbody></table>' . $summary;

        $html = $this->pdfWrap('Inventory Added Report', 'Inventory Added Report — Stock-In Movements &nbsp;|&nbsp; ' . $label, $body);
        $this->renderPdf('SouthDev_Inventory_Added_' . date('Y-m-d') . '.pdf', $html);
    }

    /* ==========================================================
     *  5b. INVENTORY COMBINED – current + added + removed per product
     * ========================================================== */
    private function exportInventoryCombined() {
        $sql = "
            SELECT
                c.name  AS category,
                p.sku,
                p.name  AS product_name,
                p.price AS selling_price,
                COALESCE(i.quantity, 0) AS current_stock,
                COALESCE(added.total_added, 0)   AS total_added,
                COALESCE(removed.total_removed, 0) AS total_removed
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN inventory i ON p.id = i.product_id
            LEFT JOIN (
                SELECT product_id, SUM(quantity) AS total_added
                FROM stock_movements
                WHERE quantity > 0
                GROUP BY product_id
            ) added ON p.id = added.product_id
            LEFT JOIN (
                SELECT product_id, SUM(ABS(quantity)) AS total_removed
                FROM stock_movements
                WHERE quantity < 0
                GROUP BY product_id
            ) removed ON p.id = removed.product_id
            WHERE p.is_active = 1
            ORDER BY c.name, p.name
        ";
        $rows = $this->pdo->query($sql)->fetchAll();

        $grandOpening = 0; $grandAdded = 0; $grandRemoved = 0; $grandCurrent = 0;
        $tbody = '';
        foreach ($rows as $r) {
            $added   = intval($r['total_added']);
            $removed = intval($r['total_removed']);
            $current = intval($r['current_stock']);
            $opening = $current - $added + $removed;
            $tbody .= '<tr>'
                . '<td>' . htmlspecialchars($r['category']) . '</td>'
                . '<td>' . htmlspecialchars($r['sku'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['product_name']) . '</td>'
                . '<td class="right">PHP ' . number_format(floatval($r['selling_price']), 2) . '</td>'
                . '<td class="right">' . $opening . '</td>'
                . '<td class="right" style="color:#27ae60">+' . $added . '</td>'
                . '<td class="right" style="color:#c0392b">-' . $removed . '</td>'
                . '<td class="right"><strong>' . $current . '</strong></td>'
                . '<td style="font-size:8px;color:#666">' . $opening . '+' . $added . '-' . $removed . '=' . $current . '</td>'
                . '</tr>';
            $grandOpening += $opening;
            $grandAdded   += $added;
            $grandRemoved += $removed;
            $grandCurrent += $current;
        }
        $tbody .= '<tr class="total-row">'
            . '<td colspan="4">TOTAL</td>'
            . '<td class="right">' . $grandOpening . '</td>'
            . '<td class="right">+' . $grandAdded . '</td>'
            . '<td class="right">-' . $grandRemoved . '</td>'
            . '<td class="right">' . $grandCurrent . '</td>'
            . '<td></td></tr>';

        $body = '<table><thead><tr>'
            . '<th>Category</th><th>SKU</th><th>Product Name</th><th>Selling Price</th>'
            . '<th>Opening</th><th>Added</th><th>Removed</th><th>Current</th><th>Formula</th>'
            . '</tr></thead><tbody>' . $tbody . '</tbody></table>';

        $html = $this->pdfWrap('Inventory Combined Report', 'Inventory Combined — Added vs Removed vs Current', $body);
        $this->renderPdf('SouthDev_Inventory_Combined_' . date('Y-m-d') . '.pdf', $html);
    }

    /* ==========================================================
     *  6. DAMAGED INVENTORY – all damaged product records
     * ========================================================== */
    private function exportDamagedInventory() {
        [, , $label, , , , , , , $bindSm] = $this->getExportDateRange();
        $from = $bindSm[0]; $to = $bindSm[1];
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
                WHERE DATE(dp.created_at) BETWEEN ? AND ?
                ORDER BY dp.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$from, $to]);
        $rows = $stmt->fetchAll();

        $totalQty = 0; $totalLoss = 0.0;
        $statusCounts = ['received' => 0, 'inspected' => 0];
        $tbody = '';
        foreach ($rows as $r) {
            $loss = floatval($r['estimated_loss']);
            $qty  = intval($r['quantity']);
            $st   = $r['damage_status'];
            $tbody .= '<tr>'
                . '<td>' . date('M d, Y', strtotime($r['created_at'])) . '</td>'
                . '<td>' . htmlspecialchars($r['order_number']) . '</td>'
                . '<td>' . htmlspecialchars($r['category']) . '</td>'
                . '<td>' . htmlspecialchars($r['sku'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['product_name']) . '</td>'
                . '<td class="right">' . $qty . '</td>'
                . '<td class="right">PHP ' . number_format(floatval($r['unit_price']), 2) . '</td>'
                . '<td class="right" style="color:#c0392b">PHP ' . number_format($loss, 2) . '</td>'
                . '<td>' . htmlspecialchars($r['return_reason'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['damage_description'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars(ucfirst($st)) . '</td>'
                . '<td>' . htmlspecialchars($r['admin_notes'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['reported_by'] ?? 'System') . '</td>'
                . '</tr>';
            $totalQty  += $qty;
            $totalLoss += $loss;
            if (isset($statusCounts[$st])) $statusCounts[$st]++;
        }

        $summary = '<div class="summary"><h3>Summary</h3><table>'
            . '<tr><td>Total Damaged Records:</td><td>' . count($rows) . '</td></tr>'
            . '<tr><td>Total Damaged Units:</td><td>' . $totalQty . '</td></tr>'
            . '<tr><td>Total Estimated Loss:</td><td>PHP ' . number_format($totalLoss, 2) . '</td></tr>'
            . '<tr><td>Received:</td><td>' . $statusCounts['received'] . '</td></tr>'
            . '<tr><td>Inspected:</td><td>' . $statusCounts['inspected'] . '</td></tr>'
            . '</table></div>';

        $body = '<table><thead><tr>'
            . '<th>Date</th><th>Order #</th><th>Category</th><th>SKU</th><th>Product</th>'
            . '<th>Qty</th><th>Unit Price</th><th>Est. Loss</th>'
            . '<th>Return Reason</th><th>Damage Desc.</th><th>Status</th><th>Admin Notes</th><th>Reported By</th>'
            . '</tr></thead><tbody>' . $tbody . '</tbody></table>' . $summary;

        $html = $this->pdfWrap('Damaged Inventory Report', 'Damaged Inventory Report &nbsp;|&nbsp; ' . $label, $body);
        $this->renderPdf('SouthDev_Damaged_Inventory_' . date('Y-m-d') . '.pdf', $html);
    }
}
