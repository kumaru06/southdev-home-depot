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
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/OrderItem.php';

class ProductController {
    private const MAX_IMAGE_BYTES = 5 * 1024 * 1024;

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
        $perPage    = $this->itemsPerPage();
        $offset     = ($page - 1) * $perPage;

        $products      = $this->attachDisplayStock($this->productModel->getAll($categoryId, $perPage, $offset));
        $categories    = $this->categoryModel->getAll();
        $totalProducts = $this->productModel->count($categoryId);
        $totalPages    = ceil($totalProducts / $perPage);

        // Load average ratings and sold counts for the listed products
        $productRatings = [];
        $productSold = [];
        if (!empty($products)) {
            $reviewModel = new Review($this->pdo);
            $orderItemModel = new OrderItem($this->pdo);
            $productIds = array_column($products, 'id');
            $productRatings = $reviewModel->getAvgRatingsByProductIds($productIds);
            $productSold = $orderItemModel->getSoldCountsByProductIds($productIds);
        }

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
        $perPage    = $this->itemsPerPage();
        $offset     = ($page - 1) * $perPage;

        $products      = $this->attachDisplayStock($this->productModel->getAll($categoryId, $perPage, $offset));
        $categories    = $this->categoryModel->getAll();
        $totalProducts = $this->productModel->count($categoryId);
        $totalPages    = ceil($totalProducts / $perPage);

        // Load average ratings and sold counts for the listed products
        $productRatings = [];
        $productSold = [];
        if (!empty($products)) {
            $reviewModel = new Review($this->pdo);
            $orderItemModel = new OrderItem($this->pdo);
            $productIds = array_column($products, 'id');
            $productRatings = $reviewModel->getAvgRatingsByProductIds($productIds);
            $productSold = $orderItemModel->getSoldCountsByProductIds($productIds);
        }

        $pageTitle = 'Products';
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/products_alt.php';
    }

    public function show($id) {
        $product = $this->productModel->findActiveById($id);
        if (!$product) {
            require_once VIEWS_PATH . '/errors/404.php';
            return;
        }

        $images = $this->productModel->getImages($id);
        if (empty($images) && !empty($product['image'])) {
            $images = [['id' => 0, 'filename' => $product['image'], 'sort_order' => 0]];
        }

        $specs = Product::decodeSpecifications($product['specifications'] ?? null);
        $sizeOptions = $this->productModel->getSizeOptions($id);

        $relatedProducts = $this->productModel->getRelated($product['category_id'], $id, 8);
        $relatedRatings = [];
        if (!empty($relatedProducts)) {
            $reviewModel = new Review($this->pdo);
            $relatedRatings = $reviewModel->getAvgRatingsByProductIds(array_column($relatedProducts, 'id'));
        }
        $productSoldCount = (int) ((new OrderItem($this->pdo))->getSoldCountsByProductIds([(int) $id])[(int) $id] ?? 0);

        $pageTitle = $product['name'];
        $extraCss  = ['customer.css'];
        $extraJs   = ['cart.js', 'product-detail.js'];
        require_once VIEWS_PATH . '/customer/product-details.php';
    }

    /**
     * Dedicated product reviews page: /index.php?url=products/{id}/reviews
     * Scrollable locked list so the box does not stretch with many reviews.
     */
    public function reviews($id) {
        $product = $this->productModel->findActiveById($id);
        if (!$product) {
            require_once VIEWS_PATH . '/errors/404.php';
            return;
        }

        $pageTitle = 'Reviews — ' . $product['name'];
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/product-reviews.php';
    }

    public function manage() {
        AuthMiddleware::adminOrStaff();
        $products   = $this->productModel->getAll();
        $categories = $this->categoryModel->getAll();
        $galleryByProduct = [];
        $sizesByProduct = [];
        $sizeSummaryByProduct = [];
        if (!empty($products)) {
            $ids = array_column($products, 'id');
            $galleryByProduct = $this->productModel->getImagesByProductIds($ids);
            $sizesByProduct = $this->productModel->getSizeOptionsByProductIds($ids);
            $sizeSummaryByProduct = $this->productModel->getSizeStockSummaryByProductIds($ids);
        }
        $pageTitle  = 'Manage Products';
        $isAdmin    = true;
        $extraCss   = ['admin.css'];
        require_once VIEWS_PATH . '/superadmin/manage-products.php';
    }

    public function create() {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $sellingType = $this->sellingTypeFromPost();
        $sizeOptions = $this->sizeOptionsForSellingType($sellingType);
        $sku = trim($_POST['sku'] ?? '');
        $data = [
            'category_id'     => intval($_POST['category_id']),
            'name'            => trim($_POST['name']),
            'description'     => trim($_POST['description'] ?? ''),
            'specifications'  => $this->parseSpecificationsFromPost(),
            'price'           => $this->listingPriceForSellingType($sellingType, $sizeOptions, $_POST['price'] ?? 0),
            'sku'             => $sku !== '' ? $sku : null,
            'size_label'      => $this->nullableTrim($_POST['size_label'] ?? ''),
            'size_group'      => null,
            'image'           => null
        ];
        $initialQty = $sellingType === 'sizes' ? 0 : intval($_POST['quantity'] ?? 0);
        $validationError = $this->validateProductPayload($data, $sizeOptions, $initialQty, $sellingType);
        if ($validationError !== null) {
            flash('error', $validationError);
            header('Location: ' . APP_URL . '/index.php?url=admin/products&add=1');
            exit;
        }

        $cover = $this->storeUploadedImage($_FILES['image'] ?? null);
        if (!empty($cover['error'])) {
            flash('error', $cover['error']);
            header('Location: ' . APP_URL . '/index.php?url=admin/products&add=1');
            exit;
        }
        if (!empty($cover['filename'])) {
            $data['image'] = $cover['filename'];
        }

        if ($data['sku'] && $this->productModel->skuExists($data['sku'])) {
            flash('error', 'SKU "' . htmlspecialchars($data['sku']) . '" already exists. Please use a unique SKU.');
            header('Location: ' . APP_URL . '/index.php?url=admin/products&add=1');
            exit;
        }

        $productId = $this->productModel->create($data);
        if ($productId) {
            if (!empty($data['image'])) {
                $this->productModel->addImage($productId, $data['image'], 0);
            }
            $this->storeGalleryUploads($productId, $_FILES['gallery_images'] ?? null);
            $this->productModel->replaceSizeOptions($productId, $sizeOptions);

            $inv = new Inventory($this->pdo);
            if (!empty($sizeOptions)) {
                $initialQty = 0;
            }
            $inv->updateQuantity($productId, $initialQty);

            // Record initial stock movement
            if ($initialQty > 0) {
                $this->stockMovementModel->record($productId, 'initial', $initialQty, null, 'Initial stock on product creation', $_SESSION['user_id']);
            }

            $this->logModel->create(LOG_PRODUCT_CREATE, "Product created: {$data['name']} (ID #{$productId})");
            flash('success', 'Product created successfully.');
            header('Location: ' . APP_URL . '/index.php?url=admin/products');
            exit;
        }

        flash('error', 'Failed to create product.');
        header('Location: ' . APP_URL . '/index.php?url=admin/products&add=1');
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
        $galleryImages = $this->productModel->getImages($id);
        $specPairs = Product::decodeSpecifications($product['specifications'] ?? null);
        $sizeOptionRows = $this->productModel->getSizeOptions($id);
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

        list($sellingType, $sizeOptions) = $this->resolveUpdateSellingPayload($id);
        $sku = trim($_POST['sku'] ?? '');
        $data = [
            'category_id'    => intval($_POST['category_id']),
            'name'           => trim($_POST['name']),
            'description'    => trim($_POST['description'] ?? ''),
            'specifications' => $this->parseSpecificationsFromPost(),
            'price'          => $this->listingPriceForSellingType($sellingType, $sizeOptions, $_POST['price'] ?? 0),
            'sku'            => $sku !== '' ? $sku : null,
            'size_label'     => $this->nullableTrim($_POST['size_label'] ?? ''),
            'size_group'     => null,
            'image'          => $_POST['existing_image'] ?? null
        ];
        $newQty = $sellingType === 'sizes' ? 0 : intval($_POST['quantity'] ?? 0);
        $validationError = $this->validateProductPayload($data, $sizeOptions, $newQty, $sellingType);
        if ($validationError !== null) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $validationError]); exit; }
            flash('error', $validationError);
            header('Location: ' . APP_URL . '/index.php?url=admin/products/edit/' . $id);
            exit;
        }

        $cover = $this->storeUploadedImage($_FILES['image'] ?? null);
        if (!empty($cover['error'])) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $cover['error']]); exit; }
            flash('error', $cover['error']);
            header('Location: ' . APP_URL . '/index.php?url=admin/products/edit/' . $id);
            exit;
        }
        if (!empty($cover['filename'])) {
            $data['image'] = $cover['filename'];
            $this->productModel->addImage($id, $cover['filename']);
        }

        if ($data['sku'] && $this->productModel->skuExists($data['sku'], $id)) {
            $msg = 'SKU "' . htmlspecialchars($data['sku']) . '" already exists. Please use a unique SKU.';
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $msg]); exit; }
            flash('error', $msg);
            header('Location: ' . APP_URL . '/index.php?url=admin/products/edit/' . $id);
            exit;
        }

        // Track price change before updating
        $existingProduct = $this->productModel->findById($id);
        $oldPrice = floatval($existingProduct['price'] ?? 0);
        $newPrice = $data['price'];

        // Delete selected gallery images before syncing primary
        $deleteIds = array_map('intval', $_POST['delete_gallery_ids'] ?? []);
        foreach ($deleteIds as $imageId) {
            if ($imageId <= 0) continue;
            $removed = $this->productModel->deleteImage($imageId, $id);
            if ($removed && !empty($removed['filename'])) {
                $path = UPLOADS_PATH . '/' . $removed['filename'];
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }

        $this->storeGalleryUploads($id, $_FILES['gallery_images'] ?? null);

        if (!empty($cover['filename'])) {
            $data['image'] = $cover['filename'];
        } else {
            $syncedCover = $this->productModel->syncPrimaryImage($id);
            if ($syncedCover !== null) {
                $data['image'] = $syncedCover;
            } elseif (empty($data['image'])) {
                $data['image'] = $existingProduct['image'] ?? null;
            }
        }

        $this->productModel->update($id, $data);
        $this->productModel->replaceSizeOptions($id, $sizeOptions);

        // Record price history if price changed
        if (abs($oldPrice - $newPrice) > 0.001) {
            $reason = trim($_POST['price_change_reason'] ?? 'Price updated via product edit');
            $this->priceHistoryModel->record($id, $oldPrice, $newPrice, $_SESSION['user_id'], $reason);
            $this->logModel->create(LOG_PRICE_UPDATE, "Price updated for {$data['name']} (ID #{$id}): ₱" . number_format($oldPrice, 2) . " → ₱" . number_format($newPrice, 2));
        }

        if (isset($_POST['quantity'])) {
            $inv = new Inventory($this->pdo);
            $oldQty = intval($existingProduct['stock'] ?? 0);
            if (!empty($sizeOptions)) {
                $newQty = 0;
            }
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
                'stock'   => $newQty,
                'sku'     => $data['sku'],
                'category_id' => $data['category_id'],
                'size_label'  => $data['size_label'],
                'has_sizes'   => !empty($sizeOptions),
                'size_total_stock' => array_sum(array_map(static function ($opt) { return (int) $opt['stock']; }, $sizeOptions)),
                'sizes'       => $sizeOptions,
                'specifications' => Product::decodeSpecifications($data['specifications'] ?? null),
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

    public function bulkDelete() {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?url=admin/products');
            exit;
        }

        $ids = array_map('intval', $_POST['product_ids'] ?? []);
        $ids = array_values(array_unique(array_filter($ids)));

        if (empty($ids)) {
            flash('error', 'Please select at least one product to delete.');
            header('Location: ' . APP_URL . '/index.php?url=admin/products');
            exit;
        }

        $deleted = $this->productModel->deleteMany($ids);
        $this->logModel->create(
            LOG_PRODUCT_DELETE,
            'Bulk deleted ' . $deleted . ' product(s): #' . implode(', #', $ids)
        );

        flash('success', $deleted . ' product' . ($deleted === 1 ? '' : 's') . ' deleted.');
        header('Location: ' . APP_URL . '/index.php?url=admin/products');
        exit;
    }

    public function search() {
        $keyword    = trim($_GET['q'] ?? '');
        $products   = $keyword ? $this->attachDisplayStock($this->productModel->search($keyword)) : [];
        $categories = $this->categoryModel->getAll();
        $pageTitle  = 'Search: ' . htmlspecialchars($keyword);
        $extraCss   = ['customer.css'];
        // Use the alternate products layout (grid-focused) for search results
        require_once VIEWS_PATH . '/customer/products_alt.php';
    }

    private function itemsPerPage(): int {
        require_once MODELS_PATH . '/Setting.php';
        return (new Setting($this->pdo))->itemsPerPage();
    }

    private function attachDisplayStock(array $products): array {
        if (empty($products)) {
            return $products;
        }

        $summaries = $this->productModel->getSizeStockSummaryByProductIds(array_column($products, 'id'));
        $sizesByProduct = $this->productModel->getSizeOptionsByProductIds(array_column($products, 'id'));
        foreach ($products as &$product) {
            $productId = (int) $product['id'];
            $summary = $summaries[$productId] ?? null;
            $hasSizes = !empty($summary) && (int) ($summary['option_count'] ?? 0) > 0;
            $product['has_sizes'] = $hasSizes;
            $product['display_stock'] = $hasSizes
                ? (int) ($summary['total_stock'] ?? 0)
                : (int) ($product['stock'] ?? 0);
            $product['size_options'] = $hasSizes ? ($sizesByProduct[$productId] ?? []) : [];
        }
        unset($product);

        return $products;
    }

    private function nullableTrim($value): ?string {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function parseSpecificationsFromPost(): ?string {
        $keys = $_POST['spec_keys'] ?? [];
        $vals = $_POST['spec_values'] ?? [];
        if (!is_array($keys) || !is_array($vals)) {
            return null;
        }
        $pairs = [];
        $count = max(count($keys), count($vals));
        for ($i = 0; $i < $count; $i++) {
            $k = trim((string) ($keys[$i] ?? ''));
            $v = trim((string) ($vals[$i] ?? ''));
            if ($k === '' || $v === '') {
                continue;
            }
            $pairs[$k] = $v;
        }
        return Product::encodeSpecifications($pairs);
    }

    private function sellingTypeFromPost(): string {
        return (($_POST['selling_type'] ?? 'simple') === 'sizes') ? 'sizes' : 'simple';
    }

    private function resolveUpdateSellingPayload(int $productId): array {
        $postedSizes = $this->parseSizeOptionsFromPost();
        $radio = (string) ($_POST['selling_type'] ?? '');

        if (!empty($postedSizes)) {
            return ['sizes', $postedSizes];
        }
        if ($radio === 'simple') {
            return ['simple', []];
        }
        if ($radio === 'sizes') {
            return ['sizes', []];
        }

        $existing = $this->productModel->getSizeOptions($productId);
        if (!empty($existing)) {
            return ['sizes', $existing];
        }
        return ['simple', []];
    }

    private function sizeOptionsForSellingType(string $sellingType): array {
        return $sellingType === 'sizes' ? $this->parseSizeOptionsFromPost() : [];
    }

    private function listingPriceForSellingType(string $sellingType, array $sizeOptions, $postedPrice): float {
        if ($sellingType === 'sizes' && !empty($sizeOptions)) {
            $prices = array_map(static function ($opt) {
                return (float) ($opt['price'] ?? 0);
            }, $sizeOptions);
            return (float) min($prices);
        }
        return (float) $postedPrice;
    }

    private function parseSizeOptionsFromPost(): array {
        $labels = $_POST['size_opt_labels'] ?? [];
        $prices = $_POST['size_opt_prices'] ?? [];
        $stocks = $_POST['size_opt_stocks'] ?? [];
        if (!is_array($labels)) {
            return [];
        }
        $out = [];
        $count = count($labels);
        for ($i = 0; $i < $count; $i++) {
            $label = trim((string) ($labels[$i] ?? ''));
            if ($label === '') {
                continue;
            }
            $out[] = [
                'size_label' => $label,
                'price'      => (float) ($prices[$i] ?? 0),
                'stock'      => (int) ($stocks[$i] ?? 0),
            ];
        }
        return $out;
    }

    private function validateProductPayload(array $data, array $sizeOptions, int $quantity, string $sellingType = 'simple'): ?string {
        if (($data['category_id'] ?? 0) <= 0) {
            return 'Please select a valid category.';
        }
        if (!$this->categoryModel->findById($data['category_id'])) {
            return 'Selected category no longer exists.';
        }

        if (($data['name'] ?? '') === '') {
            return 'Product name is required.';
        }

        if (!isset($data['price']) || !is_numeric($data['price']) || (float) $data['price'] < 0) {
            return 'Price must be zero or greater.';
        }

        if ($quantity < 0) {
            return 'Stock quantity must be zero or greater.';
        }
        if ($sellingType === 'sizes' && empty($sizeOptions)) {
            return 'Add at least one size with a label, price, and stock.';
        }

        $seenLabels = [];
        foreach ($sizeOptions as $option) {
            $label = trim((string) ($option['size_label'] ?? ''));
            if ($label === '') {
                return 'Each size option must have a size label.';
            }
            $normalized = strtolower($label);
            if (isset($seenLabels[$normalized])) {
                return 'Duplicate size labels are not allowed for the same product.';
            }
            $seenLabels[$normalized] = true;

            if (!isset($option['price']) || !is_numeric($option['price']) || (float) $option['price'] < 0) {
                return 'Each size option must have a valid price.';
            }
            if (!isset($option['stock']) || !is_numeric($option['stock']) || (int) $option['stock'] < 0) {
                return 'Each size option must have a valid stock quantity.';
            }
        }

        return null;
    }

    /**
     * @return array{filename:?string,error:?string}
     */
    private function storeUploadedImage($file): array {
        if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['filename' => null, 'error' => null];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['filename' => null, 'error' => 'Image upload error (code ' . $file['error'] . '). Max size: 5 MB.'];
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_IMAGE_BYTES) {
            return ['filename' => null, 'error' => 'Image is too large. Maximum allowed size is 5 MB.'];
        }
        $imageInfo = @getimagesize($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $extMap  = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp', 'image/gif' => '.gif'];
        if (!$imageInfo || !in_array($imageInfo['mime'], $allowed, true)) {
            return ['filename' => null, 'error' => 'Unsupported image format. Please upload a JPG, PNG, WEBP, or GIF.'];
        }
        $ext = $extMap[$imageInfo['mime']] ?? '.jpg';
        $fileName = time() . '_' . uniqid() . $ext;
        $targetPath = UPLOADS_PATH . '/' . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['filename' => null, 'error' => 'Failed to save the uploaded image. Please try again.'];
        }
        return ['filename' => $fileName, 'error' => null];
    }

    private function storeGalleryUploads($productId, $files): void {
        if (!$files || !isset($files['name']) || !is_array($files['name'])) {
            return;
        }
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            $single = [
                'name'     => $files['name'][$i] ?? '',
                'type'     => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $files['size'][$i] ?? 0,
            ];
            $saved = $this->storeUploadedImage($single);
            if (!empty($saved['filename'])) {
                $this->productModel->addImage($productId, $saved['filename']);
            }
        }
        $this->productModel->syncPrimaryImage($productId);
    }
}
