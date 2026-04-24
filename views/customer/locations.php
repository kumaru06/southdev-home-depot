<?php
/* Locations page - shows store location with embedded Google Map */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<style>
/* ── Locations Page ─────────────────────────────────── */
.site-header .main-nav {
    margin-bottom: 0;
}
.loc-hero {
    background: #23282d;
    padding: 4.5rem 1.5rem 6rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.loc-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("<?= APP_URL ?>/assets/uploads/images/image.png") center/cover no-repeat;
    opacity: .32;
}
.loc-hero-inner {
    max-width: 820px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}
.loc-kicker {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: .55rem .95rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.14);
    color: #fff;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 1rem;
}
.loc-kicker svg {
    width: 16px;
    height: 16px;
}
.loc-hero h1 {
    color: #fff;
    font-size: clamp(2.3rem, 4vw, 3.5rem);
    font-weight: 800;
    margin: 0 0 .8rem;
    letter-spacing: -.03em;
    line-height: 1.05;
}
.loc-hero p {
    color: rgba(255,255,255,.78);
    font-size: 1.05rem;
    margin: 0 auto;
    line-height: 1.8;
    max-width: 720px;
}
.loc-highlights {
    margin-top: 1.4rem;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: .75rem;
}
.loc-highlight-pill {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .7rem .95rem;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 999px;
    color: rgba(255,255,255,.9);
    font-size: .9rem;
    backdrop-filter: blur(8px);
}
.loc-highlight-pill svg {
    width: 16px;
    height: 16px;
}

/* ── Info Cards Row ────────────────────────────────── */
.loc-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.35rem;
    max-width: 1040px;
    margin: -3.1rem auto 2.2rem;
    padding: 0 1.5rem;
    position: relative;
    z-index: 2;
}
.loc-card {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 22px;
    padding: 1.85rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: .8rem;
    box-shadow: 0 18px 42px rgba(15,23,42,.08);
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.loc-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 24px 50px rgba(15,23,42,.12);
    border-color: rgba(249,115,22,.22);
}
.loc-card-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: .45rem .9rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    border: 1px solid transparent;
}
.loc-card-badge.orange  { background: rgba(249,115,22,.08); border-color: rgba(249,115,22,.12); color: #F97316; }
.loc-card-badge.blue    { background: rgba(59,130,246,.08); border-color: rgba(59,130,246,.12); color: #3B82F6; }
.loc-card-badge.green   { background: rgba(22,163,74,.08); border-color: rgba(22,163,74,.12); color: #16A34A; }
.loc-card-text h3 {
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--text-secondary);
    margin: 0 0 .5rem;
}
.loc-card-text p {
    font-size: 1rem;
    font-weight: 700;
    color: var(--charcoal);
    margin: 0;
    line-height: 1.55;
}
.loc-card-text .sub {
    font-size: .9rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-top: .35rem;
}

/* ── Map Section ───────────────────────────────────── */
.loc-map-section {
    max-width: 1040px;
    margin: 0 auto 3rem;
    padding: 0 1.5rem;
}
.loc-map-wrapper {
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 48px rgba(15,23,42,.08);
}
.loc-map-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: .85rem;
    padding: 1.35rem 1.5rem;
    border-bottom: 1px solid rgba(15,23,42,.08);
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}
.loc-map-header-badge {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(249,115,22,.1);
    color: #f97316;
    flex-shrink: 0;
}
.loc-map-header-badge svg {
    width: 22px;
    height: 22px;
}
.loc-map-header-copy {
    display: flex;
    flex-direction: column;
    gap: .2rem;
}
.loc-map-header h2 {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--charcoal);
    margin: 0;
}
.loc-map-header-copy small {
    font-size: .85rem;
    color: var(--text-secondary);
}
.loc-map-header span {
    font-size: .85rem;
    color: var(--text-secondary);
    margin-left: auto;
    padding: .5rem .8rem;
    border-radius: 999px;
    background: #f8fafc;
    border: 1px solid rgba(15,23,42,.06);
}
.loc-map-embed {
    width: 100%;
    height: 500px;
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
    padding: 1.1rem 1.5rem;
    border-top: 1px solid rgba(15,23,42,.08);
    background: linear-gradient(180deg, #fcfdff 0%, #f8fafc 100%);
}
.loc-directions p {
    margin: 0;
    font-size: .95rem;
    color: var(--text-secondary);
}
.loc-directions a {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .72rem 1.25rem;
    background: var(--accent);
    color: #fff;
    border-radius: 12px;
    font-size: .85rem;
    font-weight: 700;
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
@media (max-width: 900px) {
    .loc-hero {
        padding: 3.75rem 1.25rem 5.4rem;
    }
    .loc-cards {
        margin-top: -2.6rem;
        padding: 0 1.25rem;
    }
    .loc-map-section {
        padding: 0 1.25rem;
    }
    .loc-map-header {
        align-items: flex-start;
        flex-wrap: wrap;
    }
    .loc-map-header span {
        margin-left: 0;
    }
}

@media (max-width: 640px) {
    .loc-hero { padding: 3rem 1rem 5.5rem; }
    .loc-hero h1 { font-size: 1.85rem; }
    .loc-cards { grid-template-columns: 1fr; margin-top: -2.4rem; padding: 0 1rem; }
    .loc-map-section { padding: 0 1rem; }
    .loc-map-embed { height: 320px; }
    .loc-directions { flex-direction: column; text-align: center; }
    .loc-map-header {
        flex-wrap: wrap;
    }
    .loc-map-header span {
        margin-left: 0;
    }
}

@media (max-width: 420px) {
    .loc-hero {
        padding: 2.6rem .85rem 4.9rem;
    }
    .loc-kicker {
        font-size: .72rem;
        letter-spacing: .06em;
    }
    .loc-hero p {
        font-size: .94rem;
        line-height: 1.68;
    }
    .loc-highlights {
        gap: .55rem;
    }
    .loc-highlight-pill {
        width: 100%;
        justify-content: center;
        text-align: center;
    }
    .loc-card,
    .loc-map-header {
        border-radius: 18px;
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .loc-map-embed {
        height: 280px;
    }
    .loc-directions a {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Hero Banner -->
<div class="loc-hero">
    <div class="loc-hero-inner">
        <span class="loc-kicker">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 21s-6-4.35-6-10a6 6 0 1 1 12 0c0 5.65-6 10-6 10Z"/>
                <circle cx="12" cy="11" r="2.5"/>
            </svg>
            Store Location
        </span>
        <h1>Visit Our Store</h1>
        <p>Drop by Southdev Home Depot in Davao City for tiles, hardware essentials, and reliable in-store assistance for your next residential or commercial project.</p>
        <div class="loc-highlights">
            <span class="loc-highlight-pill">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 8v4l3 3"/>
                    <circle cx="12" cy="12" r="9"/>
                </svg>
                Mon - Sat, 8:00 AM - 5:00 PM
            </span>
            <span class="loc-highlight-pill">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.62a2 2 0 0 1-.45 2.11L8 9.91a16 16 0 0 0 6.09 6.09l1.46-1.28a2 2 0 0 1 2.11-.45c.84.29 1.72.5 2.62.62A2 2 0 0 1 22 16.92z"/>
                </svg>
                +63 (939) 939 8250
            </span>
        </div>
    </div>
</div>

<!-- Info Cards -->
<div class="loc-cards">
    <div class="loc-card">
        <span class="loc-card-badge orange">Visit us</span>
        <div class="loc-card-text">
            <h3>Address</h3>
            <p>3H3W+MJ8, Juna Ave, Talomo,<br>Davao City, Davao del Sur</p>
        </div>
    </div>
    <div class="loc-card">
        <span class="loc-card-badge blue">Open hours</span>
        <div class="loc-card-text">
            <h3>Store Hours</h3>
            <p>Mon – Sat: 8:00 AM – 5:00 PM<br>Sunday: Closed</p>
        </div>
    </div>
    <div class="loc-card">
        <span class="loc-card-badge green">Reach out</span>
        <div class="loc-card-text">
            <h3>Contact</h3>
            <p>+63 (939) 939 8250</p>
            <p class="sub">southdevhomedepo2020@gmail.com</p>
        </div>
    </div>
</div>

<!-- Map -->
<div class="loc-map-section">
    <div class="loc-map-wrapper">
        <div class="loc-map-header">
            <span class="loc-map-header-badge" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21s-6-4.35-6-10a6 6 0 1 1 12 0c0 5.65-6 10-6 10Z"/>
                    <circle cx="12" cy="11" r="2.5"/>
                </svg>
            </span>
            <div class="loc-map-header-copy">
                <h2><?= htmlspecialchars(APP_NAME) ?></h2>
                <small>Find us easily with the live map below.</small>
            </div>
            <span><?= htmlspecialchars(APP_LOCATION) ?></span>
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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 3 3 10l7 2 2 7 9-16Z"/>
                </svg>
                Get Directions
            </a>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
