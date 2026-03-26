<?php
// Preserve any page title provided by the router; otherwise default to "My Account"
if (!isset($pageTitle) || empty($pageTitle)) {
    $pageTitle = 'My Account';
}
$extraCss = ['customer.css'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

// Determine hero image: prefer display1.jpg if present, otherwise fall back to image.png
$heroRel = 'assets/uploads/images/display1.jpg';
$heroFull = ROOT_PATH . '/' . $heroRel;
if (!file_exists($heroFull)) {
    $heroRel = 'assets/uploads/images/image.png';
}

// Also prepare display2 (small thumbnail shown below the hero if present)
$display2 = 'assets/uploads/images/display2.jpg';
$display2Full = ROOT_PATH . '/' . $display2;
if (!file_exists($display2Full)) {
    $display2 = 'assets/uploads/images/image.png';
}

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

    <!-- Hero Banner — replaced with plain image hero (text removed) -->
    <section class="hero-banner" style="background-image: url('<?= APP_URL ?>/<?= $heroRel ?>'); background-size: cover; background-position: center;">
    </section>

    <!-- Optional secondary display image below the hero -->
    <div class="hero-below">
        <div class="hero-below-inner">
            <img src="<?= APP_URL ?>/<?= $display2 ?>" alt="Display 2" class="hero-below-thumb">
        </div>
    </div>

    <!-- page-heading removed per request -->

    <!-- Stat cards removed per request -->

    <!-- Recent Orders block removed per request -->
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
