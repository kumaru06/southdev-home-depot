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
require_once __DIR__ . '/../models/SupplierRequest.php';

class InventoryController {
    private $inventoryModel;
    private $productModel;
    private $logModel;
    private $stockMovementModel;
    private $priceHistoryModel;
    private $damagedProductModel;
    private $supplierRequestModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->inventoryModel       = new Inventory($pdo);
        $this->productModel         = new Product($pdo);
        $this->logModel             = new Log($pdo);
        $this->stockMovementModel   = new StockMovement($pdo);
        $this->priceHistoryModel    = new PriceHistory($pdo);
        $this->damagedProductModel  = new DamagedProduct($pdo);
        $this->supplierRequestModel = new SupplierRequest($pdo);
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

    private function supplierRequestsUrl() {
        return $this->inventoryUrl() . '/supplier-requests';
    }

    public function index() {
        AuthMiddleware::adminOrStaffOrInventory();
        $inventory  = $this->inventoryModel->getAll();
        $lowStock   = $this->inventoryModel->getLowStock();
        $categoryModel = new Category($this->pdo);
        $categories = $categoryModel->getAll();

        // Map product_id => open supplier request id (pending/ordered)
        $openSupplierByProduct = [];
        foreach ($this->supplierRequestModel->getAll() as $sr) {
            if (in_array($sr['status'], ['pending', 'ordered'], true)) {
                $pid = (int) $sr['product_id'];
                if (!isset($openSupplierByProduct[$pid])) {
                    $openSupplierByProduct[$pid] = (int) $sr['id'];
                }
            }
        }

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
        $supplierRequestId = intval($_POST['supplier_request_id'] ?? 0);

        if ($addQty <= 0) {
            flash('error', 'Quantity must be greater than 0.');
            header('Location: ' . ($supplierRequestId ? $this->supplierRequestsUrl() : $this->inventoryUrl()));
            exit;
        }

        $this->inventoryModel->adjustQuantity($productId, $addQty);
        $this->stockMovementModel->record($productId, 'purchase', $addQty, null, $reason, $_SESSION['user_id']);

        $product = $this->productModel->findById($productId);
        $this->logModel->create(LOG_STOCK_ADD, "Added {$addQty} units to {$product['name']} (ID #{$productId}). Reason: {$reason}");

        if ($supplierRequestId > 0) {
            $request = $this->supplierRequestModel->findById($supplierRequestId);
            if ($request && $request['status'] === SupplierRequest::STATUS_ORDERED
                && (int) $request['product_id'] === $productId) {
                $this->supplierRequestModel->updateStatus($supplierRequestId, SupplierRequest::STATUS_RECEIVED);
                $this->logModel->create(
                    LOG_SUPPLIER_REQUEST,
                    "Supplier request #{$supplierRequestId} marked received after adding {$addQty} units of {$product['name']}"
                );
                flash('success', "Received supplier request #{$supplierRequestId} and added {$addQty} units to {$product['name']}.");
                header('Location: ' . $this->supplierRequestsUrl());
                exit;
            }
        }

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

        if ($productId <= 0 || !$this->productModel->findById($productId)) {
            flash('error', 'Product not found.');
            header('Location: ' . $this->inventoryUrl());
            exit;
        }

        if ($requestQty <= 0) {
            flash('error', 'Request quantity must be greater than 0.');
            header('Location: ' . $this->inventoryUrl());
            exit;
        }

        if ($this->supplierRequestModel->hasOpenRequest($productId)) {
            $openId = $this->supplierRequestModel->getOpenRequestId($productId);
            flash('error', 'This product already has an open supplier request'
                . ($openId ? " (#{$openId})" : '')
                . '. Update that request instead of creating a duplicate.');
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        $requestId = $this->supplierRequestModel->create(
            $productId,
            $requestQty,
            $notes,
            $_SESSION['user_id'] ?? null
        );

        $product = $this->productModel->findById($productId);
        if ($requestId) {
            $this->logModel->create(
                LOG_SUPPLIER_REQUEST,
                "Supplier request #{$requestId} for {$requestQty} units of {$product['name']} (ID #{$productId})"
            );
            flash('success', "Supplier request submitted for {$product['name']} ({$requestQty} units). Waiting for Super Admin approval.");
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        flash('error', 'Failed to submit supplier request.');
        header('Location: ' . $this->inventoryUrl());
        exit;
    }

    /**
     * List supplier restock requests
     */
    public function supplierRequests() {
        AuthMiddleware::adminOrStaffOrInventory();
        $status   = $_GET['status'] ?? null;
        $valid    = ['pending', 'ordered', 'received', 'cancelled'];
        if ($status && !in_array($status, $valid, true)) {
            $status = null;
        }
        $requests = $this->supplierRequestModel->getAll($status);
        $summary  = $this->supplierRequestModel->getSummary();
        $pageTitle = 'Supplier Requests';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/staff/supplier-requests.php';
    }

    /**
     * Update supplier request status (ordered / cancelled)
     */
    public function updateSupplierRequestStatus($id) {
        AuthMiddleware::adminOrStaffOrInventory();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        $id = (int) $id;
        $status = trim($_POST['status'] ?? '');
        $request = $this->supplierRequestModel->findById($id);

        if (!$request) {
            flash('error', 'Supplier request not found.');
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        $current = $request['status'];
        $roleId  = (int) ($_SESSION['role_id'] ?? 0);
        $isSuperAdmin = $roleId === ROLE_SUPER_ADMIN;

        // Only Super Admin can approve (pending → ordered)
        if ($status === 'ordered') {
            if (!$isSuperAdmin) {
                flash('error', 'Only Super Admin can approve and mark a supplier request as ordered.');
                header('Location: ' . $this->supplierRequestsUrl());
                exit;
            }
            if ($current !== 'pending') {
                flash('error', 'Only pending requests can be approved.');
                header('Location: ' . $this->supplierRequestsUrl());
                exit;
            }
        } elseif ($status === 'cancelled') {
            $canCancel = ($current === 'pending')
                || ($current === 'ordered' && $isSuperAdmin);
            if (!$canCancel) {
                flash('error', 'You cannot cancel this request.');
                header('Location: ' . $this->supplierRequestsUrl());
                exit;
            }
        } else {
            flash('error', 'Invalid status transition for this request.');
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        $this->supplierRequestModel->updateStatus($id, $status);
        $this->logModel->create(
            LOG_SUPPLIER_REQUEST,
            "Supplier request #{$id} ({$request['product_name']}) status: {$current} → {$status}"
        );

        if ($status === 'ordered') {
            flash('success', "Supplier request #{$id} approved and marked as Ordered.");
        } else {
            flash('success', "Supplier request #{$id} marked as Cancelled.");
        }
        header('Location: ' . $this->supplierRequestsUrl());
        exit;
    }

    /**
     * Receive ordered goods: add stock + mark request received
     */
    public function receiveSupplierRequest($id) {
        AuthMiddleware::adminOrStaffOrInventory();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        $id = (int) $id;
        $request = $this->supplierRequestModel->findById($id);
        if (!$request) {
            flash('error', 'Supplier request not found.');
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        if ($request['status'] !== SupplierRequest::STATUS_ORDERED) {
            flash('error', 'Only ordered requests can be received.');
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        $addQty = intval($_POST['add_quantity'] ?? $request['requested_quantity']);
        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            $reason = "Supplier request #{$id} received";
        }

        if ($addQty <= 0) {
            flash('error', 'Received quantity must be greater than 0.');
            header('Location: ' . $this->supplierRequestsUrl());
            exit;
        }

        $productId = (int) $request['product_id'];
        $this->inventoryModel->adjustQuantity($productId, $addQty);
        $this->stockMovementModel->record($productId, 'purchase', $addQty, null, $reason, $_SESSION['user_id']);
        $this->supplierRequestModel->updateStatus($id, SupplierRequest::STATUS_RECEIVED);

        $productName = $request['product_name'] ?? ('Product #' . $productId);
        $this->logModel->create(
            LOG_SUPPLIER_REQUEST,
            "Supplier request #{$id} received: added {$addQty} units of {$productName}"
        );
        $this->logModel->create(LOG_STOCK_ADD, "Added {$addQty} units to {$productName} (ID #{$productId}). Reason: {$reason}");

        flash('success', "Request #{$id} received. Added {$addQty} units to {$productName}.");
        header('Location: ' . $this->supplierRequestsUrl());
        exit;
    }

    /**
     * AJAX: inventory sidebar badge counts (supplier pending + low stock)
     */
    public function apiPendingSupplierCount() {
        AuthMiddleware::adminOrStaffOrInventory();
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        $suppliers = $this->supplierRequestModel->countPending();
        $lowStock  = $this->inventoryModel->countLowStock();
        echo json_encode([
            'count'      => $suppliers, // backward-compatible
            'suppliers'  => $suppliers,
            'low_stock'  => $lowStock,
        ]);
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
     * Update damaged product status (received, inspected)
     */
    public function updateDamagedStatus($id) {
        AuthMiddleware::adminOrStaffOrInventory();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $status     = $_POST['status'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        $validStatuses = ['received', 'inspected'];
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
