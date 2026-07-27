<?php
/**
 * SouthDev Home Depot – System Settings Controller (SuperAdmin)
 */

require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Log.php';

class SettingsController {
    private $pdo;
    private $settings;
    private $logModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->settings = new Setting($pdo);
        $this->settings->boot();
        $this->logModel = new Log($pdo);
    }

    public function index() {
        AuthMiddleware::superAdmin();

        $general  = $this->settings->getGeneral();
        $payments = [];
        foreach (array_keys(Setting::defaultPaymentFlags()) as $method) {
            $payments[$method] = $this->settings->isPaymentEnabled($method);
        }

        $pageTitle = 'System Settings';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/superadmin/system-settings.php';
    }

    public function updateGeneral() {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?url=admin/settings');
            exit;
        }

        $perPage = max(5, min(100, (int) ($_POST['items_per_page'] ?? 12) ?: 12));

        $this->settings->set('items_per_page', (string) $perPage);

        $this->logModel->create(
            'settings_updated',
            'Updated items per page to ' . $perPage . '.'
        );

        flash('success', 'Items per page saved successfully.');
        header('Location: ' . APP_URL . '/index.php?url=admin/settings');
        exit;
    }

    public function updatePayment() {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?url=admin/settings');
            exit;
        }

        $methods = array_keys(Setting::defaultPaymentFlags());
        $enabled = [];

        foreach ($methods as $method) {
            $on = isset($_POST[$method . '_enabled']) && (string) $_POST[$method . '_enabled'] === '1';
            $this->settings->set('payment_' . $method . '_enabled', $on ? '1' : '0');
            if ($on) {
                $enabled[] = strtoupper($method);
            }
        }

        if (empty($enabled)) {
            // Keep at least COD so checkout never has zero options
            $this->settings->set('payment_cod_enabled', '1');
            flash('error', 'At least one payment method is required. Cash on Delivery was kept enabled.');
            header('Location: ' . APP_URL . '/index.php?url=admin/settings');
            exit;
        }

        $this->logModel->create(
            'settings_updated',
            'Updated payment methods: ' . implode(', ', $enabled)
        );

        flash('success', 'Payment settings saved successfully.');
        header('Location: ' . APP_URL . '/index.php?url=admin/settings');
        exit;
    }
}
