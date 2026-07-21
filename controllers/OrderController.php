<?php
/**
 * SouthDev Home Depot – Order Controller
 * Handles orders, cancellation with stock restore, cancel requests, system logging
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/CancelRequest.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/ReturnRequest.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../includes/Mailer.php';

class OrderController {
    private $orderModel;
    private $cartModel;
    private $inventoryModel;
    private $logModel;
    private $cancelModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->orderModel     = new Order($pdo);
        $this->cartModel      = new Cart($pdo);
        $this->inventoryModel = new Inventory($pdo);
        $this->logModel       = new Log($pdo);
        $this->cancelModel    = new CancelRequest($pdo);
    }

    private function buildCancelReasonFromPost() {
        $selected = trim($_POST['cancel_reason'] ?? '');
        $other = trim($_POST['cancel_reason_other'] ?? '');

        if ($selected === 'other') {
            $selected = $other;
        }

        $selected = trim($selected);
        if ($selected === '') return '';

        // Hard cap to avoid storing excessively long payloads
        if (strlen($selected) > 1000) {
            $selected = substr($selected, 0, 1000);
        }
        return $selected;
    }

    private function requirePost(string $fallbackUrl): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $fallbackUrl);
            exit;
        }
    }

    public function index() {
        AuthMiddleware::handle();
        $pageTitle = 'My Orders';
        $allOrders = $this->orderModel->getByUserId($_SESSION['user_id']);

        $selectedOrderDate = trim((string) ($_GET['order_date'] ?? ''));
        $hasOrderDateFilter = false;

        if ($selectedOrderDate !== '') {
            $date = DateTime::createFromFormat('Y-m-d', $selectedOrderDate);
            $hasOrderDateFilter = $date && $date->format('Y-m-d') === $selectedOrderDate;
            if (!$hasOrderDateFilter) {
                $selectedOrderDate = '';
            }
        }

        $filteredOrders = $allOrders;
        if ($hasOrderDateFilter) {
            $filteredOrders = array_values(array_filter($allOrders, function ($order) use ($selectedOrderDate) {
                return !empty($order['created_at']) && substr((string) $order['created_at'], 0, 10) === $selectedOrderDate;
            }));
        }

        $ordersForStats = $filteredOrders;
        $totalFilteredOrders = count($filteredOrders);
        $ordersPerPage = 8;
        $totalPages = max(1, (int) ceil($totalFilteredOrders / $ordersPerPage));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $ordersPerPage;
        $orders = array_slice($filteredOrders, $offset, $ordersPerPage);

        // Load return request data for all orders so we can show refund badges
        $returnModel = new ReturnRequest($this->pdo);
        $orderIds = array_column($orders, 'id');
        $returnsByOrder = $returnModel->getByOrderIds($orderIds, $_SESSION['user_id']);

        // Load cancel request data for processing orders
        $cancelsByOrder = $this->cancelModel->getByOrderIds($orderIds, $_SESSION['user_id']);

        $extraCss = ['customer.css'];
        require_once VIEWS_PATH . '/customer/orders.php';
    }

    /* ===== Order Detail ===== */
    public function show($id) {
        AuthMiddleware::handle();
        $order = $this->orderModel->findById($id);

        if (!$order) {
            require_once VIEWS_PATH . '/errors/404.php';
            return;
        }

        if ($_SESSION['role_id'] == ROLE_CUSTOMER && $order['user_id'] != $_SESSION['user_id']) {
            require_once VIEWS_PATH . '/errors/403.php';
            return;
        }

        $orderItems = $this->orderModel->getItems($id);
        $payment = (new Payment($this->pdo))->getByOrderId($id);
        $returnModel = new ReturnRequest($this->pdo);
        $returnRequest = $returnModel->getByOrderId($id);
        $cancelRequest = $this->cancelModel->getByOrderId($id);
        $pageTitle = 'Order ' . $order['order_number'];

        if ($_SESSION['role_id'] == ROLE_CUSTOMER) {
            $extraCss = ['customer.css'];
            require_once VIEWS_PATH . '/customer/order-details.php';
        } else {
            $isAdmin = true;
            $extraCss = ['admin.css'];
            require_once VIEWS_PATH . '/staff/order-details.php';
        }
    }

    /* ===== Place Order (from checkout) ===== */
    public function create() {
        AuthMiddleware::handle();
        $this->requirePost(APP_URL . '/index.php?url=checkout');
        AuthMiddleware::csrf();

        $cartItems = $this->cartModel->getByUserId($_SESSION['user_id']);
        if (empty($cartItems)) {
            flash('error', 'Your cart is empty.');
            header('Location: ' . APP_URL . '/index.php?url=cart');
            exit;
        }

        try {
            $this->pdo->beginTransaction();

            $totalAmount = $this->cartModel->getCartTotal($_SESSION['user_id']);
            $paymentMethod = trim($_POST['payment_method'] ?? 'cod');

            $orderId = $this->orderModel->create([
                'user_id'          => $_SESSION['user_id'],
                'total_amount'     => $totalAmount,
                'shipping_address' => trim($_POST['shipping_address']),
                'shipping_city'    => trim($_POST['shipping_city'] ?? ''),
                'shipping_state'   => trim($_POST['shipping_state'] ?? ''),
                'shipping_zip'     => trim($_POST['shipping_zip'] ?? ''),
                'notes'            => trim($_POST['notes'] ?? '')
            ]);

            foreach ($cartItems as $item) {
                $reserved = $this->inventoryModel->reserveQuantity($item['product_id'], (int) $item['quantity']);
                if (!$reserved) {
                    $inventoryRow = $this->inventoryModel->getByProductId($item['product_id']);
                    throw new Exception('Insufficient stock for ' . ($inventoryRow['product_name'] ?? $item['product_name'] ?? 'one or more items') . '.');
                }

                $this->orderModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
            }

            // Create payment record so payment method is tracked
            $paymentModel = new Payment($this->pdo);
            $paymentModel->create([
                'order_id'       => $orderId,
                'payment_method' => $paymentMethod,
                'amount'         => $totalAmount,
                'status'         => ($paymentMethod === 'cod') ? 'completed' : 'pending'
            ]);

            $this->cartModel->clearCart($_SESSION['user_id']);
            $this->pdo->commit();

            $this->logModel->create(LOG_ORDER_CREATE, "Order #{$orderId} placed via {$paymentMethod}, total: ₱" . number_format($totalAmount, 2));

            // For COD/bank: notify immediately. For online payments (gcash/card/qrph):
            // no notification here — payment-success.php sends 'Order Placed' on success,
            // payment-failed.php sends 'Order Cancelled' on failure.
            if (!in_array($paymentMethod, ['gcash', 'card', 'qrph'])) {
                try {
                    $orderData = $this->orderModel->findById($orderId);
                    $notifModel = new Notification($this->pdo);
                    $notifModel->create(
                        $_SESSION['user_id'],
                        'Order Placed',
                        "Your order #{$orderData['order_number']} has been placed successfully! Total: ₱" . number_format($totalAmount, 2),
                        'order_processing',
                        APP_URL . '/index.php?url=orders/' . $orderId
                    );
                } catch (Throwable $e) { /* silent */ }
            }

            // Redirect to payment gateway for online payment methods
            if (in_array($paymentMethod, ['gcash', 'card', 'qrph'])) {
                header('Location: ' . APP_URL . '/payment/payment-gateway.php?order_id=' . $orderId . '&method=' . $paymentMethod);
            } else {
                flash('success', 'Order placed successfully!');
                header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
            }
        } catch (Exception $e) {
            $this->pdo->rollBack();
            flash('error', $e->getMessage() ?: 'Failed to place order. Please try again.');
            header('Location: ' . APP_URL . '/index.php?url=checkout');
            exit;
        }
        exit;
    }

    /* ===== Customer: Direct Cancel (pending only) ===== */
    public function cancel($id) {
        AuthMiddleware::handle();
        $this->requirePost(APP_URL . '/index.php?url=orders/' . intval($id));
        AuthMiddleware::csrf();

        // Reason is optional for pending orders (direct cancel)
        $reason = $this->buildCancelReasonFromPost();
        if ($reason === '') {
            $reason = 'Cancelled by customer';
        }

        $order = $this->orderModel->findById($id);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            flash('error', 'Order not found.');
            header('Location: ' . APP_URL . '/index.php?url=orders');
            exit;
        }

        if ($order['status'] !== ORDER_PENDING) {
            flash('error', 'Only pending orders can be cancelled directly. Submit a cancellation request instead.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $id);
            exit;
        }

        if ($this->orderModel->cancelOrder($id, $_SESSION['user_id'], $reason)) {
            $this->logModel->create(LOG_ORDER_CANCEL, "Order #{$id} ({$order['order_number']}) cancelled by customer. Stock restored.");

            // Notify customer: order cancelled
            try {
                $notifModel = new Notification($this->pdo);
                $notifModel->create(
                    $_SESSION['user_id'],
                    'Order Cancelled',
                    "Your order #{$order['order_number']} has been cancelled." . ($reason ? " Reason: {$reason}" : ''),
                    'order_cancelled',
                    APP_URL . '/index.php?url=orders/' . $id
                );
            } catch (Throwable $e) { /* silent */ }

            flash('success', 'Order cancelled. Stock has been restored.');
        } else {
            flash('error', 'Unable to cancel this order.');
        }
        header('Location: ' . APP_URL . '/index.php?url=orders/' . $id);
        exit;
    }

    /* ===== Customer: Submit Cancel Request (for processing orders) ===== */
    public function requestCancel($orderId) {
        AuthMiddleware::handle();
        $orderId = intval($orderId);
        $this->requirePost(APP_URL . '/index.php?url=orders/' . $orderId);
        AuthMiddleware::csrf();
        $reason  = $this->buildCancelReasonFromPost();
        $order   = $this->orderModel->findById($orderId);

        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            flash('error', 'Order not found.');
            header('Location: ' . APP_URL . '/index.php?url=orders');
            exit;
        }

        if ($order['status'] !== ORDER_PROCESSING) {
            flash('error', 'Cannot request cancellation for this order status.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
            exit;
        }

        if ($this->cancelModel->hasExistingRequest($orderId)) {
            flash('warning', 'A cancellation request already exists for this order.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
            exit;
        }

        if ($reason === '') {
            flash('error', 'Please select a reason for your cancellation request.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
            exit;
        }

        $this->cancelModel->create([
            'order_id' => $orderId,
            'user_id'  => $_SESSION['user_id'],
            'reason'   => $reason
        ]);

        $this->logModel->create(LOG_CANCEL_REQUEST, "Cancel request submitted for Order #{$orderId}");

        // Notify customer: cancellation request submitted
        try {
            $notifModel = new Notification($this->pdo);
            $notifModel->create(
                $_SESSION['user_id'],
                'Cancellation Requested',
                "Your cancellation request for order #{$order['order_number']} has been submitted and is awaiting approval.",
                'cancel_requested',
                APP_URL . '/index.php?url=orders/' . $orderId
            );
        } catch (Throwable $e) { /* silent */ }

        flash('success', 'Cancellation request submitted. Awaiting admin approval.');
        header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
        exit;
    }

    /* ===== Staff/Admin: Approve Cancel Request ===== */
    public function approveCancel($requestId) {
        AuthMiddleware::adminOrStaff();
        $this->requirePost(APP_URL . '/index.php?url=staff/cancel-requests');
        AuthMiddleware::csrf();

        $request = $this->cancelModel->findById($requestId);
        if (!$request || $request['status'] !== 'pending') {
            flash('error', 'Cancel request not found or already processed.');
            header('Location: ' . APP_URL . '/index.php?url=staff/cancel-requests');
            exit;
        }

        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $this->cancelModel->approve($requestId, $adminNotes);
        $this->orderModel->cancelOrder($request['order_id'], null, $request['reason'] ?? null);

        // Notify customer: cancellation approved
        try {
            $order = $this->orderModel->findById($request['order_id']);
            if ($order) {
                $notifModel = new Notification($this->pdo);
                $notifModel->create(
                    $request['user_id'],
                    'Cancellation Approved',
                    "Your cancellation request for order #{$order['order_number']} has been approved.",
                    'cancel_approved',
                    APP_URL . '/index.php?url=orders/' . $request['order_id']
                );
            }
        } catch (Throwable $e) { /* silent */ }

        $this->logModel->create(LOG_CANCEL_APPROVE, "Cancel request #{$requestId} approved. Order #{$request['order_id']} cancelled, stock restored.");
        flash('success', 'Cancellation approved. Order cancelled and stock restored.');
        header('Location: ' . APP_URL . '/index.php?url=staff/cancel-requests');
        exit;
    }

    /* ===== Staff/Admin: Reject Cancel Request ===== */
    public function rejectCancel($requestId) {
        AuthMiddleware::adminOrStaff();
        $this->requirePost(APP_URL . '/index.php?url=staff/cancel-requests');
        AuthMiddleware::csrf();

        $adminNotes = trim($_POST['admin_notes'] ?? '');

        // Fetch request data BEFORE rejecting so we have user_id/order_id
        $request = $this->cancelModel->findById($requestId);
        $this->cancelModel->reject($requestId, $adminNotes);

        // Notify customer: cancellation rejected
        try {
            if ($request) {
                $order = $this->orderModel->findById($request['order_id']);
                if ($order) {
                    $notifModel = new Notification($this->pdo);
                    $notifModel->create(
                        $request['user_id'],
                        'Cancellation Rejected',
                        "Your cancellation request for order #{$order['order_number']} has been rejected." . ($adminNotes ? " Reason: {$adminNotes}" : ''),
                        'cancel_rejected',
                        APP_URL . '/index.php?url=orders/' . $request['order_id']
                    );
                }
            }
        } catch (Throwable $e) { /* silent */ }

        $this->logModel->create(LOG_CANCEL_REJECT, "Cancel request #{$requestId} rejected.");
        flash('success', 'Cancellation request rejected.');
        header('Location: ' . APP_URL . '/index.php?url=staff/cancel-requests');
        exit;
    }

    /* ===== Staff/Admin: Manage Cancel Requests ===== */
    public function cancelRequests() {
        AuthMiddleware::adminOrStaff();
        $pageTitle = 'Cancel Requests';
        $isAdmin = true;
        $status = $_GET['status'] ?? null;
        $cancelRequests = $this->cancelModel->getAll($status);
        $extraCss = ['admin.css'];
        require_once VIEWS_PATH . '/staff/cancel-requests.php';
    }

    /* ===== Staff: Update Order Status ===== */
    public function updateStatus($id) {
        AuthMiddleware::adminOrStaff();
        $this->requirePost(APP_URL . '/index.php?url=staff/orders/' . intval($id));
        AuthMiddleware::csrf();

        $status = $_POST['status'] ?? '';
        $allowedStatuses = [ORDER_PENDING, ORDER_PROCESSING, ORDER_SHIPPED, ORDER_DELIVERED];
        if (!in_array($status, $allowedStatuses, true)) {
            flash('error', 'Invalid order status.');
            header('Location: ' . APP_URL . '/index.php?url=staff/orders/' . $id);
            exit;
        }

        $order = $this->orderModel->findById($id);
        if (!$order) {
            flash('error', 'Order not found.');
            header('Location: ' . APP_URL . '/index.php?url=staff/orders');
            exit;
        }

        // Order fulfillment is forward-only and sequential:
        // pending -> processing -> shipped -> delivered.
        $nextStatus = [
            ORDER_PENDING    => ORDER_PROCESSING,
            ORDER_PROCESSING => ORDER_SHIPPED,
            ORDER_SHIPPED    => ORDER_DELIVERED,
        ];
        $currentStatus = (string) ($order['status'] ?? '');
        $expectedStatus = $nextStatus[$currentStatus] ?? null;
        if ($expectedStatus === null || $status !== $expectedStatus) {
            $message = in_array($currentStatus, [ORDER_DELIVERED, ORDER_CANCELLED], true)
                ? 'This order is already final and its status can no longer be changed.'
                : 'Invalid status transition. Orders must follow Pending → Processing → Shipped → Delivered and cannot move backward.';
            flash('error', $message);
            header('Location: ' . APP_URL . '/index.php?url=staff/orders/' . $id);
            exit;
        }

        $this->orderModel->updateStatus($id, $status);
        $this->logModel->create(LOG_ORDER_STATUS, "Order #{$id} status changed: {$currentStatus} → {$status}");

        // Auto-mark COD payment as paid when order is delivered
        if ($status === ORDER_DELIVERED) {
            try {
                $paymentModel = new Payment($this->pdo);
                $payment = $paymentModel->getByOrderId($id);
                if ($payment && strtolower($payment['payment_method']) === PAYMENT_METHOD_COD
                    && $payment['status'] === PAYMENT_PENDING) {
                    $paymentModel->updateStatus($payment['id'], PAYMENT_COMPLETED);
                }
            } catch (Throwable $e) { /* silent */ }
        }

        // Notify customer about order status change
        if ($order) {
            try {
                $notifModel = new Notification($this->pdo);
                $msg = Notification::orderStatusMessage($status, $order['order_number']);
                $notifModel->create(
                    $order['user_id'],
                    $msg['title'],
                    $msg['message'],
                    $msg['type'],
                    APP_URL . '/index.php?url=orders/' . $id
                );
            } catch (Throwable $e) { /* silent */ }

            // Send status update email (only for meaningful statuses)
            $emailStatuses = [ORDER_PROCESSING, ORDER_SHIPPED, ORDER_DELIVERED, ORDER_CANCELLED];
            if (in_array($status, $emailStatuses) && !empty($order['email'])) {
                try {
                    $statusMeta = [
                        ORDER_PROCESSING => [
                            'label'   => 'Processing',
                            'color'   => '#3B82F6',
                            'icon'    => '⚙️',
                            'message' => 'Great news! Your order is now being processed. Our team is preparing your items for shipment.',
                            'next'    => 'We will notify you again once your order has been shipped. You can track your order anytime by clicking the button below.',
                        ],
                        ORDER_SHIPPED => [
                            'label'   => 'Shipped',
                            'color'   => '#8B5CF6',
                            'icon'    => '🚚',
                            'message' => 'Your order is on its way! It has been handed over to our delivery partner.',
                            'next'    => 'Expect your delivery within the estimated timeframe. Please make sure someone is available to receive the package.',
                        ],
                        ORDER_DELIVERED => [
                            'label'   => 'Delivered',
                            'color'   => '#16A34A',
                            'icon'    => '✅',
                            'message' => 'Your order has been delivered! We hope you love your purchase.',
                            'next'    => 'Enjoying your items? Leave a review on our website to help other customers. Thank you for shopping with us!',
                        ],
                        ORDER_CANCELLED => [
                            'label'   => 'Cancelled',
                            'color'   => '#DC2626',
                            'icon'    => '✖',
                            'message' => 'Your order has been cancelled. If you did not request this cancellation, please contact us.',
                            'next'    => 'Any payment made will be refunded according to our refund policy. Feel free to place a new order anytime.',
                        ],
                    ];

                    $meta = $statusMeta[$status];
                    $customerName = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
                    $orderUrl = APP_URL . '/index.php?url=orders/' . $id;

                    $templatePath = ROOT_PATH . '/templates/email/order-status.html';
                    $html = file_get_contents($templatePath);
                    $html = str_replace('{{app_name}}',       APP_NAME,                                                       $html);
                    $html = str_replace('{{customer_name}}',  htmlspecialchars($customerName),                                $html);
                    $html = str_replace('{{order_number}}',   htmlspecialchars($order['order_number']),                       $html);
                    $html = str_replace('{{order_date}}',     date('F j, Y', strtotime($order['created_at'])),                $html);
                    $html = str_replace('{{total_amount}}',   number_format($order['total_amount'], 2),                       $html);
                    $html = str_replace('{{status_label}}',   $meta['label'],                                                 $html);
                    $html = str_replace('{{status_color}}',   $meta['color'],                                                 $html);
                    $html = str_replace('{{status_icon}}',    $meta['icon'],                                                  $html);
                    $html = str_replace('{{status_message}}', $meta['message'],                                               $html);
                    $html = str_replace('{{next_step}}',      $meta['next'],                                                  $html);
                    $html = str_replace('{{order_url}}',      $orderUrl,                                                      $html);
                    $html = str_replace('{{receipt_email}}',  htmlspecialchars($order['email']),                              $html);

                    $subject = $meta['icon'] . ' Order ' . $meta['label'] . ' - #' . $order['order_number'] . ' | ' . APP_NAME;
                    (new Mailer())->send($order['email'], $customerName, $subject, $html);
                } catch (Throwable $e) {
                    error_log('Order status email failed for order ' . $id . ': ' . $e->getMessage());
                }
            }
        }

        flash('success', 'Order status updated.');
        header('Location: ' . APP_URL . '/index.php?url=staff/orders/' . $id);
        exit;
    }

    /* ===== Staff/Admin: Manage All Orders ===== */
    public function manage() {
        AuthMiddleware::adminOrStaff();
        $pageTitle = 'Manage Orders';
        $isAdmin = true;
        $status = $_GET['status'] ?? null;
        $orders = $this->orderModel->getAll($status);

        // Load return & cancel request data for all orders
        $orderIds = array_column($orders, 'id');
        $returnModel = new ReturnRequest($this->pdo);
        $returnsByOrder = $returnModel->getByOrderIds($orderIds);
        $cancelsByOrder = $this->cancelModel->getByOrderIds($orderIds);

        $extraCss = ['admin.css'];
        require_once VIEWS_PATH . '/staff/manage-orders.php';
    }

    /**
     * AJAX: pending counts for admin/staff sidebar badges
     */
    public function apiPendingCount() {
        AuthMiddleware::adminOrStaff();
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $returnModel = new ReturnRequest($this->pdo);
        $orders  = (int) $this->orderModel->countByStatus(ORDER_PENDING);
        $cancels = (int) $this->cancelModel->countPending();
        $returns = (int) $returnModel->countPending();

        echo json_encode([
            'count'   => $orders, // backward-compatible alias
            'orders'  => $orders,
            'cancels' => $cancels,
            'returns' => $returns,
        ]);
        exit;
    }

    /* ===== Customer: Checkout Page ===== */
    public function checkout() {
        AuthMiddleware::handle();
        $cartItems = $this->cartModel->getByUserId($_SESSION['user_id']);
        $cartTotal = $this->cartModel->getCartTotal($_SESSION['user_id']);

        if (empty($cartItems)) {
            flash('error', 'Your cart is empty.');
            header('Location: ' . APP_URL . '/index.php?url=cart');
            exit;
        }

        // Fetch saved profile address so checkout can pre-fill fields
        $userModel = new User($this->pdo);
        $savedUser = $userModel->findById($_SESSION['user_id']) ?: null;

        $pageTitle = 'Checkout';
        $extraCss = ['customer.css'];
        require_once VIEWS_PATH . '/customer/checkout.php';
    }
}
