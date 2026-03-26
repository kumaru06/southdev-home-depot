<?php
/* Locations page - shows store location with embedded Google Map */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <h1 class="page-heading"><i data-lucide="map-pin"></i> Locations</h1>

    <div class="card">
        <div class="card-body">
            <p>Store Location</p>
            <address style="font-weight:600; margin-bottom:1rem;"><?= htmlspecialchars(APP_LOCATION) ?></address>
                <div class="map-responsive" style="width:100%;height:420px;overflow:hidden;border-radius:8px;">
                    <iframe
                        width="100%"
                        height="100%"
                        frameborder="0"
                        style="border:0;"
                        src="https://www.google.com/maps?q=Southdev+Home+Depot,+Davao+City&output=embed"
                        allowfullscreen
                        aria-label="Store location map">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
