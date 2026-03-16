<?php
/**
 * SouthDev Home Depot – Inventory Controller
 * Enhanced with stock movement tracking, price history, and supplier requests
 */

require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../models/PriceHistory.php';

class InventoryController {
    private $inventoryModel;
    private $productModel;
    private $logModel;
    private $stockMovementModel;
    private $priceHistoryModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->inventoryModel    = new Inventory($pdo);
        $this->productModel      = new Product($pdo);
        $this->logModel          = new Log($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
        $this->priceHistoryModel  = new PriceHistory($pdo);
    }

    /**
     * Get the correct inventory base URL for the current user's role
     */
    private function inventoryUrl() {
        $roleId = $_SESSION['role_id'] ?? 0;
        if ($roleId == ROLE_INVENTORY) return APP_URL . '/index.php?url=inventory/stock';
        if ($roleId == ROLE_SUPER_ADMIN) return APP_URL . '/index.php?url=admin/inventory';
        return APP_URL . '/index.php?url=staff/inventory';
    }

    public function index() {
        AuthMiddleware::adminOrStaffOrInventory();
        $inventory = $this->inventoryModel->getAll();
        $lowStock  = $this->inventoryModel->getLowStock();
        $pageTitle = 'Inventory Management';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/staff/inventory.php';
    }

    public function update() {
        AuthMiddleware::adminOrStaffOrInventory();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $productId = intval($_POST['product_id'] ?? 0);
        $quantity  = intval($_POST['quantity'] ?? 0);
        $reason    = trim($_POST['reason'] ?? 'Manual stock update');

        // Get current quantity for movement tracking
        $currentInventory = $this->inventoryModel->getByProductId($productId);
        $oldQty = intval($currentInventory['quantity'] ?? 0);

        $this->inventoryModel->updateQuantity($productId, $quantity);

        // Record stock movement
        $diff = $quantity - $oldQty;
        if ($diff != 0) {
            $this->stockMovementModel->record($productId, 'adjustment', $diff, null, $reason, $_SESSION['user_id']);
        }

        $product = $this->productModel->findById($productId);
        $this->logModel->create(LOG_STOCK_MOVEMENT, "Inventory updated for {$product['name']} (ID #{$productId}): {$oldQty} → {$quantity}. Reason: {$reason}");

        flash('success', 'Inventory updated successfully.');
        header('Location: ' . $this->inventoryUrl());
        exit;
    }

    /**
     * Add stock (purchase/restock)
     */
    public function addStock() {
        AuthMiddleware::adminOrStaffOrInventory();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $productId = intval($_POST['product_id'] ?? 0);
        $addQty    = intval($_POST['add_quantity'] ?? 0);
        $reason    = trim($_POST['reason'] ?? 'Stock purchase/restock');

        if ($addQty <= 0) {
            flash('error', 'Quantity must be greater than 0.');
            header('Location: ' . $this->inventoryUrl());
            exit;
        }

        $this->inventoryModel->adjustQuantity($productId, $addQty);
        $this->stockMovementModel->record($productId, 'purchase', $addQty, null, $reason, $_SESSION['user_id']);

        $product = $this->productModel->findById($productId);
        $this->logModel->create(LOG_STOCK_ADD, "Added {$addQty} units to {$product['name']} (ID #{$productId}). Reason: {$reason}");

        flash('success', "Added {$addQty} units to {$product['name']}.");
        header('Location: ' . $this->inventoryUrl());
        exit;
    }

    /**
     * Request supplier restock
     */
    public function requestSupplier() {
        AuthMiddleware::adminOrStaffOrInventory();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $productId = intval($_POST['product_id'] ?? 0);
        $requestQty = intval($_POST['request_quantity'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if ($requestQty <= 0) {
            flash('error', 'Request quantity must be greater than 0.');
            header('Location: ' . $this->inventoryUrl());
            exit;
        }

        // Insert supplier request
        $stmt = $this->pdo->prepare("
            INSERT INTO supplier_requests (product_id, requested_quantity, status, notes, requested_by, created_at)
            VALUES (?, ?, 'pending', ?, ?, NOW())
        ");
        $stmt->execute([$productId, $requestQty, $notes, $_SESSION['user_id']]);

        $product = $this->productModel->findById($productId);
        $this->logModel->create(LOG_SUPPLIER_REQUEST, "Supplier request for {$requestQty} units of {$product['name']} (ID #{$productId})");

        flash('success', "Supplier request submitted for {$product['name']} ({$requestQty} units).");
        header('Location: ' . $this->inventoryUrl());
        exit;
    }

    /**
     * Stock movements report
     */
    public function movements() {
        AuthMiddleware::adminOrStaffOrInventory();

        $filters = [
            'type' => $_GET['type'] ?? null,
            'product_id' => $_GET['product_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $movements = $this->stockMovementModel->getAll($filters, $limit, $offset);
        $totalMovements = $this->stockMovementModel->count($filters);
        $totalPages = ceil($totalMovements / $limit);
        $summary = $this->stockMovementModel->getSummary($filters['date_from'], $filters['date_to']);
        $products = $this->productModel->getAll();

        $pageTitle = 'Stock Movements';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/staff/stock-movements.php';
    }

    /**
     * Price history report
     */
    public function priceHistory() {
        AuthMiddleware::adminOrStaffOrInventory();

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $productId = $_GET['product_id'] ?? null;
        if ($productId) {
            $history = $this->priceHistoryModel->getByProduct($productId, $limit, $offset);
            $total = count($history); // simplified
        } else {
            $history = $this->priceHistoryModel->getAll($limit, $offset);
            $total = $this->priceHistoryModel->count();
        }

        $totalPages = ceil($total / $limit);
        $products = $this->productModel->getAll();

        $pageTitle = 'Price History';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/staff/price-history.php';
    }
}
