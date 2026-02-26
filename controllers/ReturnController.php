<?php
/**
 * SouthDev Home Depot – Return Controller
 */

require_once __DIR__ . '/../models/ReturnRequest.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Log.php';

class ReturnController {
    private $returnModel;
    private $orderModel;
    private $logModel;

    public function __construct($pdo) {
        $this->returnModel = new ReturnRequest($pdo);
        $this->orderModel  = new Order($pdo);
        $this->logModel    = new Log($pdo);
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

        if ($this->returnModel->create($data)) {
            $this->logModel->create(LOG_RETURN_REQUEST, "Return request submitted for Order #{$data['order_id']}");
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
        flash('success', 'Return request updated.');
        header('Location: ' . APP_URL . '/index.php?url=staff/returns');
        exit;
    }
}
