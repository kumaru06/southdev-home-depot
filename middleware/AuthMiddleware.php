<?php
/**
 * SouthDev Home Depot – Authentication Middleware
 */

class AuthMiddleware {

    private static function getLoginUrlFromSessionRole() {
        $roleId = (int) ($_SESSION['role_id'] ?? ROLE_CUSTOMER);
        return APP_URL . '/index.php?url=' . ($roleId === ROLE_CUSTOMER ? 'login' : 'admin-login');
    }

    private static function clearSessionAndRedirect($message) {
        $redirectUrl = self::getLoginUrlFromSessionRole();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        session_start();

        flash('error', $message);
        header('Location: ' . $redirectUrl);
        exit;
    }

    private static function getCurrentSessionUser() {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        global $pdo;
        if (!$pdo) {
            return null;
        }

        require_once MODELS_PATH . '/User.php';
        $userModel = new User($pdo);
        $user = $userModel->findById((int) $_SESSION['user_id']);

        if (!$user || empty($user['is_active'])) {
            return false;
        }

        return $user;
    }

    /**
     * Require authenticated user
     */
    public static function handle() {
        if (!isset($_SESSION['user_id'])) {
            flash('error', 'Please log in to continue.');
            header('Location: ' . APP_URL . '/index.php?url=login');
            exit;
        }

        $user = self::getCurrentSessionUser();
        if ($user === false) {
            self::clearSessionAndRedirect('This account is no longer available. Please log in again.');
        }

        if (is_array($user)) {
            $_SESSION['role_id'] = (int) ($user['role_id'] ?? ($_SESSION['role_id'] ?? ROLE_CUSTOMER));
            $_SESSION['first_name'] = $user['first_name'] ?? ($_SESSION['first_name'] ?? '');
            $_SESSION['last_name'] = $user['last_name'] ?? ($_SESSION['last_name'] ?? '');
            $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $_SESSION['profile_image'] = $user['profile_image'] ?? null;
        }
    }

    /**
     * Guest only (redirect logged-in users)
     */
    public static function guest() {
        if (isset($_SESSION['user_id'])) {
            $user = self::getCurrentSessionUser();
            if ($user === false) {
                self::clearSessionAndRedirect('Your previous session is no longer valid. Please log in again.');
            }

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
