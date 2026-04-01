<?php
/**
 * Application Constants
 */

// User roles
define('ROLE_CUSTOMER', 1);
define('ROLE_STAFF', 2);
define('ROLE_SUPER_ADMIN', 3);
define('ROLE_INVENTORY', 4);

// Order statuses
define('ORDER_PENDING', 'pending');
define('ORDER_PROCESSING', 'processing');
define('ORDER_SHIPPED', 'shipped');
define('ORDER_DELIVERED', 'delivered');
define('ORDER_CANCELLED', 'cancelled');

// Payment statuses
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_COMPLETED', 'completed');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');

// Payment methods
define('PAYMENT_METHOD_COD', 'cod');
define('PAYMENT_METHOD_GCASH', 'gcash');
define('PAYMENT_METHOD_BANK', 'bank');
define('PAYMENT_METHOD_CARD', 'card');

// Return request statuses
define('RETURN_PENDING', 'pending');
define('RETURN_APPROVED', 'approved');
define('RETURN_REJECTED', 'rejected');
define('RETURN_COMPLETED', 'completed');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Log actions
define('LOG_LOGIN', 'user_login');
define('LOG_LOGOUT', 'user_logout');
define('LOG_ORDER_CREATE', 'order_created');
define('LOG_ORDER_CANCEL', 'order_cancelled');
define('LOG_ORDER_STATUS', 'order_status_updated');
define('LOG_STOCK_RESTORE', 'stock_restored');
define('LOG_RETURN_REQUEST', 'return_requested');
define('LOG_RETURN_UPDATE', 'return_updated');
define('LOG_CANCEL_REQUEST', 'cancel_requested');
define('LOG_CANCEL_APPROVE', 'cancel_approved');
define('LOG_CANCEL_REJECT', 'cancel_rejected');
define('LOG_PRODUCT_CREATE', 'product_created');
define('LOG_PRODUCT_UPDATE', 'product_updated');
define('LOG_PRODUCT_DELETE', 'product_deleted');
define('LOG_USER_CREATE', 'user_created');
define('LOG_USER_UPDATE', 'user_updated');
define('LOG_CATEGORY_CREATE', 'category_created');
define('LOG_CATEGORY_DELETE', 'category_deleted');
define('LOG_PAYMENT', 'payment_processed');
define('LOG_PAYMENT_FAIL', 'payment_failed');
define('LOG_PRICE_UPDATE', 'price_updated');
define('LOG_STOCK_ADD', 'stock_added');
define('LOG_STOCK_MOVEMENT', 'stock_movement');
define('LOG_SUPPLIER_REQUEST', 'supplier_request');
define('LOG_DAMAGED_PRODUCT', 'damaged_product');
