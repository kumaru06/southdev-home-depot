<?php
/**
 * SouthDev Home Depot – Authentication Middleware
 */

class AuthMiddleware {

    /**
     * Require authenticated user
     */
    public static function handle() {
        if (!isset($_SESSION['user_id'])) {
            flash('error', 'Please log in to continue.');
            header('Location: ' . APP_URL . '/index.php?url=login');
            exit;
        }
    }

    /**
     * Guest only (redirect logged-in users)
     */
    public static function guest() {
        if (isset($_SESSION['user_id'])) {
            $url = ((int)$_SESSION['role_id'] === ROLE_CUSTOMER) ? 'products' : 'dashboard';
            header('Location: ' . APP_URL . '/index.php?url=' . $url);
            exit;
        }
    }

    /**
     * Require admin or staff role
     */
    public static function adminOrStaff() {
        self::handle();
        if ((int)$_SESSION['role_id'] !== ROLE_STAFF && (int)$_SESSION['role_id'] !== ROLE_SUPER_ADMIN) {
            http_response_code(403);
            include ROOT_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Require admin, staff, or inventory role
     */
    public static function adminOrStaffOrInventory() {
        self::handle();
        if (!in_array((int)$_SESSION['role_id'], [ROLE_STAFF, ROLE_SUPER_ADMIN, ROLE_INVENTORY], true)) {
            http_response_code(403);
            include ROOT_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Require inventory in-charge role (or super admin)
     */
    public static function inventory() {
        self::handle();
        if ((int)$_SESSION['role_id'] !== ROLE_INVENTORY && (int)$_SESSION['role_id'] !== ROLE_SUPER_ADMIN) {
            http_response_code(403);
            include ROOT_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Require super admin role
     */
    public static function superAdmin() {
        self::handle();
        if ((int)$_SESSION['role_id'] !== ROLE_SUPER_ADMIN) {
            http_response_code(403);
            include ROOT_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Verify CSRF token on current request
     */
    public static function csrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Invalid security token. Please try again.');
                // Validate referer is same-origin to prevent open redirect
                $ref = $_SERVER['HTTP_REFERER'] ?? '';
                $appBase = rtrim(APP_URL, '/');
                if ($ref && strpos($ref, $appBase) === 0) {
                    header('Location: ' . $ref);
                } else {
                    header('Location: ' . APP_URL);
                }
                exit;
            }
        }
    }
}
