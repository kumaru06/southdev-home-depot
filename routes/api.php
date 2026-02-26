<?php
/**
 * API Routes (for AJAX requests)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$method = $_SERVER['REQUEST_METHOD'];

// API response helper
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Auth check for API
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$urlParts = explode('/', $url);

switch ($urlParts[0]) {
    case 'cart':
        require_once __DIR__ . '/../models/Cart.php';
        $cart = new Cart($pdo);

        if ($urlParts[1] === 'count') {
            jsonResponse(['count' => $cart->getCartCount($_SESSION['user_id'])]);
        }
        break;

    case 'orders':
        require_once __DIR__ . '/../models/Order.php';
        $order = new Order($pdo);

        if (isset($urlParts[1]) && $urlParts[1] === 'track') {
            $orderNumber = $_GET['order_number'] ?? '';
            $result = $order->getByOrderNumber($orderNumber);
            if ($result) {
                jsonResponse(['order' => $result]);
            } else {
                jsonResponse(['error' => 'Order not found'], 404);
            }
        }
        break;

    default:
        jsonResponse(['error' => 'Endpoint not found'], 404);
}
