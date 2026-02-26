<?php
/**
 * Super Admin Middleware
 */

class SuperAdminMiddleware {
    public static function handle() {
        AuthMiddleware::handle();

        if ($_SESSION['role_id'] != ROLE_SUPER_ADMIN) {
            $_SESSION['flash_error'] = 'Access denied.';
            header('Location: ' . APP_URL . '/index.php?url=dashboard');
            exit;
        }
    }
}
