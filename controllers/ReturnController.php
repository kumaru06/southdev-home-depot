<?php
/**
 * SouthDev Home Depot – Return Controller
 */

require_once __DIR__ . '/../models/ReturnRequest.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../models/DamagedProduct.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../includes/Mailer.php';
require_once __DIR__ . '/../models/User.php';

class ReturnController {
    private $returnModel;
    private $orderModel;
    private $orderItemModel;
    private $damagedModel;
    private $inventoryModel;
    private $stockMovementModel;
    private $logModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo               = $pdo;
        $this->returnModel       = new ReturnRequest($pdo);
        $this->orderModel        = new Order($pdo);
        $this->orderItemModel    = new OrderItem($pdo);
        $this->damagedModel      = new DamagedProduct($pdo);
        $this->inventoryModel    = new Inventory($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
        $this->logModel          = new Log($pdo);
    }

    public function requestForm($orderId) {
        AuthMiddleware::handle();
        $order = $this->orderModel->findById($orderId);

        if (!$order || $order['user_id'] != $_SESSION['user_id'] || $order['status'] != 'delivered') {
            flash('error', 'This order is not eligible for a return request.');
            header('Location: ' . APP_URL . '/index.php?url=orders');
            exit;
        }

        if ($this->returnModel->hasExistingRequest($orderId)) {
            flash('warning', 'A return request already exists for this order.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $orderId);
            exit;
        }

        $orderItems = $this->orderItemModel->getByOrderId($orderId);
        $pageTitle = 'Request Return';
        $extraCss  = ['customer.css'];
        require_once VIEWS_PATH . '/customer/request-return.php';
    }

    public function submit() {
        AuthMiddleware::handle();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $data = [
            'order_id'       => intval($_POST['order_id']),
            'user_id'        => $_SESSION['user_id'],
            'reason'         => trim($_POST['reason'] ?? ''),
            'selected_items' => array_map('intval', $_POST['return_items'] ?? []),
            'proof_image'    => null,
        ];

        $reasonCategory = trim($_POST['reason_category'] ?? '');
        $proofRequired  = $this->requiresProofPhoto($reasonCategory, $data['reason']);

        // Validate: only delivered orders can have return requests
        $order = $this->orderModel->findById($data['order_id']);
        if (!$order || $order['user_id'] != $_SESSION['user_id'] || $order['status'] !== 'delivered') {
            flash('error', 'This order is not eligible for a return request.');
            header('Location: ' . APP_URL . '/index.php?url=orders');
            exit;
        }

        // Prevent duplicate return requests
        if ($this->returnModel->hasExistingRequest($data['order_id'])) {
            flash('warning', 'A return request already exists for this order.');
            header('Location: ' . APP_URL . '/index.php?url=orders/' . $data['order_id']);
            exit;
        }

        $orderItems = $this->orderItemModel->getByOrderId($data['order_id']);
        $validItemIds = array_map('intval', array_column($orderItems, 'id'));
        $data['selected_items'] = array_values(array_intersect($data['selected_items'], $validItemIds));

        if (empty($data['selected_items'])) {
            flash('error', 'Please select the product you want to return.');
            header('Location: ' . APP_URL . '/index.php?url=returns/request/' . $data['order_id']);
            exit;
        }

        if ($data['reason'] === '') {
            flash('error', 'Please select a reason for your return.');
            header('Location: ' . APP_URL . '/index.php?url=returns/request/' . $data['order_id']);
            exit;
        }

        $uploadResult = $this->handleProofUpload($_FILES['proof_image'] ?? null, (int) $_SESSION['user_id']);
        if ($uploadResult['error']) {
            flash('error', $uploadResult['error']);
            header('Location: ' . APP_URL . '/index.php?url=returns/request/' . $data['order_id']);
            exit;
        }
        $data['proof_image'] = $uploadResult['filename'];

        if ($proofRequired && empty($data['proof_image'])) {
            flash('error', 'Please upload a photo as proof for damaged or wrong-item returns.');
            header('Location: ' . APP_URL . '/index.php?url=returns/request/' . $data['order_id']);
            exit;
        }

        if ($this->returnModel->create($data)) {
            $this->logModel->create(LOG_RETURN_REQUEST, "Return request submitted for Order #{$data['order_id']}");

            // Notify customer: return request submitted
            try {
                $notifModel = new Notification($this->pdo);
                $notifModel->create(
                    $_SESSION['user_id'],
                    'Return Requested',
                    "Your return request for order #{$order['order_number']} has been submitted and is awaiting review.",
                    'return_requested',
                    APP_URL . '/index.php?url=orders/' . $data['order_id']
                );
            } catch (Throwable $e) { /* silent */ }

            flash('success', 'Return request submitted successfully.');
        } else {
            flash('error', 'Failed to submit return request.');
        }

        header('Location: ' . APP_URL . '/index.php?url=orders/' . $data['order_id']);
        exit;
    }

    public function manage() {
        AuthMiddleware::adminOrStaff();
        $status    = $_GET['status'] ?? null;
        $returns   = $this->returnModel->getAll($status);
        foreach ($returns as &$return) {
            $return['selected_items_summary'] = $this->returnModel->getSelectedItemsSummary($return);
        }
        unset($return);
        $pageTitle = 'Manage Returns';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        $returnBaseUrl = (($_SESSION['role_id'] ?? null) == ROLE_SUPER_ADMIN) ? 'admin/returns' : 'staff/returns';
        require_once VIEWS_PATH . '/staff/manage-returns.php';
    }

    public function details($id) {
        AuthMiddleware::adminOrStaff();

        $return = $this->returnModel->findById($id);
        if (!$return) {
            flash('error', 'Return request not found.');
            $roleId = $_SESSION['role_id'] ?? ROLE_STAFF;
            $baseUrl = ($roleId == ROLE_SUPER_ADMIN) ? 'admin/returns' : 'staff/returns';
            header('Location: ' . APP_URL . '/index.php?url=' . $baseUrl);
            exit;
        }

        $order = $this->orderModel->findById($return['order_id']);
        $orderItems = $this->orderItemModel->getByOrderId($return['order_id']);
        $selectedItemIds = $this->returnModel->getSelectedItemIds($return);

        $pageTitle = 'Return #' . $return['id'];
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        $returnBaseUrl = (($_SESSION['role_id'] ?? null) == ROLE_SUPER_ADMIN) ? 'admin/returns' : 'staff/returns';
        require_once VIEWS_PATH . '/staff/return-details.php';
    }

    public function updateStatus($id) {
        AuthMiddleware::adminOrStaff();
        AuthMiddleware::csrf();
        $status     = $_POST['status'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        $this->returnModel->updateStatus($id, $status, $adminNotes);
        $this->logModel->create(LOG_RETURN_UPDATE, "Return request #{$id} updated to: {$status}");

        $return = $this->returnModel->findById($id);

        // Notify customer about return status change
        if ($return) {
            try {
                $order = $this->orderModel->findById($return['order_id']);
                if ($order) {
                    $notifModel = new Notification($this->pdo);
                    $notifTitles = [
                        'approved'  => 'Return Approved',
                        'rejected'  => 'Return Rejected',
                        'completed' => 'Return Completed',
                    ];
                    $notifMessages = [
                        'approved'  => "Your return request for order #{$order['order_number']} has been approved.",
                        'rejected'  => "Your return request for order #{$order['order_number']} has been rejected." . ($adminNotes ? " Reason: {$adminNotes}" : ''),
                        'completed' => "Your return for order #{$order['order_number']} has been completed and a refund is being processed.",
                    ];
                    $notifTypes = [
                        'approved'  => 'return_approved',
                        'rejected'  => 'return_rejected',
                        'completed' => 'return_completed',
                    ];
                    if (isset($notifTitles[$status])) {
                        $notifModel->create(
                            $return['user_id'],
                            $notifTitles[$status],
                            $notifMessages[$status],
                            $notifTypes[$status],
                            APP_URL . '/index.php?url=orders/' . $return['order_id']
                        );
                    }

                    // Send return status email
                    $emailStatuses = ['approved', 'rejected', 'completed'];
                    if (in_array($status, $emailStatuses)) {
                        $userModel = new User($this->pdo);
                        $user = $userModel->findById($return['user_id']);
                        $customerEmail = $user['email'] ?? '';

                        if ($customerEmail) {
                            $statusMeta = [
                                'approved' => [
                                    'label'   => 'Return Approved',
                                    'color'   => '#16A34A',
                                    'icon'    => '&#10003;',
                                    'message' => 'Good news! Your return request for order #' . $order['order_number'] . ' has been approved by our team.',
                                    'next'    => 'Please prepare the item(s) for return. Our team will reach out with further instructions on how to send the items back.',
                                ],
                                'rejected' => [
                                    'label'   => 'Return Rejected',
                                    'color'   => '#DC2626',
                                    'icon'    => '&#10007;',
                                    'message' => 'Unfortunately, your return request for order #' . $order['order_number'] . ' could not be approved.',
                                    'next'    => $adminNotes ? 'Reason: ' . htmlspecialchars($adminNotes) . ' If you think this is a mistake, please contact us.' : 'If you believe this is a mistake or need further assistance, please contact our support team.',
                                ],
                                'completed' => [
                                    'label'   => 'Return Completed',
                                    'color'   => '#3B82F6',
                                    'icon'    => '&#9679;',
                                    'message' => 'Your return for order #' . $order['order_number'] . ' has been completed. A refund is now being processed.',
                                    'next'    => 'Your refund will be credited back to your original payment method within 3-7 business days depending on your bank or payment provider.',
                                ],
                            ];

                            $meta = $statusMeta[$status];
                            $customerName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                            $orderUrl = APP_URL . '/index.php?url=orders/' . $return['order_id'];

                            // Admin notes row (only shown if notes exist)
                            $adminNotesRow = '';
                            if ($adminNotes) {
                                $adminNotesRow = '<tr><td style="color:#6B7280;font-size:13px;padding:4px 0;">Staff Notes</td><td align="right" style="color:#1C1C1C;font-weight:600;font-size:13px;padding:4px 0;">' . htmlspecialchars($adminNotes) . '</td></tr>';
                            }

                            $templatePath = ROOT_PATH . '/templates/email/return-status.html';
                            $html = file_get_contents($templatePath);
                            $html = str_replace('STATUS_BG_COLOR',   $meta['color'],                                      $html);
                            $html = str_replace('STATUS_ICON',        $meta['icon'],                                       $html);
                            $html = str_replace('{{app_name}}',       APP_NAME,                                            $html);
                            $html = str_replace('{{customer_name}}',  htmlspecialchars($customerName),                     $html);
                            $html = str_replace('{{order_number}}',   htmlspecialchars($order['order_number']),            $html);
                            $html = str_replace('{{return_reason}}',  htmlspecialchars($return['reason'] ?? 'N/A'),        $html);
                            $html = str_replace('{{status_label}}',   $meta['label'],                                      $html);
                            $html = str_replace('{{status_message}}', $meta['message'],                                    $html);
                            $html = str_replace('{{next_step}}',      $meta['next'],                                       $html);
                            $html = str_replace('{{admin_notes_row}}', $adminNotesRow,                                     $html);
                            $html = str_replace('{{order_url}}',      $orderUrl,                                           $html);
                            $html = str_replace('{{receipt_email}}',  htmlspecialchars($customerEmail),                    $html);

                            $subject = $meta['icon'] . ' Return ' . $meta['label'] . ' - Order #' . $order['order_number'] . ' | ' . APP_NAME;
                            (new Mailer())->send($customerEmail, $customerName, $subject, $html);
                        }
                    }
                }
            } catch (Throwable $e) { /* silent */ }
        }

        // When a return is APPROVED
        if ($status === 'approved' && $return) {
            $isDamaged = $this->isDamageReason($return['reason']);
            $orderItems = $this->orderItemModel->getByOrderId($return['order_id']);
            $selectedItemIds = $this->returnModel->getSelectedItemIds($return);

            if (!empty($selectedItemIds)) {
                $orderItems = array_values(array_filter($orderItems, function ($item) use ($selectedItemIds) {
                    return in_array((int) $item['id'], $selectedItemIds, true);
                }));
            }

            // Check once before the loop so every item in the order is processed
            $alreadyRecordedDamage = $isDamaged && $this->damagedModel->existsForReturn($id);

            foreach ($orderItems as $item) {
                if ($isDamaged && !$alreadyRecordedDamage) {
                    // Create damaged product record (inventory is NOT restored)
                    $this->damagedModel->create([
                        'product_id'        => $item['product_id'],
                        'order_id'          => $return['order_id'],
                        'return_request_id' => $id,
                        'quantity'           => $item['quantity'],
                        'reason'            => $return['reason'],
                        'reported_by'       => $_SESSION['user_id']
                    ]);

                    $this->logModel->create(LOG_STOCK_MOVEMENT,
                        "Damaged product recorded: {$item['product_name']} (qty: {$item['quantity']}) from Return #{$id} — not restored to inventory"
                    );
                } elseif (!$isDamaged) {
                    // Non-damaged returns: restore inventory
                    $this->inventoryModel->adjustQuantity($item['product_id'], $item['quantity']);
                    $this->stockMovementModel->record(
                        $item['product_id'],
                        'return',
                        $item['quantity'],
                        $id,
                        'Return approved (non-damaged) from return #' . $id,
                        $_SESSION['user_id']
                    );
                    $this->logModel->create(LOG_STOCK_MOVEMENT,
                        "Stock restored: {$item['product_name']} (qty: {$item['quantity']}) from Return #{$id}"
                    );
                }
            }
        }

        // When a return is marked COMPLETED → mark payment as refunded
        if ($status === 'completed' && $return) {
            $paymentModel = new Payment($this->pdo);
            $payment = $paymentModel->getByOrderId($return['order_id']);
            if ($payment) {
                $paymentModel->updateStatus($payment['id'], 'refunded');
                $this->logModel->create(LOG_RETURN_UPDATE,
                    "Payment for Order #{$return['order_id']} marked as refunded (Return #{$id})"
                );
            }
        }

        flash('success', 'Return request updated.');

        // Redirect based on role
        $roleId = $_SESSION['role_id'] ?? ROLE_STAFF;
        if ($roleId == ROLE_SUPER_ADMIN) {
            header('Location: ' . APP_URL . '/index.php?url=admin/returns');
        } else {
            header('Location: ' . APP_URL . '/index.php?url=staff/returns');
        }
        exit;
    }

    /**
     * Check if a return reason indicates item damage.
     */
    private function isDamageReason($reason) {
        $damageKeywords = [
            'damaged', 'broken', 'defective', 'not working',
            'cracked', 'torn', 'dented', 'scratched'
        ];
        $reasonLower = strtolower($reason);
        foreach ($damageKeywords as $keyword) {
            if (str_contains($reasonLower, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Damage / wrong-item returns require photo proof.
     */
    private function requiresProofPhoto($reasonCategory, $reasonText = '') {
        $proofCategories = [
            'Item arrived damaged or broken',
            'Received wrong item',
        ];
        if (in_array($reasonCategory, $proofCategories, true)) {
            return true;
        }

        $haystack = strtolower(trim($reasonCategory . ' ' . $reasonText));
        $keywords = ['damaged', 'broken', 'wrong item', 'received wrong'];
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle optional/required proof photo upload for return requests.
     * @return array{filename:?string,error:?string}
     */
    private function handleProofUpload($file, $userId) {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['filename' => null, 'error' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['filename' => null, 'error' => 'Photo upload failed. Please try again.'];
        }

        $maxBytes = 5 * 1024 * 1024;
        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            return ['filename' => null, 'error' => 'Proof photo is too large. Maximum allowed size is 5 MB.'];
        }

        $tmpName = $file['tmp_name'] ?? '';
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return ['filename' => null, 'error' => 'Invalid proof photo upload.'];
        }

        $imageInfo = @getimagesize($tmpName);
        $allowed = [
            'image/jpeg' => '.jpg',
            'image/png'  => '.png',
            'image/webp' => '.webp',
            'image/gif'  => '.gif',
        ];
        $mime = $imageInfo['mime'] ?? '';
        if (!$imageInfo || !isset($allowed[$mime])) {
            return ['filename' => null, 'error' => 'Proof photo must be a JPG, PNG, WebP, or GIF image.'];
        }

        $uploadDir = UPLOADS_PATH . '/returns';
        if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true)) {
            return ['filename' => null, 'error' => 'Could not prepare upload folder. Please try again.'];
        }

        $fileName = 'proof_u' . (int) $userId . '_' . time() . '_' . uniqid() . $allowed[$mime];
        $targetPath = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $targetPath)) {
            return ['filename' => null, 'error' => 'Failed to save the proof photo. Please try again.'];
        }

        return ['filename' => 'returns/' . $fileName, 'error' => null];
    }
}
