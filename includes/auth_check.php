<?php
/**
 * SouthDev Home Depot – Authentication & Authorization
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isCustomer() {
    return isLoggedIn() && $_SESSION['role_id'] == ROLE_CUSTOMER;
}

function isStaff() {
    return isLoggedIn() && $_SESSION['role_id'] == ROLE_STAFF;
}

function isSuperAdmin() {
    return isLoggedIn() && $_SESSION['role_id'] == ROLE_SUPER_ADMIN;
}

function isAdminOrStaff() {
    return isStaff() || isSuperAdmin();
}

function requireLogin() {
    if (!isLoggedIn()) {
        flash('error', 'Please log in to continue.');
        header('Location: ' . APP_URL . '/index.php?url=login');
        exit;
    }
}

function requireRole($roleId) {
    requireLogin();
    if ($_SESSION['role_id'] != $roleId) {
        http_response_code(403);
        include ROOT_PATH . '/views/errors/403.php';
        exit;
    }
}

function requireAdminOrStaff() {
    requireLogin();
    if (!isAdminOrStaff()) {
        http_response_code(403);
        include ROOT_PATH . '/views/errors/403.php';
        exit;
    }
}

/**
 * Verify CSRF token on POST requests
 */
function requireCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            flash('error', 'Invalid security token. Please try again.');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? APP_URL);
            exit;
        }
    }
}

/**
 * Get current user ID
 */
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role ID
 */
function currentRoleId() {
    return $_SESSION['role_id'] ?? null;
}
