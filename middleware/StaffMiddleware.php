<?php
/**
 * Staff Middleware
 */

class StaffMiddleware {
    public static function handle() {
        AuthMiddleware::handle();

        if ($_SESSION['role_id'] != ROLE_STAFF) {
            $_SESSION['flash_error'] = 'Access denied.';
            header('Location: ' . APP_URL . '/index.php?url=dashboard');
            exit;
        }
    }
}
