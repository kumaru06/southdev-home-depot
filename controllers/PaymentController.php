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

        // If it's a JSON/AJAX request that ended up here, return a JSON error
        // instead of flashing a user-visible CSRF message
        $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
                   strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
        if ($isJson) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid endpoint']);
            exit;
        }

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
     * Returns true when PayMongo keys are still placeholder values (no real account yet).
     * In test mode every payment is simulated as successful locally.
     */
    private function isPayMongoTestMode(): bool {
        $sk = defined('PAYMONGO_SECRET_KEY') ? PAYMONGO_SECRET_KEY : '';
        return (strpos($sk, 'xxxxxxxxxxxx') !== false || $sk === '');
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
            $receiptEmail = trim($input['receipt_email'] ?? '');

            // Store receipt email in session for later use
            if ($receiptEmail && filter_var($receiptEmail, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['receipt_email'] = $receiptEmail;
            }

            // Verify order exists and belongs to user
            $order = $this->orderModel->findById($orderId);
            if (!$order || $order['user_id'] != $_SESSION['user_id']) {
                throw new Exception('Invalid or unauthorized order');
            }

            $existingPayment = $this->paymentModel->getByOrderId($orderId);
            if ($existingPayment && $existingPayment['status'] === PAYMENT_COMPLETED) {
                throw new Exception('This order has already been paid');
            }

            if ($this->isPayMongoTestMode()) {
                $fakeSourceId = 'test_src_' . uniqid();
                if ($existingPayment) {
                    $this->paymentModel->updateSourceId($existingPayment['id'], $fakeSourceId);
                } else {
                    $this->paymentModel->createWithSource([
                        'order_id'       => $orderId,
                        'payment_method' => $method,
                        'source_id'      => $fakeSourceId,
                        'amount'         => $order['total_amount'],
                        'status'         => PAYMENT_PENDING
                    ]);
                }
                $this->logModel->create(LOG_PAYMENT, "[TEST MODE] GCash simulated for Order #{$order['order_number']}");
                echo json_encode([
                    'success'      => true,
                    'checkout_url' => APP_URL . '/payment/payment-success.php?order_id=' . $orderId . '&test_mode=1',
                    'source_id'    => $fakeSourceId,
                    'test_mode'    => true
                ]);
                exit;
            }
            // ── END TEST MODE ───────────────────────────────────────────────

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

            if ($eventType === 'payment.succeeded' || $eventType === 'payment.paid') {
                $this->handlePaymentSucceeded($event);
                echo json_encode(['success' => true]);
            } elseif ($eventType === 'payment.failed') {
                $this->handlePaymentFailed($event);
                echo json_encode(['success' => true]);
            } elseif ($eventType === 'payment_intent.payment.paid') {
                $this->handlePaymentIntentPaid($event);
                echo json_encode(['success' => true]);
            } elseif ($eventType === 'payment_intent.payment.failed') {
                $this->handlePaymentIntentFailed($event);
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

        // Order stays pending — staff will advance to processing
        // (payment is marked completed, order awaits staff review)

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

    // ─── CARD PAYMENT (Payment Intent flow) ───────────────────────────────────

    /**
     * Handle payment_intent.payment.paid webhook event (card payment fully paid after 3DS)
     */
    private function handlePaymentIntentPaid($event) {
        $paymentIntentId = $event['data']['id'] ?? null;
        // PayMongo may also nest this inside attributes.payment_intent_id
        if (!$paymentIntentId) {
            $paymentIntentId = $event['data']['attributes']['payment_intent_id'] ?? null;
        }

        if (!$paymentIntentId) return;

        $payment = $this->paymentModel->getBySourceId($paymentIntentId);
        if ($payment && $payment['status'] !== PAYMENT_COMPLETED) {
            $this->paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED, $paymentIntentId);
            // Order stays pending — staff will advance to processing

            $order = $this->orderModel->findById($payment['order_id']);
            $this->logModel->create(
                LOG_PAYMENT,
                "Card payment confirmed (webhook) for Order #{$order['order_number']}"
            );
        }
    }

    /**
     * Handle payment_intent.payment.failed webhook event (card payment failed or expired)
     */
    private function handlePaymentIntentFailed($event) {
        $paymentIntentId = $event['data']['id'] ?? null;
        if (!$paymentIntentId) {
            $paymentIntentId = $event['data']['attributes']['payment_intent_id'] ?? null;
        }

        if (!$paymentIntentId) return;

        $payment = $this->paymentModel->getBySourceId($paymentIntentId);
        if ($payment) {
            $this->paymentModel->updateStatus($payment['id'], PAYMENT_FAILED);
            $order = $this->orderModel->findById($payment['order_id']);
            $this->logModel->create(
                LOG_PAYMENT,
                "Card payment failed (webhook) for Order #{$order['order_number']}"
            );
        }
    }
    /**
     * Step 1: Create PayMongo Payment Intent for card payment (called via AJAX)
     * Returns payment_intent_id and client_key to the frontend.
     */
    public function createCardPaymentIntent() {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        try {
            $input   = json_decode(file_get_contents('php://input'), true);
            $orderId = intval($input['order_id'] ?? 0);

            $order = $this->orderModel->findById($orderId);
            if (!$order || $order['user_id'] != $_SESSION['user_id']) {
                throw new Exception('Invalid or unauthorized order');
            }

            $existing = $this->paymentModel->getByOrderId($orderId);
            if ($existing && $existing['status'] === PAYMENT_COMPLETED) {
                throw new Exception('This order has already been paid');
            }

            // ── TEST MODE ───────────────────────────────────────────────────
            if ($this->isPayMongoTestMode()) {
                $fakeIntentId  = 'test_pi_' . uniqid();
                $fakeClientKey = 'test_ck_' . uniqid();
                if ($existing) {
                    $this->paymentModel->updateSourceAndClientKey($existing['id'], $fakeIntentId, $fakeClientKey);
                } else {
                    $this->paymentModel->createWithSource([
                        'order_id'       => $orderId,
                        'payment_method' => 'card',
                        'source_id'      => $fakeIntentId,
                        'client_key'     => $fakeClientKey,
                        'amount'         => $order['total_amount'],
                        'status'         => PAYMENT_PENDING
                    ]);
                }
                $this->logModel->create(LOG_PAYMENT, "[TEST MODE] Card intent simulated for Order #{$order['order_number']}");
                echo json_encode([
                    'success'           => true,
                    'payment_intent_id' => $fakeIntentId,
                    'client_key'        => $fakeClientKey,
                    'public_key'        => PAYMONGO_PUBLIC_KEY,
                    'test_mode'         => true
                ]);
                exit;
            }
            // ── END TEST MODE ───────────────────────────────────────────────

            $intent    = $this->payMongoGateway->createPaymentIntent(
                $order['total_amount'],
                "Order #{$order['order_number']}"
            );
            $intentId  = $intent['data']['id'];
            $clientKey = $intent['data']['attributes']['client_key'];

            if ($existing) {
                $this->paymentModel->updateSourceAndClientKey($existing['id'], $intentId, $clientKey);
            } else {
                $this->paymentModel->createWithSource([
                    'order_id'       => $orderId,
                    'payment_method' => 'card',
                    'source_id'      => $intentId,
                    'client_key'     => $clientKey,
                    'amount'         => $order['total_amount'],
                    'status'         => PAYMENT_PENDING
                ]);
            }

            $this->logModel->create(
                LOG_PAYMENT,
                "PayMongo card Payment Intent created for Order #{$order['order_number']} (₱" . number_format($order['total_amount'], 2) . ")"
            );

            echo json_encode([
                'success'            => true,
                'payment_intent_id'  => $intentId,
                'client_key'         => $clientKey,
                'public_key'         => PAYMONGO_PUBLIC_KEY
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            $this->logModel->create(LOG_PAYMENT, "Card intent error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Step 2: Attach PaymentMethod to Payment Intent (called via AJAX after frontend tokenises card)
     * Returns success + redirect URL (for 3DS) or direct success.
     */
    public function attachCardPaymentMethod() {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        try {
            $input           = json_decode(file_get_contents('php://input'), true);
            $orderId         = intval($input['order_id'] ?? 0);
            $paymentMethodId = $input['payment_method_id'] ?? '';
            $paymentIntentId = $input['payment_intent_id'] ?? '';
            $clientKey       = $input['client_key'] ?? '';
            $receiptEmail    = trim($input['receipt_email'] ?? '');

            // Store receipt email in session for later use
            if ($receiptEmail && filter_var($receiptEmail, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['receipt_email'] = $receiptEmail;
            }

            if (!$paymentMethodId || !$paymentIntentId || !$clientKey) {
                throw new Exception('Missing required payment data');
            }

            $order = $this->orderModel->findById($orderId);
            if (!$order || $order['user_id'] != $_SESSION['user_id']) {
                throw new Exception('Invalid or unauthorized order');
            }

            // ── TEST MODE ───────────────────────────────────────────────────
            if ($this->isPayMongoTestMode() || strpos($paymentIntentId, 'test_pi_') === 0) {
                $payment = $this->paymentModel->getByOrderId($orderId);
                if ($payment) {
                    $this->paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED, $paymentIntentId);
                }
                // Order stays pending — staff will advance to processing
                $this->logModel->create(LOG_PAYMENT, "[TEST MODE] Card payment simulated for Order #{$order['order_number']}");
                echo json_encode([
                    'success'      => true,
                    'status'       => 'succeeded',
                    'redirect_url' => APP_URL . '/payment/payment-success.php?order_id=' . $orderId . '&test_mode=1',
                    'test_mode'    => true
                ]);
                exit;
            }
            // ── END TEST MODE ───────────────────────────────────────────────

            $returnUrl = APP_URL . '/payment/payment-success.php?order_id=' . $orderId . '&intent_id=' . urlencode($paymentIntentId) . '&client_key=' . urlencode($clientKey);

            $result = $this->payMongoGateway->attachPaymentMethod(
                $paymentIntentId,
                $paymentMethodId,
                $clientKey,
                $returnUrl
            );

            $status     = $result['data']['attributes']['status'] ?? '';
            $nextAction = $result['data']['attributes']['next_action'] ?? null;

            if ($status === 'succeeded') {
                // No 3DS needed – mark complete right away
                $payment = $this->paymentModel->getByOrderId($orderId);
                if ($payment) {
                    $this->paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED, $paymentIntentId);
                }
                // Order stays pending — staff will advance to processing
                $this->logModel->create(LOG_PAYMENT, "Card payment succeeded for Order #{$order['order_number']}");

                echo json_encode([
                    'success'      => true,
                    'status'       => 'succeeded',
                    'redirect_url' => APP_URL . '/payment/payment-success.php?order_id=' . $orderId
                ]);

            } elseif ($status === 'awaiting_next_action' && isset($nextAction['redirect']['url'])) {
                // 3DS authentication required
                echo json_encode([
                    'success'      => true,
                    'status'       => 'requires_action',
                    'redirect_url' => $nextAction['redirect']['url']
                ]);

            } else {
                throw new Exception('Payment failed or unexpected status: ' . $status);
            }

        } catch (Exception $e) {
            http_response_code(400);
            $this->logModel->create(LOG_PAYMENT, "Card attach error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }}