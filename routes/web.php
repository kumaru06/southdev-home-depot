<?php
/**
 * SouthDev Home Depot – Web Routes
 */

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

switch ($urlParts[0]) {

    /* ================================================================
     * AUTH
     * ============================================================= */
    case 'login':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            AuthMiddleware::guest();
            $controller->showLogin();
        }
        break;

    case 'admin-login':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->adminLogin();
        } else {
            AuthMiddleware::guest();
            $controller->showAdminLogin();
        }
        break;

    case 'register':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->register();
        } else {
            AuthMiddleware::guest();
            $controller->showRegister();
        }
        break;

    case 'check-username':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        $controller->checkUsername();
        break;

    case 'logout':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        $controller->logout();
        break;

    case 'verify-email':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        if (isset($_GET['token'])) {
            $controller->verifyEmailLink();
        } else {
            $controller->showVerifyEmail();
        }
        break;

    case 'verify-otp':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        $controller->verifyOtp();
        break;

    case 'resend-verification':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        $controller->resendVerification();
        break;

    case 'forgot-password':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->sendPasswordReset();
        } else {
            AuthMiddleware::guest();
            $controller->showForgotPassword();
        }
        break;

    case 'reset-password':
        require_once CONTROLLERS_PATH . '/AuthController.php';
        $controller = new AuthController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->resetPassword();
        } else {
            AuthMiddleware::guest();
            $controller->showResetPassword();
        }
        break;

    /* ================================================================
     * DASHBOARD
     * ============================================================= */
    case 'dashboard':
        AuthMiddleware::handle();
        if ($_SESSION['role_id'] == ROLE_CUSTOMER) {
            // Customers redirect to products
            header('Location: ' . APP_URL . '/index.php?url=products');
            exit;
        } elseif ($_SESSION['role_id'] == ROLE_INVENTORY) {
            // Inventory in-charge gets redirected to inventory management
            require_once CONTROLLERS_PATH . '/DashboardController.php';
            $controller = new DashboardController($pdo);
            $controller->index();
        } else {
            require_once CONTROLLERS_PATH . '/DashboardController.php';
            $controller = new DashboardController($pdo);
            $controller->index();
        }
        break;

    /* ================================================================
     * PRODUCTS  (public browsing + search)
     * ============================================================= */
    case 'products':
        require_once CONTROLLERS_PATH . '/ProductController.php';
        $controller = new ProductController($pdo);
        if (isset($urlParts[1])) {
            if ($urlParts[1] === 'search') {
                $controller->search();
            } else {
                $controller->show($urlParts[1]);
            }
        } else {
            // Serve the alternate products layout when clicking the main Products link
            $controller->alt();
        }
        break;

    /* ================================================================
     * LOCATIONS
     * Simple public page showing store locations with an embedded map
     * ============================================================= */
    case 'locations':
        $pageTitle = 'Locations';
        require_once VIEWS_PATH . '/customer/locations.php';
        break;

    /* ================================================================
     * CART
     * ============================================================= */
    case 'cart':
        require_once CONTROLLERS_PATH . '/CartController.php';
        $controller = new CartController($pdo);
        if (isset($urlParts[1])) {
            switch ($urlParts[1]) {
                case 'add':    $controller->add();    break;
                case 'update': $controller->update(); break;
                case 'remove': $controller->remove(); break;
                default:       $controller->index();
            }
        } else {
            $controller->index();
        }
        break;

    /* ================================================================
     * CHECKOUT
     * ============================================================= */
    case 'checkout':
        require_once CONTROLLERS_PATH . '/CartController.php';
        $controller = new CartController($pdo);
        $controller->checkout();
        break;

    /* ================================================================
     * ORDERS  (customer-facing)
     * ============================================================= */
    case 'orders':
        require_once CONTROLLERS_PATH . '/OrderController.php';
        $controller = new OrderController($pdo);
        if (isset($urlParts[1])) {
            if ($urlParts[1] === 'create') {
                $controller->create();
            } elseif ($urlParts[1] === 'request-cancel' && isset($urlParts[2])) {
                $controller->requestCancel($urlParts[2]);
            } elseif (isset($urlParts[2])) {
                if ($urlParts[2] === 'cancel') {
                    $controller->cancel($urlParts[1]);
                } else {
                    $controller->show($urlParts[1]);
                }
            } else {
                $controller->show($urlParts[1]);
            }
        } else {
            $controller->index();
        }
        break;

    /* ================================================================
     * NOTIFICATIONS (customer-facing)
     * ============================================================= */
    case 'notifications':
        require_once CONTROLLERS_PATH . '/NotificationController.php';
        $controller = new NotificationController($pdo);
        if (isset($urlParts[1])) {
            if ($urlParts[1] === 'read' && isset($urlParts[2])) {
                $controller->read($urlParts[2]);
            } elseif ($urlParts[1] === 'mark-all-read') {
                $controller->markAllRead();
            } elseif ($urlParts[1] === 'api-unread') {
                $controller->apiUnread();
            }
        } else {
            $controller->index();
        }
        break;

    /* ================================================================
     * RETURNS  (customer-facing)
     * ============================================================= */
    case 'returns':
        require_once CONTROLLERS_PATH . '/ReturnController.php';
        $controller = new ReturnController($pdo);
        if (isset($urlParts[1])) {
            if ($urlParts[1] === 'request' && isset($urlParts[2])) {
                $controller->requestForm($urlParts[2]);
            } elseif ($urlParts[1] === 'submit') {
                $controller->submit();
            }
        }
        break;

    /* ================================================================
     * REVIEWS (customer reviews for delivered items)
     * ============================================================= */
    case 'reviews':
        require_once CONTROLLERS_PATH . '/ReviewController.php';
        $controller = new ReviewController($pdo);
        if (isset($urlParts[1]) && $urlParts[1] === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->submit();
        } else {
            require_once VIEWS_PATH . '/errors/404.php';
        }
        break;

    /* ================================================================
     * PAYMENT
     * ============================================================= */
    case 'payment':
        require_once CONTROLLERS_PATH . '/PaymentController.php';
        $controller = new PaymentController($pdo);
        $sub = $urlParts[1] ?? '';
        if ($sub === 'create-source') {
            $controller->createPayMongoSource();
        } elseif ($sub === 'create-intent') {
            $controller->createCardPaymentIntent();
        } elseif ($sub === 'attach-card') {
            $controller->attachCardPaymentMethod();
        } elseif ($sub === 'webhook') {
            $controller->handlePayMongoWebhook();
        } else {
            $controller->process();
        }
        break;

    /* ================================================================
     * PROFILE
     * ============================================================= */
    case 'profile':
        require_once CONTROLLERS_PATH . '/UserController.php';
        $controller = new UserController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateProfile();
        } else {
            $controller->profile();
        }
        break;

    case 'profile-send-email-otp':
        require_once CONTROLLERS_PATH . '/UserController.php';
        $controller = new UserController($pdo);
        $controller->sendEmailChangeOtp();
        break;

    case 'profile-verify-email-otp':
        require_once CONTROLLERS_PATH . '/UserController.php';
        $controller = new UserController($pdo);
        $controller->verifyEmailChangeOtp();
        break;

    case 'profile-send-new-email-otp':
        require_once CONTROLLERS_PATH . '/UserController.php';
        $controller = new UserController($pdo);
        $controller->sendNewEmailOtp();
        break;

    case 'profile-verify-new-email-otp':
        require_once CONTROLLERS_PATH . '/UserController.php';
        $controller = new UserController($pdo);
        $controller->verifyNewEmailOtp();
        break;

    /* ================================================================
     * INVENTORY ROLE ROUTES
     * ============================================================= */
    case 'inventory':
        if (isset($urlParts[1])) {
            switch ($urlParts[1]) {
                case 'stock':
                    require_once CONTROLLERS_PATH . '/InventoryController.php';
                    $controller = new InventoryController($pdo);
                    if (isset($urlParts[2]) && $urlParts[2] === 'update') {
                        $controller->update();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'add-stock') {
                        $controller->addStock();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'request-supplier') {
                        $controller->requestSupplier();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'movements') {
                        $controller->movements();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'price-history') {
                        $controller->priceHistory();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'damaged') {
                        if (isset($urlParts[3]) && isset($urlParts[4]) && $urlParts[4] === 'update') {
                            $controller->updateDamagedStatus($urlParts[3]);
                        } else {
                            $controller->damagedProducts();
                        }
                    } else {
                        $controller->index();
                    }
                    break;

                case 'reports':
                    require_once CONTROLLERS_PATH . '/ReportController.php';
                    $controller = new ReportController($pdo);
                    $controller->index();
                    break;

                default:
                    require_once VIEWS_PATH . '/errors/404.php';
            }
        } else {
            header('Location: ' . APP_URL . '/index.php?url=dashboard');
            exit;
        }
        break;

    /* ================================================================
     * STAFF ROUTES
     * ============================================================= */
    case 'staff':
        if (isset($urlParts[1])) {
            switch ($urlParts[1]) {
                case 'orders':
                    require_once CONTROLLERS_PATH . '/OrderController.php';
                    $controller = new OrderController($pdo);
                    if (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'status') {
                        $controller->updateStatus($urlParts[2]);
                    } elseif (isset($urlParts[2])) {
                        $controller->show($urlParts[2]);
                    } else {
                        $controller->manage();
                    }
                    break;

                case 'cancel-requests':
                    require_once CONTROLLERS_PATH . '/OrderController.php';
                    $controller = new OrderController($pdo);
                    if (isset($urlParts[2]) && isset($urlParts[3])) {
                        if ($urlParts[3] === 'approve') {
                            $controller->approveCancel($urlParts[2]);
                        } elseif ($urlParts[3] === 'reject') {
                            $controller->rejectCancel($urlParts[2]);
                        }
                    } else {
                        $controller->cancelRequests();
                    }
                    break;

                case 'inventory':
                    require_once CONTROLLERS_PATH . '/InventoryController.php';
                    $controller = new InventoryController($pdo);
                    if (isset($urlParts[2]) && $urlParts[2] === 'update') {
                        $controller->update();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'add-stock') {
                        $controller->addStock();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'request-supplier') {
                        $controller->requestSupplier();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'movements') {
                        $controller->movements();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'price-history') {
                        $controller->priceHistory();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'damaged') {
                        if (isset($urlParts[3]) && isset($urlParts[4]) && $urlParts[4] === 'update') {
                            $controller->updateDamagedStatus($urlParts[3]);
                        } else {
                            $controller->damagedProducts();
                        }
                    } else {
                        $controller->index();
                    }
                    break;

                case 'returns':
                    require_once CONTROLLERS_PATH . '/ReturnController.php';
                    $controller = new ReturnController($pdo);
                    if (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'update') {
                        $controller->updateStatus($urlParts[2]);
                    } else {
                        $controller->manage();
                    }
                    break;

                case 'reviews':
                    require_once CONTROLLERS_PATH . '/ReviewController.php';
                    $controller = new ReviewController($pdo);
                    if (isset($urlParts[2]) && $urlParts[2] === 'delete' && isset($urlParts[3])) {
                        $controller->delete($urlParts[3]);
                    } else {
                        $controller->adminIndex();
                    }
                    break;

                case 'reports':
                    require_once CONTROLLERS_PATH . '/ReportController.php';
                    $controller = new ReportController($pdo);
                    $controller->index();
                    break;

                default:
                    require_once VIEWS_PATH . '/errors/404.php';
            }
        } else {
            // staff root → dashboard
            header('Location: ' . APP_URL . '/index.php?url=dashboard');
            exit;
        }
        break;

    /* ================================================================
     * ADMIN (SUPER ADMIN) ROUTES
     * ============================================================= */
    case 'admin':
        if (isset($urlParts[1])) {
            switch ($urlParts[1]) {
                case 'users':
                    require_once CONTROLLERS_PATH . '/UserController.php';
                    $controller = new UserController($pdo);
                    if (isset($urlParts[2]) && $urlParts[2] === 'create') {
                        $controller->create();
                    } elseif (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'view') {
                        $controller->viewUser($urlParts[2]);
                    } elseif (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'toggle') {
                        $controller->toggleActive($urlParts[2]);
                    } elseif (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'delete') {
                        $controller->delete($urlParts[2]);
                    } elseif (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'update') {
                        $controller->update($urlParts[2]);
                    } else {
                        $controller->index();
                    }
                    break;

                case 'products':
                    require_once CONTROLLERS_PATH . '/ProductController.php';
                    $controller = new ProductController($pdo);
                    if (isset($urlParts[2]) && $urlParts[2] === 'create') {
                        $controller->create();
                    } elseif (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'edit') {
                        $controller->edit($urlParts[2]);
                    } elseif (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'update') {
                        $controller->update($urlParts[2]);
                    } elseif (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'delete') {
                        $controller->delete($urlParts[2]);
                    } else {
                        $controller->manage();
                    }
                    break;

                case 'categories':
                    require_once MODELS_PATH . '/Category.php';
                    require_once MODELS_PATH . '/Log.php';
                    $categoryModel = new Category($pdo);
                    $logModel = new Log($pdo);
                    if (isset($urlParts[2]) && $urlParts[2] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        AuthMiddleware::superAdmin();
                        AuthMiddleware::csrf();
                        $data = ['name' => trim($_POST['name']), 'description' => trim($_POST['description'] ?? '')];
                        $categoryModel->create($data);
                        $logModel->create(LOG_CATEGORY_CREATE, "Category created: {$data['name']}");
                        flash('success', 'Category created.');
                        header('Location: ' . APP_URL . '/index.php?url=admin/categories');
                        exit;
                    } elseif (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'delete') {
                        AuthMiddleware::superAdmin();
                        AuthMiddleware::csrf();
                        $categoryModel->delete($urlParts[2]);
                        $logModel->create(LOG_CATEGORY_DELETE, "Category #{$urlParts[2]} deleted");
                        flash('success', 'Category deleted.');
                        header('Location: ' . APP_URL . '/index.php?url=admin/categories');
                        exit;
                    } else {
                        AuthMiddleware::superAdmin();
                        $categories = $categoryModel->getAll();
                        $pageTitle = 'Manage Categories';
                        $isAdmin   = true;
                        $extraCss  = ['admin.css'];
                        require_once VIEWS_PATH . '/superadmin/manage-categories.php';
                    }
                    break;

                case 'cancel-requests':
                    require_once CONTROLLERS_PATH . '/OrderController.php';
                    $controller = new OrderController($pdo);
                    if (isset($urlParts[2]) && isset($urlParts[3])) {
                        if ($urlParts[3] === 'approve') {
                            $controller->approveCancel($urlParts[2]);
                        } elseif ($urlParts[3] === 'reject') {
                            $controller->rejectCancel($urlParts[2]);
                        }
                    } else {
                        $controller->cancelRequests();
                    }
                    break;

                case 'orders':
                    require_once CONTROLLERS_PATH . '/OrderController.php';
                    $controller = new OrderController($pdo);
                    if (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'status') {
                        $controller->updateStatus($urlParts[2]);
                    } elseif (isset($urlParts[2])) {
                        $controller->show($urlParts[2]);
                    } else {
                        $controller->manage();
                    }
                    break;

                case 'inventory':
                    require_once CONTROLLERS_PATH . '/InventoryController.php';
                    $controller = new InventoryController($pdo);
                    if (isset($urlParts[2]) && $urlParts[2] === 'update') {
                        $controller->update();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'add-stock') {
                        $controller->addStock();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'request-supplier') {
                        $controller->requestSupplier();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'movements') {
                        $controller->movements();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'price-history') {
                        $controller->priceHistory();
                    } elseif (isset($urlParts[2]) && $urlParts[2] === 'damaged') {
                        if (isset($urlParts[3]) && isset($urlParts[4]) && $urlParts[4] === 'update') {
                            $controller->updateDamagedStatus($urlParts[3]);
                        } else {
                            $controller->damagedProducts();
                        }
                    } else {
                        $controller->index();
                    }
                    break;

                case 'returns':
                    require_once CONTROLLERS_PATH . '/ReturnController.php';
                    $controller = new ReturnController($pdo);
                    if (isset($urlParts[2]) && isset($urlParts[3]) && $urlParts[3] === 'update') {
                        $controller->updateStatus($urlParts[2]);
                    } else {
                        $controller->manage();
                    }
                    break;

                case 'reviews':
                    require_once CONTROLLERS_PATH . '/ReviewController.php';
                    $controller = new ReviewController($pdo);
                    if (isset($urlParts[2]) && $urlParts[2] === 'delete' && isset($urlParts[3])) {
                        $controller->delete($urlParts[3]);
                    } else {
                        $controller->adminIndex();
                    }
                    break;

                case 'reports':
                    require_once CONTROLLERS_PATH . '/ReportController.php';
                    $controller = new ReportController($pdo);
                    $controller->index();
                    break;

                case 'logs':
                    require_once CONTROLLERS_PATH . '/LogController.php';
                    $controller = new LogController($pdo);
                    $controller->index();
                    break;

                case 'settings':
                    AuthMiddleware::superAdmin();
                    $pageTitle = 'System Settings';
                    $isAdmin   = true;
                    $extraCss  = ['admin.css'];
                    require_once VIEWS_PATH . '/superadmin/system-settings.php';
                    break;

                default:
                    require_once VIEWS_PATH . '/errors/404.php';
            }
        } else {
            header('Location: ' . APP_URL . '/index.php?url=dashboard');
            exit;
        }
        break;

    /* ================================================================
     * HOME / DEFAULT
     * ============================================================= */
    case '':
        // Redirect logged-in users to their appropriate page
        if (isLoggedIn()) {
            $roleId = $_SESSION['role_id'] ?? null;
            if ($roleId == ROLE_CUSTOMER) {
                header('Location: ' . APP_URL . '/index.php?url=products');
            } else {
                header('Location: ' . APP_URL . '/index.php?url=dashboard');
            }
            exit;
        }
        // Serve the public homepage for guests
        $pageTitle = 'Home';
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/dashboard.php';
        exit;
        break;

    default:
        require_once VIEWS_PATH . '/errors/404.php';
        break;
}
