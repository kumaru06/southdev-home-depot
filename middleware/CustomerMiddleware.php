<?php
/**
 * Customer Middleware
 */

class CustomerMiddleware {
    public static function handle() {
        AuthMiddleware::handle();

        if ($_SESSION['role_id'] != ROLE_CUSTOMER) {
            $_SESSION['flash_error'] = 'Access denied.';
            header('Location: ' . APP_URL . '/index.php?url=dashboard');
            exit;
        }
    }
}
