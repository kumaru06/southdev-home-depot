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

    public function create() {
        AuthMiddleware::superAdmin();
        AuthMiddleware::csrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $data = [
            'role_id'    => intval($_POST['role_id']),
            'first_name' => trim($_POST['first_name']),
            'last_name'  => trim($_POST['last_name']),
            'email'      => trim($_POST['email']),
            'password'   => $_POST['password'],
            'phone'      => trim($_POST['phone'] ?? '')
        ];

        if ($this->userModel->findByEmail($data['email'])) {
            flash('error', 'Email already exists.');
        } elseif ($this->userModel->create($data)) {
            // If super admin created a staff or inventory account, mark email verified
            // so the account can sign in immediately without email verification.
            $newId = (int)$this->pdo->lastInsertId();
            if (in_array($data['role_id'], [2, 4], true) && $newId > 0) {
                $this->userModel->markEmailVerified($newId);
            }

            $this->logModel->create(LOG_USER_CREATE, "User created: {$data['email']} (Role ID: {$data['role_id']})");
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

            if (strlen($newPassword) < 8) {
                flash('error', 'New password must be at least 8 characters.');
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
}
