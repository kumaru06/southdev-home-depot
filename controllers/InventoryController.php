<?php
/**
 * SouthDev Home Depot – Inventory Controller
 */

require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Log.php';

class InventoryController {
    private $inventoryModel;
    private $productModel;
    private $logModel;

    public function __construct($pdo) {
        $this->inventoryModel = new Inventory($pdo);
        $this->productModel   = new Product($pdo);
        $this->logModel       = new Log($pdo);
    }

    public function index() {
        AuthMiddleware::adminOrStaff();
        $inventory = $this->inventoryModel->getAll();
        $lowStock  = $this->inventoryModel->getLowStock();
        $pageTitle = 'Inventory Management';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/staff/inventory.php';
    }

    public function update() {
        AuthMiddleware::adminOrStaff();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $productId = intval($_POST['product_id'] ?? 0);
        $quantity  = intval($_POST['quantity'] ?? 0);

        $this->inventoryModel->updateQuantity($productId, $quantity);

        $product = $this->productModel->findById($productId);
        $this->logModel->create(LOG_STOCK_RESTORE, "Inventory updated for {$product['name']} (ID #{$productId}): set to {$quantity}");

        flash('success', 'Inventory updated successfully.');
        header('Location: ' . APP_URL . '/index.php?url=staff/inventory');
        exit;
    }
}
