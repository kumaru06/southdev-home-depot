<?php
/**
 * SouthDev Home Depot – Payment Controller
 */

require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/PayMongoGateway.php';

class PaymentController {
    private $paymentModel;
    private $orderModel;
    private $logModel;
    private $payMongoGateway;

    public function __construct($pdo) {
        $this->paymentModel = new Payment($pdo);
        $this->orderModel   = new Order($pdo);
        $this->logModel     = new Log($pdo);
        
        // Initialize PayMongo if enabled
        if (defined('PAYMONGO_ENABLED') && PAYMONGO_ENABLED) {
            $this->payMongoGateway = new PayMongoGateway();
        }
    }

    public function process() {
        AuthMiddleware::handle();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $orderId       = intval($_POST['order_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? '';
        $order         = $this->orderModel->findById($orderId);

        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            flash('error', 'Invalid order.');
            header('Location: ' . APP_URL . '/index.php?url=orders');
            exit;
        }

        $paymentData = [
            'order_id'       => $orderId,
            'payment_method' => $paymentMethod,
            'amount'         => $order['total_amount'],
            'status'         => PAYMENT_PENDING
        ];

        $paymentId = $this->paymentModel->create($paymentData);
        $this->logModel->create(LOG_PAYMENT, "Payment initiated for Order #{$orderId} via {$paymentMethod} (₱" . number_format($order['total_amount'], 2) . ")");

        header('Location: ' . APP_URL . '/payment/payment-gateway.php?order_id=' . $orderId . '&method=' . $paymentMethod);
        exit;
    }

    /**
     * Create PayMongo GCash payment source via AJAX
     */
    public function createPayMongoSource() {
        AuthMiddleware::handle();
        
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = intval($input['order_id'] ?? 0);
            $method = $input['method'] ?? 'gcash';

            // Verify order exists and belongs to user
            $order = $this->orderModel->findById($orderId);
            if (!$order || $order['user_id'] != $_SESSION['user_id']) {
                throw new Exception('Invalid or unauthorized order');
            }

            // Verify payment hasn't been charged yet
            $existingPayment = $this->paymentModel->getByOrderId($orderId);
            if ($existingPayment && $existingPayment['status'] === PAYMENT_COMPLETED) {
                throw new Exception('This order has already been paid');
            }

            // Create PayMongo source
            $successUrl = APP_URL . '/payment/payment-success.php?order_id=' . $orderId;
            $failureUrl = APP_URL . '/payment/payment-failed.php?order_id=' . $orderId;

            $source = $this->payMongoGateway->createSource(
                $order['total_amount'],
                $method,
                "Order #{$order['order_number']}",
                $successUrl,
                $failureUrl
            );

            $sourceId = $source['data']['id'];
            $checkoutUrl = $source['data']['attributes']['redirect']['checkout_url'];

            // Store payment record with source ID
            if ($existingPayment) {
                // Update existing payment
                $this->paymentModel->updateSourceId($existingPayment['id'], $sourceId);
            } else {
                // Create new payment record
                $paymentData = [
                    'order_id' => $orderId,
                    'payment_method' => $method,
                    'amount' => $order['total_amount'],
                    'status' => PAYMENT_PENDING,
                    'source_id' => $sourceId
                ];
                $this->paymentModel->createWithSource($paymentData);
            }

            $this->logModel->create(
                LOG_PAYMENT,
                "PayMongo {$method} source created for Order #{$order['order_number']} (₱" . number_format($order['total_amount'], 2) . ")"
            );

            echo json_encode([
                'success' => true,
                'checkout_url' => $checkoutUrl,
                'source_id' => $sourceId
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            $this->logModel->create(LOG_PAYMENT, "PayMongo error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Handle PayMongo webhook for payment updates
     */
    public function handlePayMongoWebhook() {
        header('Content-Type: application/json');

        try {
            // Get raw payload for signature verification
            $payload = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_X_PAYMONGO_SIGNATURE'] ?? '';

            if (!$signature) {
                throw new Exception('Missing webhook signature');
            }

            // Verify webhook signature
            if (!$this->payMongoGateway->verifyWebhookSignature($payload, $signature)) {
                throw new Exception('Invalid webhook signature');
            }

            $event = json_decode($payload, true);
            $eventType = $event['data']['type'] ?? '';

            if ($eventType === 'payment.succeeded') {
                $this->handlePaymentSucceeded($event);
                echo json_encode(['success' => true]);
            } elseif ($eventType === 'payment.failed') {
                $this->handlePaymentFailed($event);
                echo json_encode(['success' => true]);
            } else {
                // Unknown event type, acknowledge anyway
                echo json_encode(['success' => true]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            $this->logModel->create(LOG_PAYMENT, "Webhook error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Process successful payment from webhook
     */
    private function handlePaymentSucceeded($event) {
        $paymentId = $event['data']['id'] ?? null;
        $sourceId = $event['data']['attributes']['source']['id'] ?? null;

        if (!$paymentId || !$sourceId) {
            throw new Exception('Missing payment or source ID in webhook');
        }

        // Find payment by source ID
        $payment = $this->paymentModel->getBySourceId($sourceId);
        if (!$payment) {
            throw new Exception('Payment source not found: ' . $sourceId);
        }

        // Update payment status to completed
        $this->paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED, $paymentId);

        // Update order status to processing
        $this->orderModel->updateStatus($payment['order_id'], ORDER_PROCESSING);

        $order = $this->orderModel->findById($payment['order_id']);
        $this->logModel->create(
            LOG_PAYMENT,
            "Payment confirmed for Order #{$order['order_number']} via {$payment['payment_method']}"
        );
    }

    /**
     * Process failed payment from webhook
     */
    private function handlePaymentFailed($event) {
        $sourceId = $event['data']['attributes']['source']['id'] ?? null;

        if (!$sourceId) {
            throw new Exception('Missing source ID in webhook');
        }

        $payment = $this->paymentModel->getBySourceId($sourceId);
        if ($payment) {
            $this->paymentModel->updateStatus($payment['id'], PAYMENT_FAILED);
            
            $order = $this->orderModel->findById($payment['order_id']);
            $this->logModel->create(
                LOG_PAYMENT,
                "Payment failed for Order #{$order['order_number']}"
            );
        }
    }
}
