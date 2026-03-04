<?php
/* $pageTitle, $extraCss, $isAdmin set by InventoryController */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggleTop"><i data-lucide="menu"></i></button>
            <h2><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="page-content">
        <?php if (!empty($lowStock)): ?>
            <div class="alert alert-warning">
                <i data-lucide="alert-triangle"></i>
                <strong>Low Stock Alert:</strong> <?= count($lowStock) ?> item(s) are below reorder level.
            </div>
        <?php endif; ?>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Reorder Level</th>
                        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == ROLE_SUPER_ADMIN): ?>
                        <th>Update Stock</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inventory)): ?>
                        <?php foreach ($inventory as $item): ?>
                            <tr class="<?= $item['quantity'] <= ($item['reorder_level'] ?? 10) ? 'row-warning' : '' ?>">
                                <td><code><?= htmlspecialchars($item['sku'] ?? 'N/A') ?></code></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td>₱<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <?php if ($item['quantity'] <= ($item['reorder_level'] ?? 10)): ?>
                                        <span class="badge badge-cancelled"><?= $item['quantity'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-delivered"><?= $item['quantity'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $item['reorder_level'] ?? 10 ?></td>
                                <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == ROLE_SUPER_ADMIN): ?>
                                <td>
                                    <form action="<?= APP_URL ?>/index.php?url=staff/inventory/update" method="POST" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <div class="action-btn-group">
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" class="form-control form-control-sm" min="0">
                                            <button type="submit" class="action-btn edit">Update</button>
                                        </div>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= (isset($_SESSION['role_id']) && $_SESSION['role_id'] == ROLE_SUPER_ADMIN) ? 6 : 5 ?>" class="text-center">No inventory records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
