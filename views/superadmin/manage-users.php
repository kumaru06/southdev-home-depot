<?php
$pageTitle = 'Manage Users';
$extraCss = ['admin.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>Manage Users</h2>
        </div>
    </div>

    <div class="page-content">

        <!-- Add User Card -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <i data-lucide="user-plus" style="width:20px;height:20px;color:var(--accent);"></i>
                <h3 style="margin:0; font-size:1.05rem; font-weight:600;">Add New User</h3>
            </div>
            <form action="<?= APP_URL ?>/index.php?url=admin/users/create" method="POST">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Last Name <span class="required">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Role <span class="required">*</span></label>
                            <select name="role_id" class="form-control" required>
                                <option value="1">Customer</option>
                                <option value="2">Staff</option>
                                <option value="4">Inventory In-Charge</option>
                                <option value="3">Super Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-accent">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Add User
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:.5rem;">
                    <i data-lucide="users" style="width:20px;height:20px;color:var(--accent);"></i>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:600;">All Users</h3>
                </div>
                <span class="badge badge-pending" style="font-size:.8rem;"><?= count($users ?? []) ?> total</span>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td data-label="ID"><?= $user['id'] ?></td>
                                    <td data-label="Name">
                                        <div style="display:flex; align-items:center; gap:.5rem;">
                                            <div class="user-avatar-circle">
                                                <?= strtoupper(substr($user['first_name'],0,1) . substr($user['last_name'],0,1)) ?>
                                            </div>
                                            <span class="user-name-text"><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])) ?: '<em style="opacity:.4">No name</em>' ?></span>
                                        </div>
                                    </td>
                                    <td data-label="Email"><span class="user-email-text"><?= htmlspecialchars($user['email']) ?></span></td>
                                    <td data-label="Role">
                                        <?php
                                        $roleBadgeClass = 'badge-delivered';
                                        if ($user['role_name'] === 'super_admin') $roleBadgeClass = 'badge-processing';
                                        elseif ($user['role_name'] === 'staff') $roleBadgeClass = 'badge-pending';
                                        elseif ($user['role_name'] === 'inventory_incharge') $roleBadgeClass = 'badge-pending';
                                        ?>
                                        <span class="badge <?= $roleBadgeClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $user['role_name'])) ?>
                                        </span>
                                    </td>
                                    <td data-label="Status">
                                        <span class="badge <?= $user['is_active'] ? 'badge-delivered' : 'badge-cancelled' ?>">
                                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td data-label="Joined" style="white-space:nowrap;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td data-label="Actions">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <div class="action-btn-group">
                                                <a href="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/toggle"
                                                   class="action-btn <?= $user['is_active'] ? 'deactivate' : 'approve' ?>"
                                                   onclick="return confirm('<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> this user?');"
                                                   title="<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                    <i data-lucide="<?= $user['is_active'] ? 'user-x' : 'user-check' ?>" style="width:15px;height:15px;"></i>
                                                    <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                                </a>

                                                <form action="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/delete" method="POST" class="inline-form">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="action-btn delete" title="Delete">
                                                        <i data-lucide="trash-2" style="width:15px;height:15px;"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:var(--steel); font-size:.85rem;">You</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="empty-state" style="text-align:center; padding:2rem;">
                                <i data-lucide="users" style="width:40px;height:40px;color:var(--steel);margin-bottom:.5rem;"></i>
                                <p>No users found.</p>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
