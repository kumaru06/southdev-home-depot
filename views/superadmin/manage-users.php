<?php
$pageTitle = 'Manage Users';
$extraCss = ['admin.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<style>
.action-dropdown-menu { display: none !important; }
.action-dropdown.open .action-dropdown-menu { display: block !important; }
.user-role-tab { border: none; background: none; padding: 10px 16px; border-bottom: 2.5px solid transparent; border-radius: 0; font-family: inherit; font-size: .82rem; font-weight: 600; color: #6c7a8d; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
.user-role-tab.active { color: #F97316; border-bottom-color: #F97316; }
.action-dropdown-btn { border: 1px solid #e8ecf1; border-radius: 6px; background: #fff; padding: 6px 12px; font-family: inherit; font-size: .78rem; font-weight: 700; color: #6c7a8d; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; }
</style>

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
                                <option value="">Select role</option>
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
        <?php
        // Group users by role
        $grouped = [
            'super_admin'        => [],
            'staff'              => [],
            'inventory_incharge' => [],
            'customer'           => [],
        ];
        foreach ($users ?? [] as $u) {
            $role = $u['role_name'] ?? 'customer';
            if (isset($grouped[$role])) {
                $grouped[$role][] = $u;
            } else {
                $grouped['customer'][] = $u;
            }
        }

        $roleTabs = [
            'all'                => ['label' => 'All Users',           'icon' => 'users',          'count' => count($users ?? [])],
            'super_admin'        => ['label' => 'Super Admins',        'icon' => 'shield',          'count' => count($grouped['super_admin'])],
            'staff'              => ['label' => 'Staff',               'icon' => 'briefcase',       'count' => count($grouped['staff'])],
            'inventory_incharge' => ['label' => 'Inventory In-Charge', 'icon' => 'package',         'count' => count($grouped['inventory_incharge'])],
            'customer'           => ['label' => 'Customers',           'icon' => 'shopping-bag',    'count' => count($grouped['customer'])],
        ];
        ?>

        <div class="card">
            <!-- Role Tabs -->
            <div class="user-role-tabs" style="display:flex; gap:6px; flex-wrap:wrap; padding:16px 20px 0; border-bottom:1px solid var(--border,#e8ecf1);">
                <?php foreach ($roleTabs as $key => $tab): ?>
                    <button class="user-role-tab <?= $key === 'all' ? 'active' : '' ?>" data-role="<?= $key ?>" type="button">
                        <i data-lucide="<?= $tab['icon'] ?>" style="width:14px;height:14px;"></i>
                        <?= $tab['label'] ?>
                        <span class="tab-count"><?= $tab['count'] ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="data-table-wrap">
                <table class="data-table" id="usersTable">
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
                                <tr data-user-role="<?= htmlspecialchars($user['role_name'] ?? 'customer') ?>">
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
                                        <?php $isCustomer = ($user['role_name'] === 'customer'); ?>
                                        <span class="badge <?= $user['is_active'] ? 'badge-delivered' : 'badge-cancelled' ?>">
                                            <?php if (!$user['is_active'] && $isCustomer): ?>
                                                Blocked
                                            <?php else: ?>
                                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td data-label="Joined" style="white-space:nowrap;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td data-label="Actions">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <div class="action-dropdown">
                                                <button type="button" class="action-dropdown-btn" onclick="toggleActionMenu(this)">
                                                    <i data-lucide="more-horizontal" style="width:16px;height:16px;"></i>
                                                    Action
                                                    <i data-lucide="chevron-down" style="width:13px;height:13px;opacity:.6;"></i>
                                                </button>
                                                <div class="action-dropdown-menu" style="display:none">
                                                    <?php if ($isCustomer): ?>
                                                        <a href="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/view" class="action-menu-item">
                                                            <i data-lucide="eye" style="width:14px;height:14px;"></i> View Profile
                                                        </a>
                                                        <a href="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/toggle"
                                                           class="action-menu-item <?= $user['is_active'] ? 'danger' : 'success' ?>"
                                                           onclick="return confirm('<?= $user['is_active'] ? 'Block' : 'Unblock' ?> this customer?');">
                                                            <i data-lucide="<?= $user['is_active'] ? 'ban' : 'shield-check' ?>" style="width:14px;height:14px;"></i>
                                                            <?= $user['is_active'] ? 'Block' : 'Unblock' ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/toggle"
                                                           class="action-menu-item <?= $user['is_active'] ? 'danger' : 'success' ?>"
                                                           onclick="return confirm('<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> this user?');">
                                                            <i data-lucide="<?= $user['is_active'] ? 'user-x' : 'user-check' ?>" style="width:14px;height:14px;"></i>
                                                            <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                                        </a>
                                                        <form action="<?= APP_URL ?>/index.php?url=admin/users/<?= $user['id'] ?>/delete" method="POST" style="margin:0;">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="action-menu-item danger" onclick="return confirm('Permanently delete this user?');">
                                                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Delete
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
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

<style>
.action-dropdown-menu { display: none !important; }
.action-dropdown.open .action-dropdown-menu { display: block !important; }
.user-role-tabs { margin-bottom: 0; }
.user-role-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border: none;
    background: none;
    font-family: inherit;
    font-size: .82rem;
    font-weight: 600;
    color: var(--steel, #6c7a8d);
    cursor: pointer;
    border-bottom: 2.5px solid transparent;
    border-radius: 0;
    transition: all .2s;
    white-space: nowrap;
}
.user-role-tab:hover {
    color: var(--charcoal, #1B2A4A);
    background: rgba(249,115,22,.04);
}
.user-role-tab.active {
    color: var(--accent, #F97316);
    border-bottom-color: var(--accent, #F97316);
}
.tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 10px;
    background: var(--surface, #F0F2F5);
    font-size: .72rem;
    font-weight: 700;
    color: var(--steel, #6c7a8d);
}
.user-role-tab.active .tab-count {
    background: rgba(249,115,22,.12);
    color: var(--accent, #F97316);
}
/* Action Dropdown */
.action-dropdown {
    position: relative;
    display: inline-block;
}
.action-dropdown-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border: 1px solid var(--border, #e8ecf1);
    border-radius: var(--radius-sm, 6px);
    background: var(--white, #fff);
    font-family: inherit;
    font-size: .78rem;
    font-weight: 700;
    color: var(--text-secondary, #6c7a8d);
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
}
.action-dropdown-btn:hover {
    border-color: var(--accent, #F97316);
    color: var(--accent, #F97316);
    background: rgba(249,115,22,.04);
}
.action-dropdown.open .action-dropdown-btn {
    border-color: var(--accent, #F97316);
    color: var(--accent, #F97316);
    box-shadow: 0 0 0 3px rgba(249,115,22,.1);
}
.action-dropdown-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 4px);
    min-width: 155px;
    background: var(--white, #fff);
    border: 1px solid var(--border, #e8ecf1);
    border-radius: var(--radius-md, 10px);
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    z-index: 50;
    padding: 4px;
    display: none;
}
.action-dropdown.open .action-dropdown-menu {
    display: block;
}
.action-menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    border: none;
    background: none;
    font-family: inherit;
    font-size: .82rem;
    font-weight: 600;
    color: var(--charcoal, #1B2A4A);
    text-decoration: none;
    border-radius: var(--radius-sm, 6px);
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
}
.action-menu-item:hover {
    background: var(--surface, #F0F2F5);
}
.action-menu-item.danger {
    color: #dc2626;
}
.action-menu-item.danger:hover {
    background: #fef2f2;
}
.action-menu-item.success {
    color: #16a34a;
}
.action-menu-item.success:hover {
    background: #f0fdf4;
}
</style>

<script>
/* Action dropdown toggle */
function toggleActionMenu(btn) {
    var dropdown = btn.closest('.action-dropdown');
    var wasOpen = dropdown.classList.contains('open');
    // Close all open dropdowns
    document.querySelectorAll('.action-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    if (!wasOpen) dropdown.classList.add('open');
}
// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.action-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('.user-role-tab');
    var rows = document.querySelectorAll('#usersTable tbody tr[data-user-role]');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');

            var role = tab.getAttribute('data-role');
            rows.forEach(function(row) {
                if (role === 'all' || row.getAttribute('data-user-role') === role) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
