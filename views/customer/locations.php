<?php
/* Locations page - shows store location with embedded Google Map */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<style>
/* ── Locations Page ─────────────────────────────────── */
.loc-hero {
    background: linear-gradient(135deg, #1B2A4A 0%, #243352 60%, #2d3f66 100%);
    padding: 3.5rem 0 2.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.loc-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    opacity: .5;
}
.loc-hero-inner {
    max-width: 700px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}
.loc-hero-logo {
    width: 72px; height: 72px;
    object-fit: contain;
    border-radius: 16px;
    background: rgba(255,255,255,.1);
    padding: 10px;
    margin-bottom: 1rem;
    box-shadow: 0 8px 24px rgba(0,0,0,.2);
}
.loc-hero h1 {
    color: #fff;
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 .5rem;
    letter-spacing: -.02em;
}
.loc-hero p {
    color: rgba(255,255,255,.7);
    font-size: 1.05rem;
    margin: 0;
}

/* ── Info Cards Row ────────────────────────────────── */
.loc-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
    max-width: 960px;
    margin: -2rem auto 2rem;
    padding: 0 1.5rem;
    position: relative;
    z-index: 2;
}
.loc-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    box-shadow: var(--shadow-md);
    transition: transform .2s, box-shadow .2s;
}
.loc-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}
.loc-card-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.loc-card-icon svg { width: 22px; height: 22px; }
.loc-card-icon.orange  { background: rgba(249,115,22,.1); color: #F97316; }
.loc-card-icon.blue    { background: rgba(59,130,246,.1);  color: #3B82F6; }
.loc-card-icon.green   { background: rgba(22,163,74,.1);   color: #16A34A; }
.loc-card-text h3 {
    font-size: .8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--text-secondary);
    margin: 0 0 .3rem;
}
.loc-card-text p {
    font-size: .95rem;
    font-weight: 600;
    color: var(--charcoal);
    margin: 0;
    line-height: 1.4;
}

/* ── Map Section ───────────────────────────────────── */
.loc-map-section {
    max-width: 960px;
    margin: 0 auto 3rem;
    padding: 0 1.5rem;
}
.loc-map-wrapper {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}
.loc-map-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
}
.loc-map-header .dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: #16A34A;
    animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(22,163,74,.4); }
    50%      { opacity: .8; box-shadow: 0 0 0 6px rgba(22,163,74,0); }
}
.loc-map-header h2 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--charcoal);
    margin: 0;
}
.loc-map-header span {
    font-size: .85rem;
    color: var(--text-secondary);
    margin-left: auto;
}
.loc-map-embed {
    width: 100%;
    height: 450px;
}
.loc-map-embed iframe {
    width: 100%; height: 100%;
    border: 0; display: block;
}

/* ── Get Directions Banner ─────────────────────────── */
.loc-directions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border);
    background: var(--neutral);
}
.loc-directions p {
    margin: 0;
    font-size: .9rem;
    color: var(--text-secondary);
}
.loc-directions a {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .6rem 1.25rem;
    background: var(--accent);
    color: #fff;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s, transform .15s;
    white-space: nowrap;
}
.loc-directions a:hover {
    background: var(--accent-hover);
    transform: translateY(-1px);
}
.loc-directions a svg { width: 16px; height: 16px; }

/* ── Responsive ────────────────────────────────────── */
@media (max-width: 640px) {
    .loc-hero { padding: 2.5rem 1rem 2rem; }
    .loc-hero h1 { font-size: 1.5rem; }
    .loc-hero-logo { width: 56px; height: 56px; padding: 8px; }
    .loc-cards { grid-template-columns: 1fr; margin-top: -1.5rem; padding: 0 1rem; }
    .loc-map-section { padding: 0 1rem; }
    .loc-map-embed { height: 320px; }
    .loc-directions { flex-direction: column; text-align: center; }
}
</style>

<!-- Hero Banner -->
<div class="loc-hero">
    <div class="loc-hero-inner">
        <img src="<?= APP_URL ?>/assets/uploads/images/logo/location.png"
             alt="Location" class="loc-hero-logo">
        <h1>Visit Our Store</h1>
        <p>Davao City's Premier Tiles &amp; Construction Supply</p>
    </div>
</div>

<!-- Info Cards -->
<div class="loc-cards">
    <div class="loc-card">
        <div class="loc-card-icon orange">
            <i data-lucide="map-pin"></i>
        </div>
        <div class="loc-card-text">
            <h3>Address</h3>
            <p>3H3W+MJ8, Juna Ave, Talomo,<br>Davao City, Davao del Sur</p>
        </div>
    </div>
    <div class="loc-card">
        <div class="loc-card-icon blue">
            <i data-lucide="clock"></i>
        </div>
        <div class="loc-card-text">
            <h3>Store Hours</h3>
            <p>Mon – Sat: 7:30 AM – 6:00 PM<br>Sunday: 8:00 AM – 5:00 PM</p>
        </div>
    </div>
    <div class="loc-card">
        <div class="loc-card-icon green">
            <i data-lucide="phone"></i>
        </div>
        <div class="loc-card-text">
            <h3>Contact</h3>
            <p>(082) 123-4567<br>info@southdevhomedepot.ph</p>
        </div>
    </div>
</div>

<!-- Map -->
<div class="loc-map-section">
    <div class="loc-map-wrapper">
        <div class="loc-map-header">
            <span class="dot"></span>
            <h2><?= htmlspecialchars(APP_NAME) ?></h2>
            <span><i data-lucide="navigation" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:4px;"></i><?= htmlspecialchars(APP_LOCATION) ?></span>
        </div>
        <div class="loc-map-embed">
            <iframe
                src="https://www.google.com/maps?q=Southdev+Home+Depot,+Davao+City&output=embed"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                aria-label="Store location map">
            </iframe>
        </div>
        <div class="loc-directions">
            <p>Need help finding us? Get turn-by-turn directions to our store.</p>
            <a href="https://www.google.com/maps/dir/?api=1&destination=Southdev+Home+Depot,+Davao+City" target="_blank" rel="noopener">
                <i data-lucide="corner-up-right"></i>
                Get Directions
            </a>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
