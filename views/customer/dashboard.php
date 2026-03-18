<?php
$pageTitle = 'My Account';
$extraCss = ['customer.css'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

// Fetch featured tile products for the hero carousel
$featuredTiles = [];
try {
    $pdo = $GLOBALS['pdo'] ?? null;
    if ($pdo) {
        $ftStmt = $pdo->query("SELECT p.*, c.name as category_name, COALESCE(i.quantity, 0) as stock 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN inventory i ON i.product_id = p.id 
            WHERE p.category_id = 7 AND p.is_active = 1 
            ORDER BY p.created_at DESC LIMIT 4");
        $featuredTiles = $ftStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) { $featuredTiles = []; }
?>

<div class="container">

    <!-- Hero Banner — Tiles -->
    <section class="hero-banner">
        <div class="hero-banner-bg">
            <div class="hero-tile-pattern"></div>
        </div>
        <div class="hero-banner-content">
            <div class="hero-text">
                <span class="hero-badge"><i data-lucide="star" style="width:12px;height:12px"></i> Main Product</span>
                <h1 class="hero-title">Premium Tiles Collection</h1>
                <p class="hero-subtitle">Transform your spaces with our curated selection of porcelain, ceramic, mosaic, and granite tiles — Davao City's finest.</p>
                <!-- Removed product-specific action buttons to keep homepage product-free -->
            </div>
            <!-- Featured product tiles removed to allow a separate homepage design (no product displays on home) -->
        </div>
    </section>

    <div class="page-heading">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Customer') ?>!</h1>
        <p style="color:var(--steel); margin-top:.25rem;">Here's a summary of your account activity.</p>
    </div>

    <!-- Stat Cards -->
    <div class="stat-cards" style="margin-bottom:2rem;">
        <div class="stat-card">
            <div class="stat-icon"><i data-lucide="shopping-bag"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $totalOrders ?? 0 ?></span>
                <span class="stat-label">Total Orders</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i data-lucide="clock"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $pendingOrders ?? 0 ?></span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i data-lucide="check-circle"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $deliveredOrders ?? 0 ?></span>
                <span class="stat-label">Delivered</span>
            </div>
        </div>
    </div>

    <!-- Recent Orders block removed per request -->
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
