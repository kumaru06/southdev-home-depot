<?php
/**
 * SouthDev Home Depot – User Controller
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Log.php';

class UserController {
    private $userModel;
    private $logModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->logModel  = new Log($pdo);
    }

    public function index() {
        AuthMiddleware::superAdmin();
        $users     = $this->userModel->getAll();
        $pageTitle = 'Manage Users';
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/superadmin/manage-users.php';
    }

    public function viewUser($id) {
        AuthMiddleware::superAdmin();
        $user = $this->userModel->findById($id);
        if (!$user) {
            flash('error', 'User not found.');
            header('Location: ' . APP_URL . '/index.php?url=admin/users');
            exit;
        }

        // Get user orders
        require_once MODELS_PATH . '/Order.php';
        $orderModel = new Order($this->pdo);
        $orders = $orderModel->getByUserId($user['id']);

        // Get user reviews
        require_once MODELS_PATH . '/Review.php';
        $reviewModel = new Review($this->pdo);
        $reviews = $reviewModel->getByUserId($user['id']);

        $pageTitle = 'View User — ' . $user['first_name'] . ' ' . $user['last_name'];
        $isAdmin   = true;
        $extraCss  = ['admin.css'];
        require_once VIEWS_PATH . '/superadmin/view-user.php';
    }

    public function create() {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $username = strtolower(trim($_POST['username'] ?? ''));
        $generatedEmail = $username . '@staff.local';

        $data = [
            'role_id'    => intval($_POST['role_id']),
            'first_name' => trim($_POST['first_name']),
            'last_name'  => trim($_POST['last_name']),
            'username'   => $username,
            'email'      => $generatedEmail,
            'password'   => $_POST['password'],
            'phone'      => trim($_POST['phone'] ?? '')
        ];

        if (empty($data['first_name']) || empty($data['last_name']) || empty($username) || empty($data['password'])) {
            flash('error', 'First name, last name, username, and password are required.');
        } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            flash('error', 'Username may only contain letters, numbers, underscore, dash, or dot.');
        } elseif (strlen($username) < 3 || strlen($username) > 30) {
            flash('error', 'Username must be between 3 and 30 characters.');
        } elseif (validate_password($data['password'])) {
            flash('error', validate_password($data['password']));
        } elseif ($this->userModel->findByUsername($username)) {
            flash('error', 'Username already taken.');
        } elseif ($this->userModel->findByEmail($generatedEmail)) {
            flash('error', 'Username already taken.');
        } elseif ($this->userModel->create($data)) {
            // If super admin created a staff or inventory account, mark email verified
            // so the account can sign in immediately without email verification.
            $newId = (int)$this->pdo->lastInsertId();
            if (in_array($data['role_id'], [2, 4], true) && $newId > 0) {
                $this->userModel->markEmailVerified($newId);
            }

            $this->logModel->create(LOG_USER_CREATE, "User created: {$username} (Role ID: {$data['role_id']})");
            flash('success', 'User created successfully.');
        } else {
            flash('error', 'Failed to create user.');
        }

        header('Location: ' . APP_URL . '/index.php?url=admin/users');
        exit;
    }

    public function update($id) {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $data = [
            'first_name' => trim($_POST['first_name']),
            'last_name'  => trim($_POST['last_name']),
            'email'      => trim($_POST['email']),
            'phone'      => trim($_POST['phone'] ?? ''),
            'address'    => trim($_POST['address'] ?? ''),
            'city'       => trim($_POST['city'] ?? ''),
            'state'      => trim($_POST['state'] ?? ''),
            'zip_code'   => trim($_POST['zip_code'] ?? '')
        ];

        $this->userModel->update($id, $data);
        $this->logModel->create(LOG_USER_UPDATE, "User updated: {$data['email']} (ID #{$id})");
        flash('success', 'User updated successfully.');
        header('Location: ' . APP_URL . '/index.php?url=admin/users');
        exit;
    }

    public function toggleActive($id) {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        $this->userModel->toggleActive($id);
        $this->logModel->create(LOG_USER_UPDATE, "User #{$id} active status toggled");
        flash('success', 'User status updated.');
        header('Location: ' . APP_URL . '/index.php?url=admin/users');
        exit;
    }

    public function delete($id) {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('error', 'Invalid request.');
            header('Location: ' . APP_URL . '/index.php?url=admin/users');
            exit;
        }

        $id = (int)$id;
        if ($id <= 0) {
            flash('error', 'User not found.');
            header('Location: ' . APP_URL . '/index.php?url=admin/users');
            exit;
        }

        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            flash('error', 'You cannot delete your own account.');
            header('Location: ' . APP_URL . '/index.php?url=admin/users');
            exit;
        }

        if ($this->userModel->hasOrders($id)) {
            flash('error', 'Cannot delete a user with existing orders. Please deactivate instead.');
            header('Location: ' . APP_URL . '/index.php?url=admin/users');
            exit;
        }

        if ($this->userModel->deleteUser($id)) {
            $this->logModel->create(LOG_USER_UPDATE, "User #{$id} deleted");
            flash('success', 'User deleted successfully.');
        } else {
            flash('error', 'Failed to delete user.');
        }

        header('Location: ' . APP_URL . '/index.php?url=admin/users');
        exit;
    }

    public function profile() {
        AuthMiddleware::handle();
        $user      = $this->userModel->findById($_SESSION['user_id']);
        $usernameEnabled = $this->userModel->hasColumn('username');
        $pageTitle = 'My Profile';
        if (($_SESSION['role_id'] ?? ROLE_CUSTOMER) == ROLE_CUSTOMER) {
            $extraCss  = ['customer.css'];
            require_once VIEWS_PATH . '/customer/profile.php';
        } else {
            $isAdmin = true;
            $extraCss  = ['admin.css'];
            require_once VIEWS_PATH . '/staff/profile.php';
        }
    }

    public function updateProfile() {
        AuthMiddleware::handle();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        // Password change (separate form on profile page)
        if (($_POST['action'] ?? '') === 'change_password') {
            $returnUrl = trim((string)($_POST['return_url'] ?? ''));
            if ($returnUrl === '') {
                $returnUrl = 'profile';
            }
            // Safety: allow only simple internal routes (no protocol, no slashes)
            if (!preg_match('/^[a-z0-9\-\/]+$/i', $returnUrl) || str_starts_with($returnUrl, '/') || str_contains($returnUrl, '..')) {
                $returnUrl = 'profile';
            }

            $currentPassword = (string)($_POST['current_password'] ?? '');
            $newPassword = (string)($_POST['new_password'] ?? '');
            $confirmPassword = (string)($_POST['confirm_password'] ?? '');

            if (trim($currentPassword) === '' || trim($newPassword) === '' || trim($confirmPassword) === '') {
                flash('error', 'Please fill out all password fields.');
                header('Location: ' . APP_URL . '/index.php?url=' . $returnUrl);
                exit;
            }

            $pwError = validate_password($newPassword);
            if ($pwError) {
                flash('error', $pwError);
                header('Location: ' . APP_URL . '/index.php?url=' . $returnUrl);
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                flash('error', 'New password and confirmation do not match.');
                header('Location: ' . APP_URL . '/index.php?url=' . $returnUrl);
                exit;
            }

            $user = $this->userModel->findById($_SESSION['user_id']);
            $storedHash = $user['password'] ?? '';

            if (!$user || !is_string($storedHash) || $storedHash === '' || !password_verify($currentPassword, $storedHash)) {
                flash('error', 'Current password is incorrect, try again.');
                header('Location: ' . APP_URL . '/index.php?url=' . $returnUrl);
                exit;
            }

            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->userModel->updatePassword($_SESSION['user_id'], $hash);
            $this->logModel->create(LOG_USER_UPDATE, 'Password changed for user #' . (int)$_SESSION['user_id']);

            flash('success', 'Password updated successfully.');
            header('Location: ' . APP_URL . '/index.php?url=' . $returnUrl);
            exit;
        }

        // Non-customers: this endpoint is used only for password change.
        if (($_SESSION['role_id'] ?? ROLE_CUSTOMER) != ROLE_CUSTOMER) {
            flash('error', 'Action not allowed.');
            header('Location: ' . APP_URL . '/index.php?url=profile');
            exit;
        }

        $currentUser = $this->userModel->findById($_SESSION['user_id']);
        if (!$currentUser) {
            flash('error', 'User not found.');
            header('Location: ' . APP_URL . '/index.php?url=profile');
            exit;
        }

        $data = [
            'first_name' => trim($_POST['first_name']),
            'last_name'  => trim($_POST['last_name']),
            'email'      => trim((string) ($currentUser['email'] ?? '')),
            'phone'      => trim($_POST['phone'] ?? ''),
            'address'    => trim($_POST['address'] ?? ''),
            'city'       => trim($_POST['city'] ?? ''),
            'state'      => trim($_POST['state'] ?? ''),
            'zip_code'   => trim($_POST['zip_code'] ?? '')
        ];

        if ($data['first_name'] === '' || $data['last_name'] === '') {
            flash('error', 'First name and last name are required.');
            header('Location: ' . APP_URL . '/index.php?url=profile');
            exit;
        }

        // Include username if supported and provided
        if ($this->userModel->hasColumn('username') && isset($_POST['username'])) {
            $username = trim($_POST['username']);
            if ($username !== '') {
                if (strlen($username) < 3 || strlen($username) > 30) {
                    flash('error', 'Username must be between 3 and 30 characters.');
                    header('Location: ' . APP_URL . '/index.php?url=profile');
                    exit;
                }
                if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
                    flash('error', 'Username can only contain letters, numbers, underscores, dashes and dots.');
                    header('Location: ' . APP_URL . '/index.php?url=profile');
                    exit;
                }
                // Check uniqueness (exclude current user)
                $existing = $this->userModel->findByUsername($username);
                if ($existing && (int)$existing['id'] !== (int)$_SESSION['user_id']) {
                    flash('error', 'Username is already taken. Please choose another.');
                    header('Location: ' . APP_URL . '/index.php?url=profile');
                    exit;
                }
            }
            $data['username'] = $username ?: null;
        }

        $this->userModel->update($_SESSION['user_id'], $data);

        // Optional: profile image upload
        if (isset($_FILES['profile_image']) && is_array($_FILES['profile_image']) && ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $fileError = $_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($fileError === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['profile_image']['tmp_name'] ?? '';
                $fileSize = (int)($_FILES['profile_image']['size'] ?? 0);

                if ($fileSize > 0 && $fileSize <= (2 * 1024 * 1024) && is_uploaded_file($tmpName)) {
                    $imageInfo = @getimagesize($tmpName);
                    $mime = $imageInfo['mime'] ?? '';
                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (isset($allowed[$mime])) {
                        $ext = $allowed[$mime];
                        $filename = 'u' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;

                        $uploadDir = ROOT_PATH . '/assets/uploads/profiles';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0755, true);
                        }

                        $dest = $uploadDir . '/' . $filename;
                        if (@move_uploaded_file($tmpName, $dest)) {
                            $this->userModel->updateProfileImage($_SESSION['user_id'], $filename);
                            $_SESSION['profile_image'] = $filename;
                        } else {
                            flash('warning', 'Profile updated, but the photo upload failed.');
                        }
                    } else {
                        flash('warning', 'Profile updated, but the selected file is not a supported image (JPG/PNG/WebP).');
                    }
                } else {
                    flash('warning', 'Profile updated, but the selected image is too large (max 2MB).');
                }
            } else {
                flash('warning', 'Profile updated, but the photo upload could not be processed.');
            }
        }

        $_SESSION['first_name'] = $data['first_name'];
        $_SESSION['last_name']  = $data['last_name'];
        $_SESSION['user_name']  = $data['first_name'] . ' ' . $data['last_name'];

        flash('success', 'Profile updated.');
        header('Location: ' . APP_URL . '/index.php?url=profile');
        exit;
    }

    // --- Email change via two-step OTP flow ---
    // Step A: send OTP to CURRENT email to authorize change
    public function sendEmailChangeOtp() {
        AuthMiddleware::handle();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        if (!verify_csrf($data['csrf_token'] ?? null)) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid CSRF']); exit;
        }

        // No new email required for authorization step. Send OTP to the user's current email.
        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!$user) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'User not found']); exit; }

        try { $otp = (string)random_int(100000, 999999); } catch (Exception $e) { $otp = (string)mt_rand(100000,999999); }
        $expiry = time() + (defined('OTP_EXPIRY_MINUTES') ? OTP_EXPIRY_MINUTES * 60 : 300);

        // Store authorize OTP separately from the new-email OTP
        $_SESSION['email_change_authorize'] = [
            'hash' => password_hash($otp, PASSWORD_DEFAULT),
            'expires' => $expiry,
            'attempts' => 0
        ];

        require_once INCLUDES_PATH . '/Mailer.php';
        $mailer = new Mailer();
        $subject = 'Authorize email change';
        $html = "<p>Hi " . htmlspecialchars($user['first_name'] ?? '') . ",</p>" .
                "<p>Use the following OTP to authorize changing your account email (this confirms it is you). This does not change your email yet. The code expires in " . (defined('OTP_EXPIRY_MINUTES') ? OTP_EXPIRY_MINUTES : 5) . " minutes:</p>" .
                "<h2>" . htmlspecialchars($otp) . "</h2>";

        $sent = false;
        try { $sent = $mailer->send($user['email'], ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''), $subject, $html); } catch (Exception $e) { $sent = false; }

        header('Content-Type: application/json'); echo json_encode(['success' => true, 'emailed' => (bool)$sent]); exit;
    }

    // Step A verify: check OTP sent to current email and mark session authorized
    public function verifyEmailChangeOtp() {
        AuthMiddleware::handle();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        if (!verify_csrf($data['csrf_token'] ?? null)) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid CSRF']); exit;
        }

        $otp = trim((string)($data['otp'] ?? ''));
        if ($otp === '') { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Missing OTP']); exit; }

        if (empty($_SESSION['email_change_authorize']) || !is_array($_SESSION['email_change_authorize'])) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'No pending authorization']); exit;
        }

        $pending = &$_SESSION['email_change_authorize'];
        if (time() > ($pending['expires'] ?? 0)) { unset($_SESSION['email_change_authorize']); header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'OTP expired']); exit; }

        $pending['attempts'] = (int)($pending['attempts'] ?? 0) + 1;
        if ($pending['attempts'] > (defined('OTP_MAX_ATTEMPTS') ? OTP_MAX_ATTEMPTS : 5)) { unset($_SESSION['email_change_authorize']); header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Too many attempts']); exit; }

        if (!password_verify($otp, $pending['hash'])) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid OTP']); exit; }

        // Mark session as authorized to proceed with new-email step.
        $authorizedExpiry = time() + (defined('OTP_EXPIRY_MINUTES') ? OTP_EXPIRY_MINUTES * 60 : 300);
        $_SESSION['email_change_authorized'] = [ 'expires' => $authorizedExpiry ];
        unset($_SESSION['email_change_authorize']);

        header('Content-Type: application/json'); echo json_encode(['success' => true]); exit;
    }

    // Step B: send OTP to the NEW email (requires prior authorization)
    public function sendNewEmailOtp() {
        AuthMiddleware::handle();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        if (!verify_csrf($data['csrf_token'] ?? null)) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid CSRF']); exit;
        }

        $newEmail = trim((string)($data['new_email'] ?? ''));
        if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid new email']); exit;
        }

        // Ensure authorization step completed and not expired
        if (empty($_SESSION['email_change_authorized']) || !is_array($_SESSION['email_change_authorized']) || time() > ($_SESSION['email_change_authorized']['expires'] ?? 0)) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Not authorized. Please verify the OTP sent to your current email first.']); exit;
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!$user) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'User not found']); exit; }

        if (strcasecmp($newEmail, $user['email']) === 0) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'New email is same as current']); exit;
        }

        $exists = $this->userModel->findByEmail($newEmail);
        if ($exists && (int)$exists['id'] !== (int)$_SESSION['user_id']) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Email already in use']); exit;
        }

        try { $otp = (string)random_int(100000, 999999); } catch (Exception $e) { $otp = (string)mt_rand(100000,999999); }
        $expiry = time() + (defined('OTP_EXPIRY_MINUTES') ? OTP_EXPIRY_MINUTES * 60 : 300);

        $_SESSION['email_change_otp'] = [
            'hash' => password_hash($otp, PASSWORD_DEFAULT),
            'expires' => $expiry,
            'attempts' => 0,
            'new_email' => $newEmail
        ];

        require_once INCLUDES_PATH . '/Mailer.php';
        $mailer = new Mailer();
        $subject = 'Confirm new email address';
        $html = "<p>Hi " . htmlspecialchars($user['first_name'] ?? '') . ",</p>" .
                "<p>Use the following OTP to confirm ownership of <strong>" . htmlspecialchars($newEmail) . "</strong> (expires in " . (defined('OTP_EXPIRY_MINUTES') ? OTP_EXPIRY_MINUTES : 5) . " minutes):</p>" .
                "<h2>" . htmlspecialchars($otp) . "</h2>";

        $sent = false;
        try { $sent = $mailer->send($newEmail, ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''), $subject, $html); } catch (Exception $e) { $sent = false; }

        header('Content-Type: application/json'); echo json_encode(['success' => true, 'emailed' => (bool)$sent]); exit;
    }

    // Step B verify: check OTP sent to NEW email and finalize change
    public function verifyNewEmailOtp() {
        AuthMiddleware::handle();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        if (!verify_csrf($data['csrf_token'] ?? null)) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid CSRF']); exit;
        }

        $otp = trim((string)($data['otp'] ?? ''));
        $newEmail = trim((string)($data['new_email'] ?? ''));
        if ($otp === '' || $newEmail === '') { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Missing data']); exit; }

        if (empty($_SESSION['email_change_otp']) || !is_array($_SESSION['email_change_otp'])) {
            header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'No pending change']); exit;
        }

        $pending = &$_SESSION['email_change_otp'];
        if (time() > ($pending['expires'] ?? 0)) { unset($_SESSION['email_change_otp']); header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'OTP expired']); exit; }

        $pending['attempts'] = (int)($pending['attempts'] ?? 0) + 1;
        if ($pending['attempts'] > (defined('OTP_MAX_ATTEMPTS') ? OTP_MAX_ATTEMPTS : 5)) { unset($_SESSION['email_change_otp']); header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Too many attempts']); exit; }

        if (!password_verify($otp, $pending['hash'])) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid OTP']); exit; }

        if (strcasecmp($pending['new_email'] ?? '', $newEmail) !== 0) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'New email mismatch']); exit; }

        // Update email (preserve other fields from DB)
        $current = $this->userModel->findById($_SESSION['user_id']);
        $profileData = [
            'first_name' => $current['first_name'] ?? '',
            'last_name'  => $current['last_name'] ?? '',
            'email'      => $newEmail,
            'phone'      => $current['phone'] ?? '',
            'address'    => $current['address'] ?? '',
            'city'       => $current['city'] ?? '',
            'state'      => $current['state'] ?? '',
            'zip_code'   => $current['zip_code'] ?? ''
        ];
        if ($this->userModel->hasColumn('username')) { $profileData['username'] = $current['username'] ?? null; }

        $ok = $this->userModel->update($_SESSION['user_id'], $profileData);
        if (!$ok) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Update failed']); exit; }

        // Clear session states related to email change
        unset($_SESSION['email_change_otp']);
        unset($_SESSION['email_change_authorized']);
        $_SESSION['email'] = $newEmail;
        header('Content-Type: application/json'); echo json_encode(['success' => true, 'email' => $newEmail]); exit;
    }
}
