<?php
/**
 * SouthDev Home Depot – Return Controller
 */

require_once __DIR__ . '/../models/ReturnRequest.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../models/DamagedProduct.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/Notification.php';

class ReturnController {
    private $returnModel;
    private $orderModel;
    private $orderItemModel;
    private $damagedModel;
    private $inventoryModel;
    private $stockMovementModel;
    private $logModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo               = $pdo;
        $this->returnModel       = new ReturnRequest($pdo);
        $this->orderModel        = new Order($pdo);
        $this->orderItemModel    = new OrderItem($pdo);
        $this->damagedModel      = new DamagedProduct($pdo);
        $this->inventoryModel    = new Inventory($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
        $this->logModel          = new Log($pdo);
    }

    public function requestForm($orderId) {
        AuthMiddleware::handle();
        $order = $this->orderModel->findById($orderId);

        if (!$order || $order['user_id'] != $_SESSION['user_id'] || $order['status'] != 'delivered') {
            flash('error', 'This order is not eligible for a return request.');
            header('Location: ' . APP_URL . '/index.php?url=orders');
            exit;
        }

        if ($this->returnModel->hasExistingRequest($orderId)) {
            flash('warning', 'A return request already exists for this order.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
            exit;
        }

        $pageTitle = 'Request Return';
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/request-return.php';
    }

    public function submit() {
        AuthMiddleware::handle();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $data = [
            'order_id' => intval($_POST['order_id']),
            'user_id'  => $_SESSION['user_id'],
            'reason'   => trim($_POST['reason'])
        ];

        // Validate: only delivered orders can have return requests
        $order = $this->orderModel->findById($data['order_id']);
        if (!$order || $order['user_id'] != $_SESSION['user_id'] || $order['status'] !== 'delivered') {
            flash('error', 'This order is not eligible for a return request.');
            header('Location: ' . APP_URL . '/index.php?url=orders');
            exit;
        }

        // Prevent duplicate return requests
        if ($this->returnModel->hasExistingRequest($data['order_id'])) {
            flash('warning', 'A return request already exists for this order.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $data['order_id']);
            exit;
        }

        if ($this->returnModel->create($data)) {
            $this->logModel->create(LOG_RETURN_REQUEST, "Return request submitted for Order #{$data['order_id']}");

            // Notify customer: return request submitted
            try {
                $notifModel = new Notification($this->pdo);
                $notifModel->create(
                    $_SESSION['user_id'],
                    'Return Requested',
                    "Your return request for order #{$order['order_number']} has been submitted and is awaiting review.",
                    'return_requested',
                    APP_URL . '/index.php?url=orders/' . $data['order_id']
                );
            } catch (Throwable $e) { /* silent */ }

            flash('success', 'Return request submitted successfully.');
        } else {
            flash('error', 'Failed to submit return request.');
        }

        header('Location: ' . APP_URL . '/index.php?url=orders/' . $data['order_id']);
        exit;
    }

    public function manage() {
        AuthMiddleware::adminOrStaff();
        $status    = $_GET['status'] ?? null;
        $returns   = $this->returnModel->getAll($status);
        $pageTitle = 'Manage Returns';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/staff/manage-returns.php';
    }

    public function updateStatus($id) {
        AuthMiddleware::adminOrStaff();
        AuthMiddleware::csrf();
        $status     = $_POST['status'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        $this->returnModel->updateStatus($id, $status, $adminNotes);
        $this->logModel->create(LOG_RETURN_UPDATE, "Return request #{$id} updated to: {$status}");

        $return = $this->returnModel->findById($id);

        // Notify customer about return status change
        if ($return) {
            try {
                $order = $this->orderModel->findById($return['order_id']);
                if ($order) {
                    $notifModel = new Notification($this->pdo);
                    $notifTitles = [
                        'approved'  => 'Return Approved',
                        'rejected'  => 'Return Rejected',
                        'completed' => 'Return Completed',
                    ];
                    $notifMessages = [
                        'approved'  => "Your return request for order #{$order['order_number']} has been approved.",
                        'rejected'  => "Your return request for order #{$order['order_number']} has been rejected." . ($adminNotes ? " Reason: {$adminNotes}" : ''),
                        'completed' => "Your return for order #{$order['order_number']} has been completed and a refund is being processed.",
                    ];
                    $notifTypes = [
                        'approved'  => 'return_approved',
                        'rejected'  => 'return_rejected',
                        'completed' => 'return_completed',
                    ];
                    if (isset($notifTitles[$status])) {
                        $notifModel->create(
                            $return['user_id'],
                            $notifTitles[$status],
                            $notifMessages[$status],
                            $notifTypes[$status],
                            APP_URL . '/index.php?url=orders/' . $return['order_id']
                        );
                    }
                }
            } catch (Throwable $e) { /* silent */ }
        }

        // When a return is APPROVED
        if ($status === 'approved' && $return) {
            $isDamaged = $this->isDamageReason($return['reason']);
            $orderItems = $this->orderItemModel->getByOrderId($return['order_id']);

            // Check once before the loop so every item in the order is processed
            $alreadyRecordedDamage = $isDamaged && $this->damagedModel->existsForReturn($id);

            foreach ($orderItems as $item) {
                if ($isDamaged && !$alreadyRecordedDamage) {
                    // Create damaged product record (inventory is NOT restored)
                    $this->damagedModel->create([
                        'product_id'        => $item['product_id'],
                        'order_id'          => $return['order_id'],
                        'return_request_id' => $id,
                        'quantity'           => $item['quantity'],
                        'reason'            => $return['reason'],
                        'reported_by'       => $_SESSION['user_id']
                    ]);

                    $this->logModel->create(LOG_STOCK_MOVEMENT,
                        "Damaged product recorded: {$item['product_name']} (qty: {$item['quantity']}) from Return #{$id} — not restored to inventory"
                    );
                } elseif (!$isDamaged) {
                    // Non-damaged returns: restore inventory
                    $this->inventoryModel->adjustQuantity($item['product_id'], $item['quantity']);
                    $this->stockMovementModel->record(
                        $item['product_id'],
                        'return',
                        $item['quantity'],
                        $id,
                        'Return approved (non-damaged) from return #' . $id,
                        $_SESSION['user_id']
                    );
                    $this->logModel->create(LOG_STOCK_MOVEMENT,
                        "Stock restored: {$item['product_name']} (qty: {$item['quantity']}) from Return #{$id}"
                    );
                }
            }
        }

        // When a return is marked COMPLETED → mark payment as refunded
        if ($status === 'completed' && $return) {
            $paymentModel = new Payment($this->pdo);
            $payment = $paymentModel->getByOrderId($return['order_id']);
            if ($payment) {
                $paymentModel->updateStatus($payment['id'], 'refunded');
                $this->logModel->create(LOG_RETURN_UPDATE,
                    "Payment for Order #{$return['order_id']} marked as refunded (Return #{$id})"
                );
            }
        }

        flash('success', 'Return request updated.');

        // Redirect based on role
        $roleId = $_SESSION['role_id'] ?? ROLE_STAFF;
        if ($roleId == ROLE_SUPER_ADMIN) {
            header('Location: ' . APP_URL . '/index.php?url=admin/returns');
        } else {
            header('Location: ' . APP_URL . '/index.php?url=staff/returns');
        }
        exit;
    }

    /**
     * Check if a return reason indicates item damage.
     */
    private function isDamageReason($reason) {
        $damageKeywords = [
            'damaged', 'broken', 'defective', 'not working',
            'cracked', 'torn', 'dented', 'scratched'
        ];
        $reasonLower = strtolower($reason);
        foreach ($damageKeywords as $keyword) {
            if (str_contains($reasonLower, $keyword)) {
                return true;
            }
        }
        return false;
    }
}
