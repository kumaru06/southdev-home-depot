<?php
/**
 * SouthDev Home Depot – Product Controller
 */

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/PriceHistory.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../models/PriceHistory.php';
require_once __DIR__ . '/../models/StockMovement.php';

class ProductController {
    private $productModel;
    private $categoryModel;
    private $logModel;
    private $priceHistoryModel;
    private $stockMovementModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->productModel      = new Product($pdo);
        $this->categoryModel     = new Category($pdo);
        $this->logModel          = new Log($pdo);
        $this->priceHistoryModel = new PriceHistory($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
    }

    public function index() {
        $categoryId = $_GET['category'] ?? null;
        $page       = max(1, intval($_GET['page'] ?? 1));
        $offset     = ($page - 1) * ITEMS_PER_PAGE;

        $products      = $this->productModel->getAll($categoryId, ITEMS_PER_PAGE, $offset);
        $categories    = $this->categoryModel->getAll();
        $totalProducts = $this->productModel->count($categoryId);
        $totalPages    = ceil($totalProducts / ITEMS_PER_PAGE);

        $pageTitle = 'Products';
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/products.php';
    }

    /**
     * Alternate products layout used by the main Products link (simpler hero-less grid)
     */
    public function alt() {
        $categoryId = $_GET['category'] ?? null;
        $page       = max(1, intval($_GET['page'] ?? 1));
        $offset     = ($page - 1) * ITEMS_PER_PAGE;

        $products      = $this->productModel->getAll($categoryId, ITEMS_PER_PAGE, $offset);
        $categories    = $this->categoryModel->getAll();
        $totalProducts = $this->productModel->count($categoryId);
        $totalPages    = ceil($totalProducts / ITEMS_PER_PAGE);

        $pageTitle = 'Products';
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/products_alt.php';
    }

    public function show($id) {
        $product = $this->productModel->findById($id);
        if (!$product) {
            require_once VIEWS_PATH . '/errors/404.php';
            return;
        }
        $pageTitle = $product['name'];
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/product-details.php';
    }

    public function manage() {
        AuthMiddleware::adminOrStaff();
        $products   = $this->productModel->getAll();
        $categories = $this->categoryModel->getAll();
        $pageTitle  = 'Manage Products';
        $isAdmin    = true;
        $extraCss   = ['admin.css'];
        require_once VIEWS_PATH . '/superadmin/manage-products.php';
    }

    public function create() {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $sku = trim($_POST['sku'] ?? '');
        $data = [
            'category_id' => intval($_POST['category_id']),
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => floatval($_POST['price']),
            'sku'         => $sku !== '' ? $sku : null,
            'image'       => null
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['image/jpeg','image/png','image/webp'];
            if (in_array($_FILES['image']['type'], $allowed)) {
                $fileName   = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['image']['name']));
                $targetPath = UPLOADS_PATH . '/' . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $data['image'] = $fileName;
                }
            }
        }

        if ($data['sku'] && $this->productModel->skuExists($data['sku'])) {
            flash('error', 'SKU "' . htmlspecialchars($data['sku']) . '" already exists. Please use a unique SKU.');
            header('Location: ' . APP_URL . '/index.php?url=admin/products/create');
            exit;
        }

        $productId = $this->productModel->create($data);
        if ($productId) {
            $initialQty = intval($_POST['quantity'] ?? 0);
            $inv = new Inventory($this->pdo);
            $inv->updateQuantity($productId, $initialQty);

            // Record initial stock movement
            if ($initialQty > 0) {
                $this->stockMovementModel->record($productId, 'initial', $initialQty, null, 'Initial stock on product creation', $_SESSION['user_id']);
            }

            $this->logModel->create(LOG_PRODUCT_CREATE, "Product created: {$data['name']} (ID #{$productId})");
            flash('success', 'Product created successfully.');
        } else {
            flash('error', 'Failed to create product.');
        }

        header('Location: ' . APP_URL . '/index.php?url=admin/products');
        exit;
    }

    public function edit($id) {
        AuthMiddleware::superAdmin();
        $product = $this->productModel->findById($id);
        if (!$product) {
            require_once VIEWS_PATH . '/errors/404.php';
            return;
        }
        $categories = $this->categoryModel->getAll();
        $pageTitle  = 'Edit Product';
        $isAdmin    = true;
        $extraCss   = ['admin.css'];
        require_once VIEWS_PATH . '/superadmin/edit-product.php';
    }

    public function update($id) {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $sku = trim($_POST['sku'] ?? '');
        $data = [
            'category_id' => intval($_POST['category_id']),
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => floatval($_POST['price']),
            'sku'         => $sku !== '' ? $sku : null,
            'image'       => $_POST['existing_image'] ?? null
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['image/jpeg','image/png','image/webp'];
            if (in_array($_FILES['image']['type'], $allowed)) {
                $fileName   = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['image']['name']));
                $targetPath = UPLOADS_PATH . '/' . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $data['image'] = $fileName;
                }
            }
        }

        if ($data['sku'] && $this->productModel->skuExists($data['sku'], $id)) {
            flash('error', 'SKU "' . htmlspecialchars($data['sku']) . '" already exists. Please use a unique SKU.');
            header('Location: ' . APP_URL . '/index.php?url=admin/products/edit/' . $id);
            exit;
        }

        // Track price change before updating
        $existingProduct = $this->productModel->findById($id);
        $oldPrice = floatval($existingProduct['price'] ?? 0);
        $newPrice = $data['price'];

        $this->productModel->update($id, $data);

        // Record price history if price changed
        if (abs($oldPrice - $newPrice) > 0.001) {
            $reason = trim($_POST['price_change_reason'] ?? 'Price updated via product edit');
            $this->priceHistoryModel->record($id, $oldPrice, $newPrice, $_SESSION['user_id'], $reason);
            $this->logModel->create(LOG_PRICE_UPDATE, "Price updated for {$data['name']} (ID #{$id}): ₱" . number_format($oldPrice, 2) . " → ₱" . number_format($newPrice, 2));
        }

        if (isset($_POST['quantity'])) {
            $inv = new Inventory($this->pdo);
            $oldQty = intval($existingProduct['stock'] ?? 0);
            $newQty = intval($_POST['quantity']);
            $inv->updateQuantity($id, $newQty);

            // Record stock movement if quantity changed
            $diff = $newQty - $oldQty;
            if ($diff != 0) {
                $type = ($diff > 0) ? 'adjustment' : 'adjustment';
                $this->stockMovementModel->record($id, $type, $diff, null, 'Stock adjusted via product edit', $_SESSION['user_id']);
                $this->logModel->create(LOG_STOCK_MOVEMENT, "Stock adjusted for {$data['name']} (ID #{$id}): {$oldQty} → {$newQty}");
            }
        }

        $this->logModel->create(LOG_PRODUCT_UPDATE, "Product updated: {$data['name']} (ID #{$id})");
        if ($isAjax) {
            // Return JSON for AJAX callers (avoid consuming server-side flash so frontend can show notification)
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Product updated successfully.',
                'image'   => $data['image'] ?? null,
                'id'      => $id,
            ]);
            exit;
        }

        flash('success', 'Product updated successfully.');
        header('Location: ' . APP_URL . '/index.php?url=admin/products');
        exit;
    }

    public function delete($id) {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        $product = $this->productModel->findById($id);
        $this->productModel->delete($id);
        $this->logModel->create(LOG_PRODUCT_DELETE, "Product deleted: " . ($product['name'] ?? "ID #{$id}"));
        flash('success', 'Product deleted.');
        header('Location: ' . APP_URL . '/index.php?url=admin/products');
        exit;
    }

    public function search() {
        $keyword    = trim($_GET['q'] ?? '');
        $products   = $keyword ? $this->productModel->search($keyword) : [];
        $categories = $this->categoryModel->getAll();
        $pageTitle  = 'Search: ' . htmlspecialchars($keyword);
        $extraCss   = ['customer.css'];
        require_once VIEWS_PATH . '/customer/products.php';
    }
}
