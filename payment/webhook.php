<?php
/**
 * PayMongo Webhook Handler
 * Handles payment confirmation and update events from PayMongo
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/PaymentController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Process webhook
try {
    $controller = new PaymentController($pdo);
    $controller->handlePayMongoWebhook();
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
