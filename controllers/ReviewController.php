<?php
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Order.php';

class ReviewController {
    private $pdo;
    private $reviewModel;
    private $orderModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->reviewModel = new Review($pdo);
        $this->orderModel  = new Order($pdo);
    }

    // POST /index.php?url=reviews/submit
    public function submit() {
        AuthMiddleware::handle();
        AuthMiddleware::csrf();

        $userId = $_SESSION['user_id'];
        $productId = intval($_POST['product_id'] ?? 0);
        $orderId = intval($_POST['order_id'] ?? 0);
        $orderItemId = intval($_POST['order_item_id'] ?? 0) ?: null;
        $rating = intval($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($productId <= 0 || $rating < 1 || $rating > 5) {
            flash('error', 'Invalid review submission.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . ($orderId ?: 'orders'));
            exit;
        }

        // Verify order belongs to user and is delivered
        if ($orderId) {
            $order = $this->orderModel->findById($orderId);
            if (!$order || $order['user_id'] != $userId) {
                flash('error', 'Order not found.');
                header('Location: ' . APP_URL . '/index.php?url=orders');
                exit;
            }
            if ($order['status'] !== ORDER_DELIVERED) {
                flash('error', 'You can only review items from delivered orders.');
                header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
                exit;
            }
        }

        // Prevent duplicate reviews for same order item / product
        if ($this->reviewModel->hasExisting($userId, $productId, $orderItemId)) {
            flash('warning', 'You have already reviewed this item.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . ($orderId ?: 'orders'));
            exit;
        }

        $saved = $this->reviewModel->create([
            'product_id'    => $productId,
            'order_id'      => $orderId ?: null,
            'order_item_id' => $orderItemId ?: null,
            'user_id'       => $userId,
            'rating'        => $rating,
            'comment'       => $comment ?: null,
        ]);

        if ($saved) {
            flash('success', 'Thank you — your review was submitted.');
        } else {
            flash('error', 'Unable to save review. Please try again later.');
        }

        header('Location: ' . APP_URL . '/index.php?url=orders/' . ($orderId ?: 'orders'));
        exit;
    }

    // Admin / Staff: list reviews
    public function adminIndex() {
        AuthMiddleware::adminOrStaff();
        $pageTitle = 'Customer Reviews';
        $isAdmin = true;
        $reviews = $this->reviewModel->getAll(500);
        $extraCss = ['admin.css'];
        require_once VIEWS_PATH . '/staff/reviews.php';
    }

    // Admin / Staff: delete review
    public function delete($id) {
        AuthMiddleware::adminOrStaff();
        AuthMiddleware::csrf();
        $id = intval($id);
        if ($id <= 0) {
            flash('error', 'Invalid review id.');
            header('Location: ' . APP_URL . '/index.php?url=staff/reviews');
            exit;
        }
        if ($this->reviewModel->deleteById($id)) {
            flash('success', 'Review deleted.');
        } else {
            flash('error', 'Unable to delete review.');
        }
        header('Location: ' . APP_URL . '/index.php?url=staff/reviews');
        exit;
    }
}
