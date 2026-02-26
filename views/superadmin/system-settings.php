<?php
$pageTitle = 'System Settings';
$extraCss = ['admin.css'];
$isAdmin = true;
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2>System Settings</h2>
        </div>
    </div>

    <div class="page-content">

        <!-- General Settings -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <i data-lucide="settings" style="width:20px;height:20px;color:var(--accent);"></i>
                <h3 style="margin:0; font-size:1.05rem; font-weight:600;">General Settings</h3>
            </div>
            <form action="<?= APP_URL ?>/index.php?url=admin/settings/update" method="POST">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(APP_NAME) ?>">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Site URL</label>
                            <input type="text" name="site_url" class="form-control" value="<?= htmlspecialchars(APP_URL) ?>">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Items Per Page</label>
                            <input type="number" name="items_per_page" class="form-control" value="<?= ITEMS_PER_PAGE ?>" min="5" max="100">
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-accent">
                        <i data-lucide="save" style="width:16px;height:16px;"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment Settings -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <i data-lucide="credit-card" style="width:20px;height:20px;color:var(--accent);"></i>
                <h3 style="margin:0; font-size:1.05rem; font-weight:600;">Payment Methods</h3>
            </div>
            <form action="<?= APP_URL ?>/index.php?url=admin/settings/payment" method="POST">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:500;">
                                <input type="checkbox" name="cod_enabled" value="1" checked style="width:18px;height:18px;accent-color:var(--accent);">
                                Cash on Delivery (COD)
                            </label>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:500;">
                                <input type="checkbox" name="gcash_enabled" value="1" checked style="width:18px;height:18px;accent-color:var(--accent);">
                                GCash (PayMongo)
                            </label>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:500;">
                                <input type="checkbox" name="bank_enabled" value="1" checked style="width:18px;height:18px;accent-color:var(--accent);">
                                Bank Transfer
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-accent">
                        <i data-lucide="save" style="width:16px;height:16px;"></i> Save Payment Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <i data-lucide="lock" style="width:20px;height:20px;color:var(--accent);"></i>
                <h3 style="margin:0; font-size:1.05rem; font-weight:600;">Security</h3>
            </div>
            <form action="<?= APP_URL ?>/index.php?url=profile" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="return_url" value="admin/settings">

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" minlength="8" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8" required>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-accent">
                        <i data-lucide="key" style="width:16px;height:16px;"></i> Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- System Information -->
        <div class="card">
            <div class="card-header" style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
                <i data-lucide="info" style="width:20px;height:20px;color:var(--accent);"></i>
                <h3 style="margin:0; font-size:1.05rem; font-weight:600;">System Information</h3>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <tbody>
                        <tr>
                            <td style="font-weight:600; width:200px;">PHP Version</td>
                            <td><?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">Server Software</td>
                            <td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">App Version</td>
                            <td><?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">Database</td>
                            <td>MySQL / PDO</td>
                        </tr>
                        <tr>
                            <td style="font-weight:600;">Session Status</td>
                            <td>
                                <span class="badge badge-delivered">Active</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
