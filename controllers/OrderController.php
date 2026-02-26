<?php
/**
 * SouthDev Home Depot – Order Controller
 * Handles orders, cancellation with stock restore, cancel requests, system logging
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/CancelRequest.php';
require_once __DIR__ . '/../models/Payment.php';

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

    /* ===== Customer: My Orders ===== */
    public function index() {
        AuthMiddleware::handle();
        $pageTitle = 'My Orders';
        $orders = $this->orderModel->getByUserId($_SESSION['user_id']);
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
                $this->orderModel->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
                $this->inventoryModel->adjustQuantity($item['product_id'], -$item['quantity']);
            }

            $this->cartModel->clearCart($_SESSION['user_id']);
            $this->pdo->commit();

            $this->logModel->create(LOG_ORDER_CREATE, "Order #{$orderId} placed, total: ₱" . number_format($totalAmount, 2));
            flash('success', 'Order placed successfully!');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            flash('error', 'Failed to place order. Please try again.');
            header('Location: ' . APP_URL . '/index.php?url=checkout');
        }
        exit;
    }

    /* ===== Customer: Direct Cancel (pending only) ===== */
    public function cancel($id) {
        AuthMiddleware::handle();
        AuthMiddleware::csrf();

        $reason = $this->buildCancelReasonFromPost();
        if ($reason === '') {
            flash('error', 'Please select a reason for cancelling your order.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $id);
            exit;
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
        AuthMiddleware::csrf();

        $orderId = intval($orderId);
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
        flash('success', 'Cancellation request submitted. Awaiting admin approval.');
        header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
        exit;
    }

    /* ===== Staff/Admin: Approve Cancel Request ===== */
    public function approveCancel($requestId) {
        AuthMiddleware::adminOrStaff();
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

        $this->logModel->create(LOG_CANCEL_APPROVE, "Cancel request #{$requestId} approved. Order #{$request['order_id']} cancelled, stock restored.");
        flash('success', 'Cancellation approved. Order cancelled and stock restored.');
        header('Location: ' . APP_URL . '/index.php?url=staff/cancel-requests');
        exit;
    }

    /* ===== Staff/Admin: Reject Cancel Request ===== */
    public function rejectCancel($requestId) {
        AuthMiddleware::adminOrStaff();
        AuthMiddleware::csrf();

        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $this->cancelModel->reject($requestId, $adminNotes);

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
        AuthMiddleware::csrf();

        $status = $_POST['status'] ?? '';
        $this->orderModel->updateStatus($id, $status);
        $this->logModel->create(LOG_ORDER_STATUS, "Order #{$id} status changed to: {$status}");

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
        $extraCss = ['admin.css'];
        require_once VIEWS_PATH . '/staff/manage-orders.php';
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

        $pageTitle = 'Checkout';
        $extraCss = ['customer.css'];
        require_once VIEWS_PATH . '/customer/checkout.php';
    }
}
