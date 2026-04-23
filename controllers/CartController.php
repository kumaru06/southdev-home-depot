<?php
/**
 * SouthDev Home Depot – Cart Controller
 */

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';

class CartController {
    private $cartModel;
    private $productModel;

    public function __construct($pdo) {
        $this->cartModel   = new Cart($pdo);
        $this->productModel = new Product($pdo);
    }

    public function index() {
        AuthMiddleware::handle();
        $cartItems = $this->cartModel->getByUserId($_SESSION['user_id']);
        $cartTotal = $this->cartModel->getCartTotal($_SESSION['user_id']);
        $pageTitle = 'Shopping Cart';
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/cart.php';
    }

    public function add() {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $productId = intval($_POST['product_id'] ?? 0);
        $quantity  = max(1, intval($_POST['quantity'] ?? 1));

        $product = $this->productModel->findById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $existingCartItem = $this->cartModel->getItemByUserAndProduct($_SESSION['user_id'], $productId);
        $requestedTotalQty = $quantity + (int) ($existingCartItem['quantity'] ?? 0);

        if ($product['stock'] !== null && (int) $product['stock'] < $requestedTotalQty) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            return;
        }

        $this->cartModel->addItem($_SESSION['user_id'], $productId, $quantity);
        $count = $this->cartModel->getCartCount($_SESSION['user_id']);

        echo json_encode(['success' => true, 'cart_count' => $count]);
    }

    public function update() {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $cartId   = intval($_POST['cart_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));

        $cartItem = $this->cartModel->getItemById($cartId, $_SESSION['user_id']);
        if (!$cartItem) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            return;
        }

        if ($cartItem['stock'] !== null && (int) $cartItem['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock for the requested quantity']);
            return;
        }

        // Pass user_id to prevent IDOR (users modifying other users' carts)
        $this->cartModel->updateQuantity($cartId, $quantity, $_SESSION['user_id']);
        echo json_encode(['success' => true]);
    }

    public function remove() {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $cartId = intval($_POST['cart_id'] ?? 0);
        $this->cartModel->removeItem($cartId, $_SESSION['user_id']);
        echo json_encode(['success' => true]);
    }

    public function checkout() {
        AuthMiddleware::handle();
        $cartItems = $this->cartModel->getByUserId($_SESSION['user_id']);
        $cartTotal = $this->cartModel->getCartTotal($_SESSION['user_id']);

        if (empty($cartItems)) {
            flash('error', 'Your cart is empty.');
            header('Location: ' . APP_URL . '/index.php?url=cart');
            exit;
        }

        $pageTitle = 'Checkout';
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/checkout.php';
    }
}
