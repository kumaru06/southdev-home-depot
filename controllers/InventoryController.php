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
require_once __DIR__ . '/../models/DamagedProduct.php';
require_once __DIR__ . '/../models/Category.php';

class InventoryController {
    private $inventoryModel;
    private $productModel;
    private $logModel;
    private $stockMovementModel;
    private $priceHistoryModel;
    private $damagedProductModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->inventoryModel       = new Inventory($pdo);
        $this->productModel         = new Product($pdo);
        $this->logModel             = new Log($pdo);
        $this->stockMovementModel   = new StockMovement($pdo);
        $this->priceHistoryModel    = new PriceHistory($pdo);
        $this->damagedProductModel  = new DamagedProduct($pdo);
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
        $inventory  = $this->inventoryModel->getAll();
        $lowStock   = $this->inventoryModel->getLowStock();
        $categoryModel = new Category($this->pdo);
        $categories = $categoryModel->getAll();
        $pageTitle  = 'Inventory Management';
        $isAdmin    = true;
        $extraCss   = ['admin.css'];
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
            $total = $this->priceHistoryModel->countByProduct($productId);
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

    /**
     * Damaged Products inventory page
     */
    public function damagedProducts() {
        AuthMiddleware::adminOrStaffOrInventory();
        $status   = $_GET['status'] ?? null;
        $damaged  = $this->damagedProductModel->getAll($status);
        $summary  = $this->damagedProductModel->getSummary();
        $pageTitle = 'Damaged Products';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/staff/damaged-products.php';
    }

    /**
     * Update damaged product status (inspected, written_off, repaired)
     */
    public function updateDamagedStatus($id) {
        AuthMiddleware::adminOrStaffOrInventory();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $status     = $_POST['status'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        $validStatuses = ['received', 'inspected', 'written_off'];
        if (!in_array($status, $validStatuses)) {
            flash('error', 'Invalid status.');
            header('Location: ' . $this->inventoryUrl() . '/damaged');
            exit;
        }

        $damaged = $this->damagedProductModel->findById($id);
        if (!$damaged) {
            flash('error', 'Damaged product record not found.');
            header('Location: ' . $this->inventoryUrl() . '/damaged');
            exit;
        }

        $this->damagedProductModel->updateStatus($id, $status, $adminNotes);

        $this->logModel->create(LOG_STOCK_MOVEMENT,
            "Damaged product #{$id} ({$damaged['product_name']}) status updated to: {$status}"
        );

        flash('success', 'Damaged product status updated to ' . str_replace('_', ' ', $status) . '.');
        header('Location: ' . $this->inventoryUrl() . '/damaged');
        exit;
    }
}
