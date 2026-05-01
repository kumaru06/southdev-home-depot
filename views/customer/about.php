<?php
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$productCount = 0;
$categoryCount = 0;
$yearsServing = 8;

try {
    $pdo = $GLOBALS['pdo'] ?? null;
    if ($pdo instanceof PDO) {
        $productCount = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
        $categoryCount = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    }
} catch (Throwable $e) {
    $productCount = 0;
    $categoryCount = 0;
}

$heroImage   = APP_URL . '/assets/uploads/images/roomgallery/commercial/commercial4.png';
$storyImage  = APP_URL . '/assets/uploads/images/image.png';
$ctaImage    = APP_URL . '/assets/uploads/images/roomgallery/livingroom/livingroom4.png';
$kitchenImg  = APP_URL . '/assets/uploads/images/roomgallery/kitchen/kitchen.png';
$livingImg   = APP_URL . '/assets/uploads/images/roomgallery/livingroom/livingroom.png';
$bathImg     = APP_URL . '/assets/uploads/images/roomgallery/bathroom/bathroom.png';
$diningImg   = APP_URL . '/assets/uploads/images/roomgallery/dining/dining.png';

$heroHighlights = [
    [
        'value' => $yearsServing . '+',
        'label' => 'years of trusted local service',
    ],
    [
        'value' => $productCount > 0 ? number_format($productCount) . '+' : '100+',
        'label' => 'active products ready to browse',
    ],
    [
        'value' => 'Matina',
        'label' => 'showroom destination in Davao City',
    ],
];

$showcaseCards = [
    [
        'eyebrow' => 'Showroom feel',
        'title' => 'A more elevated look for every product category',
        'copy' => 'Tiles, hardware, and construction essentials are presented with a cleaner, more premium visual direction.',
    ],
    [
        'eyebrow' => 'Customer ease',
        'title' => 'Simple browsing with practical buying confidence',
        'copy' => 'Visitors can quickly discover products, compare options, and feel guided from inspiration to checkout.',
    ],
    [
        'eyebrow' => 'Built for projects',
        'title' => 'Reliable supply for homes, renovations, and active sites',
        'copy' => 'Southdev supports both homeowners and contractors with dependable inventory, fair prices, and helpful service.',
    ],
];

$servicePillars = [
    'Trusted pricing',
    'Dependable inventory',
    'Helpful in-store guidance',
];

$galleryCards = [
    [
        'image' => $kitchenImg,
        'title' => 'Kitchen',
        'copy' => 'Durable finishes and clean surfaces for stylish daily living.',
    ],
    [
        'image' => $livingImg,
        'title' => 'Living Room',
        'copy' => 'Comfort-forward details with a more polished, welcoming atmosphere.',
    ],
    [
        'image' => $bathImg,
        'title' => 'Bathroom',
        'copy' => 'Practical fixtures and refined textures that feel fresh and premium.',
    ],
    [
        'image' => $diningImg,
        'title' => 'Dining',
        'copy' => 'Warm finishes and balanced materials that complete gathering spaces.',
    ],
];

$awards = [
    [
        'title' => 'Trusted Hardware Supply',
        'subtitle' => 'Reliable products for homes, repairs, and construction projects.',
    ],
    [
        'title' => 'Wide Product Selection',
        'subtitle' => 'Tiles, hardware, and building essentials in one place.',
    ],
    [
        'title' => 'Helpful Customer Support',
        'subtitle' => 'Practical in-store assistance for product selection and orders.',
    ],
    [
        'title' => 'Project-Ready Service',
        'subtitle' => 'Serving both homeowners and contractors with dependable supply.',
    ],
];

$promiseVisual = APP_URL . '/assets/uploads/images/storeinside.png';

$reachStats = [
    [
        'value' => $yearsServing . '+',
        'label' => 'Years serving local customers',
    ],
    [
        'value' => $productCount > 0 ? number_format($productCount) . '+' : '100+',
        'label' => 'Active products available',
    ],
    [
        'value' => $categoryCount > 0 ? $categoryCount : '9',
        'label' => 'Core construction categories',
    ],
    [
        'value' => '24/7',
        'label' => 'Online catalogue access',
    ],
];
?>

<style>
/* ═══════════════════════════════════════════════
   ABOUT PAGE — Complete Redesign
   ═══════════════════════════════════════════════ */

/* ---------- Reset / base ---------- */
.about-page {
    --clr-brand:    #f97316;
    --clr-brand-dk: #ea580c;
    --clr-dark:     #0f172a;
    --clr-mid:      #334155;
    --clr-muted:    #64748b;
    --clr-light:    #f1f5f9;
    --clr-white:    #ffffff;
    --radius-lg:    24px;
    --radius-xl:    32px;
    --shadow-sm:    0 4px 16px rgba(15,23,42,.07);
    --shadow-md:    0 12px 36px rgba(15,23,42,.11);
    --shadow-lg:    0 24px 56px rgba(15,23,42,.15);
    font-family: inherit;
    overflow-x: hidden;
    background: #ffffff;
}
.site-header .main-nav { margin-bottom: 0; }

/* ---------- shared containers ---------- */
.ab-wrap {
    max-width: 1180px;
    margin-inline: auto;
    padding-inline: 1.5rem;
}
.ab-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--clr-brand);
    margin-bottom: .9rem;
}
.ab-eyebrow::before {
    content: '';
    display: block;
    width: 28px;
    height: 3px;
    border-radius: 2px;
    background: var(--clr-brand);
}

/* ---------- scroll-reveal ---------- */
.ab-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity .6s ease, transform .6s ease;
    will-change: opacity, transform;
}
.ab-reveal.ab-visible { opacity: 1; transform: none; }
.ab-d1 { transition-delay: .08s; }
.ab-d2 { transition-delay: .18s; }
.ab-d3 { transition-delay: .28s; }
.ab-d4 { transition-delay: .38s; }

/* ═══════════ HERO ═══════════ */
.ab-hero {
    position: relative;
    background:
        linear-gradient(140deg, rgba(10,18,40,.9) 0%, rgba(20,35,60,.8) 55%, rgba(15,23,42,.58) 100%),
        url('<?= $heroImage ?>') center/cover no-repeat;
    min-height: 100svh;
    display: flex;
    align-items: center;
    padding: 6rem 1.5rem 6rem;
}
.ab-hero::after {
    content: '';
    position: absolute;
    inset: auto 0 -2px 0;
    height: 300px;
    background: linear-gradient(to bottom, transparent 0%, #ffffff 80%, #ffffff 100%);
    pointer-events: none;
    z-index: 1;
}

/* floating blobs */
.ab-blob {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    filter: blur(72px);
    opacity: .22;
}
.ab-blob-1 {
    width: 520px; height: 520px;
    top: -140px; right: -120px;
    display: none;
}
.ab-blob-2 {
    width: 380px; height: 380px;
    bottom: 60px; left: -80px;
    background: #3b82f6;
    opacity: .12;
}

.ab-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr .85fr;
    gap: 3rem;
    align-items: center;
    position: relative;
    z-index: 2;
}

/* left copy */
.ab-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .45rem 1rem .45rem .5rem;
    border-radius: 999px;
    background: rgba(255,255,255,.1);
    backdrop-filter: blur(6px);
    color: rgba(255,255,255,.9);
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 1.25rem;
    border: 1px solid rgba(255,255,255,.14);
}
.ab-hero-eyebrow-dot {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--clr-brand);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    flex-shrink: 0;
}
.ab-hero-copy h1 {
    color: var(--clr-white);
    font-size: clamp(2.4rem, 5.5vw, 4.2rem);
    line-height: 1.03;
    font-weight: 900;
    letter-spacing: -.04em;
    margin: 0 0 1.25rem;
}
.ab-hero-copy h1 em {
    font-style: normal;
    color: var(--clr-brand);
}
.ab-hero-copy p {
    color: rgba(255,255,255,.78);
    font-size: 1.1rem;
    line-height: 1.78;
    max-width: 560px;
    margin: 0 0 2rem;
}
.ab-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}
.ab-btn {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: .9rem 1.6rem;
    border-radius: 14px;
    font-weight: 700;
    font-size: .97rem;
    text-decoration: none;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    white-space: nowrap;
}
.ab-btn:hover { transform: translateY(-3px); }
.ab-btn-primary {
    background: var(--clr-brand);
    color: #fff;
    box-shadow: 0 16px 36px rgba(249,115,22,.35);
}
.ab-btn-primary:hover { background: var(--clr-brand-dk); box-shadow: 0 20px 44px rgba(249,115,22,.42); }
.ab-btn-ghost {
    background: rgba(255,255,255,.1);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,.22);
    backdrop-filter: blur(4px);
}
.ab-btn-ghost:hover { background: rgba(255,255,255,.18); }
.ab-btn-dark {
    background: var(--clr-dark);
    color: #fff;
    box-shadow: var(--shadow-md);
}
.ab-btn-dark:hover { background: var(--clr-mid); }

/* right card */
.ab-hero-card {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: var(--radius-xl);
    padding: 1.8rem;
    backdrop-filter: blur(14px);
}
.ab-hero-card > img {
    width: 100%;
    height: 280px;
    object-fit: cover;
    border-radius: 20px;
    display: block;
    margin-bottom: 1.25rem;
}
.ab-hero-card h2 {
    color: #fff;
    font-size: 1.18rem;
    margin: 0 0 .55rem;
    font-weight: 700;
}
.ab-hero-card p {
    color: rgba(255,255,255,.7);
    font-size: .94rem;
    line-height: 1.72;
    margin: 0 0 1.25rem;
}
.ab-hero-card-meta {
    display: flex;
    gap: 1rem;
}
.ab-hero-meta-chip {
    flex: 1;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 14px;
    padding: .7rem .85rem;
    text-align: center;
}
.ab-hero-meta-chip strong {
    display: block;
    color: var(--clr-brand);
    font-size: 1.3rem;
    line-height: 1;
    margin-bottom: .25rem;
}
.ab-hero-meta-chip span {
    color: rgba(255,255,255,.62);
    font-size: .78rem;
}

/* ═══════════ STATS BAND ═══════════ */
.ab-stats-band {
    background: #ffffff;
    position: relative;
    z-index: 4;
    margin-top: -2px;
    padding: 2.5rem 1.5rem 2.5rem;
}
.ab-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    max-width: 1180px;
    margin-inline: auto;
}
.ab-stat-card {
    background: var(--clr-white);
    border: 1.5px solid rgba(15,23,42,.08);
    border-radius: 22px;
    padding: 1.6rem 1.4rem 1.4rem;
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease;
}
.ab-stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.ab-stat-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(249,115,22,.14), rgba(251,146,60,.26));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: .9rem;
}
.ab-stat-card strong {
    display: block;
    font-size: 2.4rem;
    font-weight: 900;
    color: var(--clr-dark);
    line-height: 1;
    margin-bottom: .3rem;
    letter-spacing: -.03em;
}
.ab-stat-card span {
    color: var(--clr-muted);
    font-size: .9rem;
    line-height: 1.55;
}

/* ═══════════ STORY SECTION ═══════════ */
.ab-story {
    padding: 5rem 1.5rem;
    background: var(--clr-white);
}
.ab-story-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

/* Image mosaic */
.ab-mosaic {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 280px 200px;
    gap: .9rem;
    position: relative;
}
.ab-mosaic-main {
    grid-column: 1 / 3;
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}
.ab-mosaic-main img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .5s ease;
}
.ab-mosaic-main:hover img { transform: scale(1.04); }
.ab-mosaic-thumb {
    border-radius: 18px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    position: relative;
}
.ab-mosaic-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .5s ease;
}
.ab-mosaic-thumb:hover img { transform: scale(1.06); }
.ab-mosaic-badge {
    position: absolute;
    bottom: 1rem;
    left: 1rem;
    background: var(--clr-white);
    border-radius: 14px;
    padding: .7rem 1rem;
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: .65rem;
    z-index: 2;
}
.ab-mosaic-badge-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--clr-brand), #fb923c);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.ab-mosaic-badge strong {
    display: block;
    color: var(--clr-dark);
    font-size: .92rem;
    line-height: 1;
    margin-bottom: .15rem;
}
.ab-mosaic-badge span { color: var(--clr-muted); font-size: .8rem; }

/* copy side */
.ab-story-copy { }
.ab-story-copy h2 {
    font-size: clamp(1.9rem, 3.2vw, 2.8rem);
    line-height: 1.12;
    font-weight: 900;
    color: var(--clr-dark);
    letter-spacing: -.03em;
    margin: 0 0 1rem;
}
.ab-story-copy h2 em { font-style: normal; color: var(--clr-brand); }
.ab-story-copy p {
    color: var(--clr-muted);
    line-height: 1.82;
    font-size: .99rem;
    margin: 0 0 .9rem;
}

/* checklist */
.ab-checklist { margin-top: 1.5rem; display: grid; gap: .75rem; }
.ab-check-item {
    display: flex;
    gap: .9rem;
    align-items: flex-start;
    background: var(--clr-light);
    border-radius: 16px;
    padding: 1rem 1.1rem;
    transition: background .2s ease;
}
.ab-check-item:hover { background: #e9f0f8; }
.ab-check-icon {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--clr-brand), #fb923c);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    font-weight: 900;
    flex-shrink: 0;
    box-shadow: 0 6px 16px rgba(249,115,22,.3);
}
.ab-check-item strong {
    display: block;
    color: var(--clr-dark);
    font-size: .96rem;
    margin-bottom: .2rem;
}
.ab-check-item span { color: var(--clr-muted); font-size: .9rem; line-height: 1.6; }

/* ═══════════ TIMELINE STRIP ═══════════ */
.ab-timeline {
    padding: 4rem 1.5rem;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    position: relative;
    overflow: hidden;
}
.ab-timeline::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.ab-timeline-header {
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
    z-index: 2;
}
.ab-timeline-header .ab-eyebrow { color: var(--clr-brand); }
.ab-timeline-header h2 {
    font-size: clamp(1.8rem, 3vw, 2.6rem);
    font-weight: 900;
    color: #fff;
    margin: 0;
    letter-spacing: -.03em;
}
.ab-tl-rail {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    position: relative;
    z-index: 2;
    max-width: 1000px;
    margin-inline: auto;
}
.ab-tl-rail::before {
    content: '';
    position: absolute;
    top: 28px;
    left: calc(12.5% + 1rem);
    right: calc(12.5% + 1rem);
    height: 2px;
    background: linear-gradient(90deg, var(--clr-brand), #fb923c, var(--clr-brand));
    opacity: .5;
}
.ab-tl-item { text-align: center; }
.ab-tl-dot {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--clr-brand), #fb923c);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    color: #fff;
    font-size: .85rem;
    margin-bottom: .9rem;
    box-shadow: 0 0 0 6px rgba(249,115,22,.15), 0 12px 28px rgba(249,115,22,.3);
    position: relative;
    z-index: 2;
}
.ab-tl-item h3 {
    color: #fff;
    font-size: .95rem;
    font-weight: 700;
    margin: 0 0 .4rem;
}
.ab-tl-item p {
    color: rgba(255,255,255,.55);
    font-size: .84rem;
    line-height: 1.65;
    margin: 0;
}

/* ═══════════ VALUES ═══════════ */
.ab-values {
    padding: 5rem 1.5rem;
    background: var(--clr-light);
}
.ab-values-header {
    text-align: center;
    max-width: 680px;
    margin: 0 auto 3rem;
}
.ab-values-header h2 {
    font-size: clamp(1.9rem, 3.2vw, 2.7rem);
    font-weight: 900;
    color: var(--clr-dark);
    letter-spacing: -.03em;
    margin: 0 0 .8rem;
}
.ab-values-header p {
    color: var(--clr-muted);
    font-size: 1.02rem;
    line-height: 1.76;
    margin: 0;
}
.ab-values-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    max-width: 1180px;
    margin-inline: auto;
}
.ab-value-card {
    background: var(--clr-white);
    border: 1.5px solid rgba(15,23,42,.06);
    border-radius: var(--radius-lg);
    padding: 2rem 1.75rem;
    box-shadow: var(--shadow-sm);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    position: relative;
    overflow: hidden;
}
.ab-value-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(249,115,22,.04), transparent 60%);
    opacity: 0;
    transition: opacity .3s ease;
}
.ab-value-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: rgba(249,115,22,.2); }
.ab-value-card:hover::after { opacity: 1; }
.ab-val-icon {
    width: 56px; height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(249,115,22,.14), rgba(251,146,60,.28));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1.25rem;
}
.ab-val-icon-peso {
    font-size: 1.55rem;
    font-weight: 800;
    color: #c2410c;
    font-family: inherit;
    line-height: 1;
}
.ab-value-card h3 {
    color: var(--clr-dark);
    font-size: 1.08rem;
    font-weight: 700;
    margin: 0 0 .65rem;
}
.ab-value-card p {
    color: var(--clr-muted);
    font-size: .94rem;
    line-height: 1.72;
    margin: 0;
}

/* ═══════════ GALLERY STRIP ═══════════ */
.ab-gallery {
    padding: 1rem 0 0;
    background: var(--clr-light);
    overflow: hidden;
}
.ab-gallery-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
}
.ab-gallery-item {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1 / 1;
}
.ab-gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .5s ease;
}
.ab-gallery-item:hover img { transform: scale(1.08); }
.ab-gallery-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(10,18,40,.72) 0%, transparent 55%);
    display: flex;
    align-items: flex-end;
    padding: 1.1rem;
    opacity: 0;
    transition: opacity .3s ease;
}
.ab-gallery-item:hover .ab-gallery-overlay { opacity: 1; }
.ab-gallery-overlay span {
    color: #fff;
    font-size: .88rem;
    font-weight: 600;
}

/* ═══════════ CTA ═══════════ */
.ab-cta {
    padding: 5rem 1.5rem;
    background: var(--clr-white);
}
.ab-cta-inner {
    max-width: 1180px;
    margin-inline: auto;
    background:
        linear-gradient(140deg, rgba(10,18,40,.84) 0%, rgba(20,35,60,.72) 50%, rgba(249,115,22,.22) 100%),
        url('<?= $ctaImage ?>') center/cover no-repeat;
    border-radius: var(--radius-xl);
    padding: 3.5rem;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2rem;
    align-items: center;
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}
.ab-cta-inner::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: var(--clr-brand);
    opacity: .12;
    filter: blur(60px);
}
.ab-cta-copy { position: relative; z-index: 2; }
.ab-cta-copy h2 {
    font-size: clamp(1.7rem, 3vw, 2.5rem);
    font-weight: 900;
    color: #fff;
    letter-spacing: -.03em;
    margin: 0 0 .75rem;
}
.ab-cta-copy p {
    color: rgba(255,255,255,.72);
    font-size: 1rem;
    line-height: 1.72;
    margin: 0;
    max-width: 520px;
}
.ab-cta-actions {
    display: flex;
    flex-direction: column;
    gap: .85rem;
    position: relative;
    z-index: 2;
    flex-shrink: 0;
}

/* ═══════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════ */

/* Tablet 1024px */
@media (max-width: 1024px) {
    .ab-tl-rail { grid-template-columns: repeat(2, 1fr); }
    .ab-tl-rail::before { display: none; }
    .ab-values-grid { grid-template-columns: repeat(2, 1fr); }
    .ab-gallery-grid { grid-template-columns: repeat(2, 1fr); }
    .ab-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .ab-stats-band { margin-top: 0; }
}

/* Tablet 900px */
@media (max-width: 900px) {
    .ab-hero-grid { grid-template-columns: 1fr; gap: 2rem; }
    .ab-hero { min-height: unset; padding: 5rem 1.5rem 4rem; }
    .ab-hero-copy h1 { font-size: clamp(2.1rem, 7vw, 3.2rem); }
    .ab-hero-copy p { font-size: 1rem; }
    .ab-story-grid { grid-template-columns: 1fr; gap: 2.5rem; }
    .ab-mosaic { grid-template-rows: 220px 160px; }
    .ab-cta-inner { grid-template-columns: 1fr; gap: 1.75rem; }
    .ab-cta-actions { flex-direction: row; flex-wrap: wrap; }
}

/* Mobile 640px */
@media (max-width: 640px) {
    .ab-hero { padding: 4.5rem 1rem 3.5rem; }
    .ab-hero-copy h1 { font-size: clamp(1.9rem, 8vw, 2.7rem); }
    .ab-hero-copy p { font-size: .97rem; }
    .ab-hero-actions { flex-direction: column; }
    .ab-btn { justify-content: center; }
    .ab-hero-card > img { height: 200px; }
    .ab-stats-band { padding: 0 1rem 2rem; }
    .ab-stats-grid { gap: .75rem; }
    .ab-stat-card { padding: 1.25rem 1.1rem; }
    .ab-stat-card strong { font-size: 1.9rem; }
    .ab-story { padding: 3.5rem 1rem; }
    .ab-mosaic { grid-template-columns: 1fr 1fr; grid-template-rows: 200px 140px; }
    .ab-mosaic-main { grid-column: 1 / 3; }
    .ab-timeline { padding: 3rem 1rem; }
    .ab-tl-rail { grid-template-columns: 1fr 1fr; gap: 1rem; }
    .ab-values { padding: 3.5rem 1rem; }
    .ab-values-grid { grid-template-columns: 1fr; gap: 1rem; }
    .ab-value-card { padding: 1.5rem 1.25rem; }
    .ab-gallery-grid { grid-template-columns: repeat(2, 1fr); }
    .ab-cta { padding: 3rem 1rem; }
    .ab-cta-inner { padding: 2rem 1.5rem; border-radius: 22px; }
    .ab-cta-copy h2 { font-size: clamp(1.5rem, 6vw, 2rem); }
    .ab-cta-actions { flex-direction: column; }
}

/* Small mobile 400px */
@media (max-width: 420px) {
    .ab-hero { padding: 3.5rem .9rem 3rem; }
    .ab-hero-eyebrow { font-size: .7rem; }
    .ab-hero-copy h1 { font-size: clamp(1.7rem, 9.5vw, 2.4rem); }
    .ab-stats-grid { grid-template-columns: 1fr 1fr; }
    .ab-tl-rail { grid-template-columns: 1fr; }
    .ab-gallery-grid { grid-template-columns: repeat(2, 1fr); }
    .ab-mosaic { grid-template-rows: 180px 120px; }
    .ab-hero-card > img { height: 170px; }
}
.about-page {
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 24%, #f8fafc 100%);
}
.about-hero {
    position: relative;
    overflow: hidden;
    background:
        linear-gradient(120deg, rgba(15, 23, 42, .88), rgba(27, 42, 74, .78)),
        url('<?= $heroImage ?>') center/cover no-repeat;
    padding: 5rem 1.5rem 4.25rem;
}
.about-hero::after {
    content: '';
    position: absolute;
    inset: auto 0 -1px 0;
    height: 200px;
    background: linear-gradient(180deg, transparent 0%, rgba(255,255,255,.5) 45%, rgba(255,255,255,.9) 75%, #ffffff 100%);
    pointer-events: none;
}
.about-hero-inner,
.about-section,
.about-values,
.about-cta-inner {
    max-width: 1120px;
    margin: 0 auto;
}
.about-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr .85fr;
    gap: 2rem;
    align-items: center;
    position: relative;
    z-index: 1;
}
.about-kicker {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .45rem .8rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    color: #fff;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 1rem;
}
.about-kicker::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f97316;
}
.about-hero-copy h1 {
    color: #fff;
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    line-height: 1.05;
    margin: 0 0 1rem;
    font-weight: 800;
    letter-spacing: -.03em;
}
.about-hero-copy p {
    color: rgba(255,255,255,.82);
    font-size: 1.05rem;
    line-height: 1.75;
    max-width: 680px;
    margin: 0 0 1.5rem;
}
.about-hero-actions {
    display: flex;
    gap: .9rem;
    flex-wrap: wrap;
}
.about-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: .9rem 1.35rem;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}
.about-btn:hover {
    transform: translateY(-2px);
}
.about-btn-primary {
    background: #f97316;
    color: #fff;
    box-shadow: 0 16px 32px rgba(249,115,22,.28);
}
.about-btn-primary:hover {
    background: #ea580c;
}
.about-btn-secondary {
    background: rgba(255,255,255,.1);
    color: #fff;
    border: 1px solid rgba(255,255,255,.16);
}
.about-highlight-card {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 24px;
    padding: 1.5rem;
    backdrop-filter: blur(8px);
    box-shadow: 0 24px 50px rgba(15,23,42,.28);
}
.about-highlight-card img {
    width: 100%;
    height: 260px;
    object-fit: cover;
    border-radius: 18px;
    display: block;
    margin-bottom: 1rem;
}
.about-highlight-card h2 {
    color: #fff;
    margin: 0 0 .5rem;
    font-size: 1.15rem;
}
.about-highlight-card p {
    color: rgba(255,255,255,.76);
    margin: 0;
    line-height: 1.7;
    font-size: .95rem;
}
.about-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    margin: -2rem auto 0;
    padding: 0 1.5rem;
    position: relative;
    z-index: 3;
}
.about-stat {
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 20px;
    padding: 1.4rem 1.2rem;
    box-shadow: 0 18px 40px rgba(15,23,42,.08);
}
.about-stat strong {
    display: block;
    font-size: 1.8rem;
    color: #0f172a;
    margin-bottom: .3rem;
}
.about-stat span {
    color: #475569;
    font-size: .92rem;
    line-height: 1.5;
}
.about-section {
    display: grid;
    grid-template-columns: .95fr 1.05fr;
    gap: 2rem;
    padding: 4rem 1.5rem 2rem;
    align-items: center;
}
.about-section-media {
    position: relative;
    display: grid;
    grid-template-rows: auto auto;
    gap: .75rem;
}
.about-section-media .about-media-main {
    position: relative;
}
.about-section-media img {
    width: 100%;
    border-radius: 20px;
    object-fit: cover;
    box-shadow: 0 18px 40px rgba(15,23,42,.11);
    display: block;
}
.about-section-media .about-media-main img {
    min-height: 300px;
}
.about-media-thumbs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .75rem;
}
.about-media-thumbs img {
    height: 160px;
    max-height: 160px;
    object-fit: cover;
    transition: transform .3s ease, box-shadow .3s ease;
}
.about-media-thumbs img:hover {
    transform: scale(1.03);
    box-shadow: 0 22px 48px rgba(15,23,42,.16);
}
/* Reveal up variant for individual images */
.reveal-up {
    opacity: 0;
    transform: translate3d(0, 36px, 0);
    filter: blur(6px);
    transition: opacity .65s ease, transform .65s ease, filter .65s ease;
    will-change: opacity, transform, filter;
}
.reveal-up.is-visible {
    opacity: 1;
    transform: translate3d(0, 0, 0);
    filter: blur(0);
}
.stagger-1 { transition-delay: .05s; }
.stagger-2 { transition-delay: .15s; }
.stagger-3 { transition-delay: .25s; }
.stagger-4 { transition-delay: .35s; }
.about-badge {
    position: absolute;
    right: 1.25rem;
    bottom: 1.25rem;
    background: #fff;
    border-radius: 18px;
    padding: .9rem 1rem;
    box-shadow: 0 20px 40px rgba(15,23,42,.12);
    min-width: 180px;
}
.about-badge strong {
    display: block;
    color: #0f172a;
    font-size: 1.1rem;
    margin-bottom: .15rem;
}
.about-badge span {
    color: #64748b;
    font-size: .9rem;
}
.about-section-copy {
    text-align: center;
}
.about-section-copy h2,
.about-values-header h2,
.about-cta-copy h2 {
    font-size: clamp(1.8rem, 3vw, 2.6rem);
    line-height: 1.15;
    margin: 0 0 1rem;
    color: #0f172a;
    letter-spacing: -.02em;
}
.about-section-copy p,
.about-values-header p,
.about-cta-copy p {
    color: #475569;
    line-height: 1.8;
    font-size: 1rem;
    margin: 0 0 1rem;
}
.about-checklist {
    display: grid;
    gap: .85rem;
    margin-top: 1.25rem;
}
.about-checklist-item {
    display: flex;
    gap: .8rem;
    align-items: flex-start;
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 16px;
    padding: .95rem 1rem;
}
.about-checklist-item i {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(249,115,22,.12);
    color: #f97316;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-style: normal;
    font-weight: 800;
    flex-shrink: 0;
}
.about-checklist-item strong {
    display: block;
    color: #0f172a;
    margin-bottom: .15rem;
}
.about-checklist-item span {
    color: #64748b;
    font-size: .95rem;
    line-height: 1.6;
}
.about-values {
    padding: 1rem 1.5rem 4rem;
}
.about-values-header {
    max-width: 760px;
    margin: 0 auto 1.5rem;
    text-align: center;
}
.about-values-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}
.about-value-card {
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 18px 38px rgba(15,23,42,.06);
    text-align: center;
}
.about-value-card .icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(249,115,22,.16), rgba(251,146,60,.3));
    color: #c2410c;
    font-size: 1.3rem;
    font-weight: 800;
    margin-bottom: 1rem;
    margin-left: auto;
    margin-right: auto;
}
.about-value-card h3 {
    color: #0f172a;
    margin: 0 0 .55rem;
    font-size: 1.1rem;
}
.about-value-card p {
    color: #64748b;
    margin: 0;
    line-height: 1.7;
    font-size: .96rem;
}
.about-cta {
    padding: 0 1.5rem 4rem;
}
.about-cta-inner {
    background:
        linear-gradient(135deg, rgba(15,23,42,.76) 0%, rgba(30,41,59,.66) 52%, rgba(51,65,85,.58) 100%),
        url('<?= $ctaImage ?>') center/cover no-repeat;
    border-radius: 28px;
    padding: 2.2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    box-shadow: 0 24px 50px rgba(27,42,74,.22);
}
.about-cta-copy h2,
.about-cta-copy p {
    color: #fff;
}
.about-cta-copy p {
    color: rgba(255,255,255,.78);
    margin-bottom: 0;
}

/* ── Tablet (≤ 991px) ── */
@media (max-width: 991px) {
    .about-hero-grid,
    .about-section,
    .about-cta-inner,
    .about-values-grid {
        grid-template-columns: 1fr;
    }
    .about-stats {
        grid-template-columns: repeat(2, 1fr);
        margin: -1.5rem auto 0;
        padding: 0 1.25rem;
    }
    .about-highlight-card img {
        height: 200px;
    }
    .about-cta-inner {
        flex-direction: column;
        align-items: flex-start;
        gap: 1.25rem;
    }
    .about-section {
        padding: 2.5rem 1.25rem 1.5rem;
        gap: 1.5rem;
    }
    .about-values {
        padding: 1rem 1.25rem 3rem;
    }
    .about-cta {
        padding: 0 1.25rem 3rem;
    }
    .about-section-copy {
        text-align: left;
    }
    .about-values-header {
        text-align: left;
        margin-bottom: 1rem;
    }
}

/* ── Mobile (≤ 640px) ── */
@media (max-width: 640px) {
    .about-hero {
        padding: 4rem 1rem 3.5rem;
    }
    .about-hero-grid {
        gap: 1.25rem;
    }
    .about-hero-copy h1 {
        font-size: clamp(1.85rem, 8vw, 2.4rem);
    }
    .about-hero-copy p {
        font-size: .97rem;
        line-height: 1.7;
        margin-bottom: 1.25rem;
    }
    .about-hero-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .75rem;
    }
    .about-btn {
        justify-content: center;
        padding: .8rem 1rem;
        font-size: .92rem;
    }
    .about-highlight-card {
        padding: 1.1rem;
        border-radius: 18px;
    }
    .about-highlight-card img {
        height: 180px;
        border-radius: 14px;
    }
    .about-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: .75rem;
        padding: 0 1rem;
        margin: -1rem auto 0;
    }
    .about-stat {
        padding: 1.1rem 1rem;
        border-radius: 16px;
    }
    .about-stat strong {
        font-size: 1.5rem;
    }
    .about-stat span {
        font-size: .85rem;
    }
    .about-section {
        padding: 2rem 1rem 1.25rem;
        gap: 1.25rem;
    }
    .about-section-media img {
        border-radius: 16px;
    }
    .about-section-media .about-media-main img {
        min-height: 220px;
        object-fit: cover;
    }
    .about-media-thumbs {
        grid-template-columns: 1fr 1fr;
        gap: .6rem;
    }
    .about-media-thumbs img {
        height: 130px;
        max-height: 130px;
        object-fit: cover;
        border-radius: 14px;
    }
    .about-badge {
        position: static;
        margin-top: .75rem;
        border-radius: 14px;
        padding: .75rem .9rem;
        min-width: unset;
    }
    .about-badge strong {
        font-size: 1rem;
    }
    .about-section-copy {
        text-align: left;
    }
    .about-section-copy h2,
    .about-values-header h2,
    .about-cta-copy h2 {
        font-size: clamp(1.5rem, 6vw, 2rem);
    }
    .about-section-copy p,
    .about-values-header p,
    .about-value-card p {
        font-size: .93rem;
        line-height: 1.7;
    }
    .about-checklist-item {
        padding: .8rem .9rem;
        border-radius: 13px;
    }
    .about-values {
        padding: 1rem 1rem 2.5rem;
    }
    .about-values-grid {
        gap: .75rem;
    }
    .about-value-card {
        padding: 1.2rem;
        border-radius: 18px;
        text-align: left;
    }
    .about-value-card .icon {
        margin-left: 0;
        margin-right: 0;
        width: 46px;
        height: 46px;
    }
    .about-cta {
        padding: 0 1rem 2.5rem;
    }
    .about-cta-inner {
        padding: 1.5rem 1.2rem;
        border-radius: 20px;
        gap: 1.1rem;
    }
    .about-cta-copy h2 {
        font-size: clamp(1.35rem, 5.5vw, 1.8rem);
        margin-bottom: .6rem;
    }
    .about-hero-actions .about-btn-secondary {
        background: rgba(255,255,255,.13);
    }
}

/* ═══════════════════════════════════════════════
   ABOUT PAGE — 2026 POLISH LAYER
   Full-page visual upgrade without changing global styles
   ═══════════════════════════════════════════════ */
.about-page {
    --clr-brand: #f97316;
    --clr-brand-dk: #ea580c;
    --clr-dark: #0b1220;
    --clr-mid: #25344d;
    --clr-muted: #64748b;
    --clr-line: rgba(15, 23, 42, .09);
    --clr-soft: #f8fafc;
    --shadow-sm: 0 12px 30px rgba(15, 23, 42, .07);
    --shadow-md: 0 20px 48px rgba(15, 23, 42, .11);
    --shadow-lg: 0 32px 80px rgba(15, 23, 42, .17);
    background:
        radial-gradient(circle at 8% 10%, rgba(249, 115, 22, .08), transparent 26rem),
        radial-gradient(circle at 92% 42%, rgba(59, 130, 246, .07), transparent 28rem),
        linear-gradient(180deg, #ffffff 0%, #f8fafc 46%, #ffffff 100%) !important;
    color: #0f172a;
}
.about-page * { box-sizing: border-box; }
.ab-wrap { max-width: min(1180px, calc(100vw - 32px)); padding-inline: 0; }
.ab-eyebrow {
    padding: .45rem .75rem;
    border: 1px solid rgba(249, 115, 22, .16);
    border-radius: 999px;
    background: rgba(249, 115, 22, .08);
    letter-spacing: .12em;
}
.ab-eyebrow::before { width: 8px; height: 8px; border-radius: 50%; }

/* Hero */
.ab-hero {
    isolation: isolate;
    min-height: clamp(620px, calc(100svh - 128px), 780px) !important;
    align-items: flex-start !important;
    padding: clamp(2.4rem, 4.5vw, 3.6rem) 1rem clamp(4.5rem, 8vw, 6.75rem) !important;
    background:
        linear-gradient(112deg, rgba(8, 13, 25, .96) 0%, rgba(12, 24, 47, .9) 43%, rgba(15, 23, 42, .72) 100%),
        url('<?= $heroImage ?>') center/cover no-repeat !important;
}
.ab-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
    background-size: 56px 56px;
    mask-image: linear-gradient(90deg, rgba(0,0,0,.8), transparent 72%);
    pointer-events: none;
    z-index: 0;
}
.ab-hero::after {
    height: 210px !important;
    background: linear-gradient(to bottom, transparent 0%, rgba(248,250,252,.72) 60%, #f8fafc 100%) !important;
}
.ab-hero-grid {
    grid-template-columns: minmax(0, 1.05fr) minmax(360px, .72fr) !important;
    gap: clamp(2rem, 5vw, 4.25rem) !important;
    align-items: start !important;
}
.ab-hero-eyebrow {
    box-shadow: inset 0 1px 0 rgba(255,255,255,.12), 0 18px 44px rgba(0,0,0,.16);
}
.ab-hero-copy-h1 {
    max-width: 700px;
    text-wrap: balance;
    text-shadow: 0 16px 44px rgba(0,0,0,.34);
}
.ab-hero-lead {
    max-width: 610px !important;
    color: rgba(255,255,255,.84) !important;
    font-size: clamp(1rem, 1.4vw, 1.16rem) !important;
}
.ab-hero-actions { gap: .85rem !important; }
.ab-btn {
    min-height: 48px;
    border-radius: 999px !important;
    box-shadow: none;
}
.ab-btn-primary {
    background: linear-gradient(135deg, #fb923c 0%, #f97316 48%, #ea580c 100%) !important;
    color: #fff !important;
    box-shadow: 0 18px 42px rgba(249,115,22,.34) !important;
}
.ab-btn-ghost {
    background: rgba(255,255,255,.1) !important;
    border: 1px solid rgba(255,255,255,.24) !important;
    color: #fff !important;
}
.ab-btn-dark { border-radius: 999px !important; }
.ab-hero-trust {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
    margin-top: 1.35rem;
}
.ab-hero-trust span {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .58rem .78rem;
    border-radius: 999px;
    background: rgba(255,255,255,.09);
    border: 1px solid rgba(255,255,255,.13);
    color: rgba(255,255,255,.78);
    font-size: .84rem;
    font-weight: 700;
    backdrop-filter: blur(10px);
}
.ab-hero-trust svg { color: #fb923c; flex: 0 0 auto; }

/* Hero showroom card */
.ab-hero-card {
    position: relative;
    padding: 1rem !important;
    border-radius: 34px !important;
    background: linear-gradient(180deg, rgba(255,255,255,.16), rgba(255,255,255,.07)) !important;
    border: 1px solid rgba(255,255,255,.18) !important;
    box-shadow: 0 28px 90px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.18);
    transform: translateZ(0);
}
.ab-hero-card::before {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(145deg, rgba(255,255,255,.45), rgba(249,115,22,.3), rgba(255,255,255,.04));
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}
.ab-card-image { position: relative; margin-bottom: 1.2rem; }
.ab-card-image img {
    width: 100%;
    height: clamp(260px, 28vw, 350px);
    object-fit: cover;
    border-radius: 26px;
    display: block;
}
.ab-card-badge {
    position: absolute;
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
    padding: .78rem .9rem;
    border-radius: 18px;
    background: rgba(11,18,32,.76);
    border: 1px solid rgba(255,255,255,.16);
    backdrop-filter: blur(14px);
    box-shadow: 0 18px 34px rgba(0,0,0,.22);
}
.ab-card-badge strong,
.ab-card-badge span { display: block; }
.ab-card-badge strong { color: #fff; font-size: .92rem; }
.ab-card-badge span { color: rgba(255,255,255,.68); font-size: .8rem; }
.ab-hero-card h2,
.ab-hero-card p,
.ab-hero-card-meta { margin-inline: .5rem; }
.ab-hero-card h2 { font-size: clamp(1.05rem, 1.3vw, 1.28rem) !important; }
.ab-hero-meta-chip {
    border-radius: 18px !important;
    background: rgba(255,255,255,.1) !important;
    transition: transform .22s ease, background .22s ease;
}
.ab-hero-meta-chip:hover { transform: translateY(-3px); background: rgba(255,255,255,.15) !important; }

/* Stats */
.ab-stats-band {
    background: transparent !important;
    padding: 0 1rem 4.75rem !important;
    margin-top: -46px !important;
}
.ab-stats-grid {
    position: relative;
    z-index: 5;
    max-width: 1180px !important;
    gap: 1rem !important;
}
.ab-stat-card {
    min-height: 196px;
    border-radius: 28px !important;
    border: 1px solid rgba(255,255,255,.74) !important;
    background: rgba(255,255,255,.84) !important;
    box-shadow: 0 22px 54px rgba(15,23,42,.11) !important;
    backdrop-filter: blur(18px);
}
.ab-stat-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(249,115,22,.16), transparent 48%);
    pointer-events: none;
}
.ab-stat-icon,
.ab-val-icon,
.ab-promise-icon {
    box-shadow: inset 0 1px 0 rgba(255,255,255,.65), 0 16px 30px rgba(249,115,22,.17);
}
.ab-stat-card strong { font-size: clamp(2rem, 3.5vw, 2.65rem) !important; }

/* Story */
.ab-story {
    padding: 6rem 1rem 5.5rem !important;
    background: transparent !important;
}
.ab-story-shell {
    width: min(1040px, 100%);
    margin: 0 auto;
    text-align: center;
}
.ab-story-header {
    margin-bottom: 2rem;
}
.ab-story-kicker {
    display: inline-block;
    margin-bottom: 1rem;
    color: var(--clr-dark);
    font-size: clamp(2rem, 4vw, 3.25rem);
    font-weight: 900;
    letter-spacing: -.04em;
    line-height: 1;
    text-transform: uppercase;
}
.ab-story-kicker span {
    color: var(--clr-brand);
}
.ab-story-grid {
    display: block !important;
}
.ab-mosaic {
    display: none !important;
}
.ab-mosaic-main::after,
.ab-gallery-item::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(11,18,32,.28), transparent 58%);
    pointer-events: none;
}
.ab-story-copy {
    position: relative;
    width: min(980px, 100%);
    margin: 0 auto;
    padding: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
}
.ab-story-copy .ab-eyebrow {
    display: none !important;
}
.ab-story-copy h2 {
    text-wrap: balance;
    max-width: 10ch;
    margin: 0 auto 1.5rem !important;
    font-size: clamp(2.4rem, 5.5vw, 4.4rem) !important;
    line-height: .95 !important;
    letter-spacing: -.06em !important;
}
.ab-story-copy p {
    max-width: 960px;
    margin: 0 auto;
    color: #475569 !important;
    font-size: clamp(.98rem, 1.4vw, 1.12rem) !important;
    line-height: 1.9 !important;
}
.ab-story-copy p + p {
    margin-top: 2rem;
}
.ab-story-copy p:last-of-type {
    margin-bottom: 0;
}

/* Service promise */
.ab-promise {
    padding: 0 1rem 5.25rem;
    background: transparent;
}
.ab-promise-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}
.ab-promise-card {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    padding: 1.45rem;
    border-radius: 28px;
    background: linear-gradient(180deg, #fff, #f8fafc);
    border: 1px solid rgba(15,23,42,.07);
    box-shadow: 0 18px 44px rgba(15,23,42,.07);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}
.ab-promise-card:hover {
    transform: translateY(-6px);
    border-color: rgba(249,115,22,.24);
    box-shadow: 0 28px 64px rgba(15,23,42,.12);
}
.ab-promise-icon {
    flex: 0 0 auto;
    width: 52px;
    height: 52px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    color: #c2410c;
    background: linear-gradient(135deg, rgba(249,115,22,.13), rgba(251,146,60,.28));
}
.ab-promise-card span {
    display: block;
    margin-bottom: .25rem;
    color: #f97316;
    font-size: .74rem;
    font-weight: 900;
    letter-spacing: .1em;
    text-transform: uppercase;
}
}
.ab-tl-rail::before { top: 50px !important; opacity: .35 !important; }
.ab-tl-dot { box-shadow: 0 0 0 8px rgba(249,115,22,.13), 0 16px 34px rgba(249,115,22,.28) !important; }
.ab-tl-item p { color: rgba(255,255,255,.64) !important; }

/* Values */
.ab-values {
    padding: 5.5rem 1rem !important;
    background: transparent !important;
}
.ab-values-header h2 { text-wrap: balance; }
.ab-values-grid { gap: 1rem !important; }
.ab-value-card {
    border-radius: 28px !important;
    background: rgba(255,255,255,.9) !important;
    border: 1px solid rgba(15,23,42,.07) !important;
    box-shadow: 0 16px 38px rgba(15,23,42,.06) !important;
}
.ab-value-card h3 { position: relative; z-index: 1; }
.ab-value-card p { position: relative; z-index: 1; color: #64748b !important; }

/* Gallery and CTA */
.ab-gallery {
    padding: 0 1rem !important;
    background: transparent !important;
}
.ab-gallery-grid {
    max-width: 1180px;
    margin-inline: auto;
    border-radius: 34px;
    overflow: hidden;
    box-shadow: 0 28px 70px rgba(15,23,42,.15);
}
.ab-gallery-item { aspect-ratio: 1.25 / 1 !important; }
.ab-gallery-overlay {
    opacity: 1 !important;
    background: linear-gradient(to top, rgba(11,18,32,.76) 0%, rgba(11,18,32,.08) 70%) !important;
    z-index: 2;
}
.ab-gallery-overlay span {
    position: relative;
    z-index: 1;
    padding: .45rem .7rem;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.16);
    backdrop-filter: blur(10px);
}
.ab-cta {
    padding: 5.5rem 1rem 6rem !important;
    background: transparent !important;
}
.ab-cta-inner {
    border-radius: 36px !important;
    border: 1px solid rgba(255,255,255,.16);
    box-shadow: 0 32px 86px rgba(15,23,42,.2) !important;
}
.ab-cta-copy h2 { text-wrap: balance; }

@media (max-width: 1024px) {
    .ab-hero-grid { grid-template-columns: 1fr !important; }
    .ab-hero { min-height: auto !important; }
    .ab-hero-card { max-width: 720px; }
    .ab-promise-grid { grid-template-columns: 1fr; }
    .ab-promise-card { align-items: center; }
}
@media (max-width: 900px) {
    .ab-story-copy { padding: 1.25rem; }
    .ab-timeline { width: min(100% - 24px, 1180px); border-radius: 28px; padding: 3.5rem 1rem !important; }
}
@media (max-width: 640px) {
    .ab-wrap { max-width: calc(100vw - 24px); }
    .ab-hero { padding: 3.5rem .75rem 4rem !important; }
    .ab-hero-copy-h1 { font-size: clamp(2rem, 11vw, 3rem) !important; }
    .ab-hero-trust { display: grid; grid-template-columns: 1fr; }
    .ab-hero-card { border-radius: 26px !important; }
    .ab-card-image img { height: 230px; border-radius: 20px; }
    .ab-card-badge { position: static; margin-top: .75rem; }
    .ab-hero-card-meta { display: grid !important; grid-template-columns: 1fr; }
    .ab-stats-band { margin-top: -24px !important; padding-bottom: 3.25rem !important; }
    .ab-stats-grid { grid-template-columns: 1fr !important; }
    .ab-stat-card { min-height: auto; }
    .ab-story { padding-block: 3.75rem !important; }
    .ab-mosaic { grid-template-rows: 220px 140px !important; }
    .ab-promise { padding-bottom: 3.75rem; }
    .ab-promise-card { flex-direction: column; align-items: flex-start; padding: 1.25rem; border-radius: 22px; }
    .ab-tl-rail { grid-template-columns: 1fr !important; }
    .ab-values { padding-block: 3.75rem !important; }
    .ab-gallery-grid { grid-template-columns: 1fr !important; border-radius: 24px; }
    .ab-gallery-item { aspect-ratio: 16 / 10 !important; }
    .ab-cta { padding-block: 3.75rem 4.25rem !important; }
    .ab-cta-inner { padding: 1.5rem !important; border-radius: 26px !important; }
}

/* Extra-small phones: keep every About section compact and unclipped */
@media (max-width: 430px) {
    .about-page {
        overflow-x: hidden;
    }
    .ab-wrap,
    .ab-hero .ab-wrap {
        width: 100% !important;
        max-width: 100% !important;
    }
    .ab-hero {
        padding: 2.25rem .55rem 2.75rem !important;
    }
    .ab-hero-grid {
        gap: 1.1rem !important;
    }
    .ab-hero-eyebrow {
        max-width: 100%;
        font-size: .62rem !important;
        line-height: 1.25;
        padding: .38rem .72rem .38rem .4rem !important;
        white-space: normal;
    }
    .ab-hero-eyebrow-dot {
        width: 24px !important;
        height: 24px !important;
    }
    .ab-hero-copy-h1 {
        font-size: clamp(1.75rem, 10.5vw, 2.35rem) !important;
        margin-bottom: .8rem !important;
    }
    .ab-hero-lead {
        font-size: .9rem !important;
        line-height: 1.6 !important;
        margin-bottom: 1.15rem !important;
    }
    .ab-btn {
        min-height: 42px;
        padding: .72rem 1rem !important;
        font-size: .82rem !important;
    }
    .ab-hero-trust span {
        padding: .5rem .65rem;
        font-size: .76rem;
    }
    .ab-hero-card {
        width: 100%;
        padding: .62rem !important;
        border-radius: 22px !important;
    }
    .ab-card-image {
        margin-bottom: .8rem;
    }
    .ab-card-image img {
        height: 195px;
        border-radius: 16px;
    }
    .ab-card-badge {
        margin-top: .55rem;
        padding: .65rem .72rem;
        border-radius: 13px;
    }
    .ab-card-badge strong {
        font-size: .76rem;
    }
    .ab-card-badge span {
        font-size: .66rem;
    }
    .ab-hero-card h2,
    .ab-hero-card p,
    .ab-hero-card-meta {
        margin-inline: .2rem;
    }
    .ab-hero-card h2 {
        font-size: .88rem !important;
        line-height: 1.35;
        margin-bottom: .45rem !important;
    }
    .ab-hero-card p {
        font-size: .76rem !important;
        line-height: 1.58 !important;
        margin-bottom: .85rem !important;
    }
    .ab-hero-card-meta {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: .42rem !important;
    }
    .ab-hero-meta-chip {
        min-width: 0;
        padding: .55rem .25rem !important;
        border-radius: 12px !important;
    }
    .ab-hero-meta-chip strong {
        font-size: .86rem !important;
        margin-bottom: .12rem !important;
    }
    .ab-hero-meta-chip span {
        font-size: .54rem !important;
        line-height: 1.2;
    }
    .ab-stats-band,
    .ab-story,
    .ab-promise,
    .ab-values,
    .ab-gallery,
    .ab-cta {
        padding-left: .75rem !important;
        padding-right: .75rem !important;
    }
    .ab-stats-grid,
    .ab-values-grid,
    .ab-promise-grid {
        grid-template-columns: 1fr !important;
    }
    .ab-stat-card,
    .ab-value-card,
    .ab-promise-card,
    .ab-story-copy {
        border-radius: 20px !important;
    }
    .ab-mosaic {
        grid-template-rows: 185px 112px !important;
        gap: .6rem !important;
    }
    .ab-mosaic-badge {
        left: .65rem;
        right: .65rem;
        bottom: .65rem;
        padding: .6rem .7rem;
    }
    .ab-timeline {
        width: calc(100vw - 20px);
        border-radius: 22px;
    }
    .ab-gallery-grid {
        border-radius: 20px;
    }
}

    /* Final premium refinements */
    .ab-hero-copy-block {
        display: grid;
        gap: 0;
        align-content: start;
    }

    .ab-hero-card {
        align-self: start;
    }

    .ab-hero-mini-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .9rem;
        margin-top: 1.5rem;
        max-width: 720px;
    }

    .ab-hero-mini-stat {
        padding: 1rem 1rem .95rem;
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(255,255,255,.15), rgba(255,255,255,.06));
        border: 1px solid rgba(255,255,255,.16);
        box-shadow: 0 18px 36px rgba(0,0,0,.14);
        backdrop-filter: blur(12px);
    }

    .ab-hero-mini-stat strong {
        display: block;
        color: #fff;
        font-size: 1.2rem;
        font-weight: 800;
        margin-bottom: .3rem;
    }

    .ab-hero-mini-stat span {
        display: block;
        color: rgba(255,255,255,.72);
        line-height: 1.45;
        font-size: .82rem;
    }

    .ab-hero-card-topline {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .42rem .8rem;
        border-radius: 999px;
        margin: 0 .5rem .9rem;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.14);
        color: rgba(255,255,255,.78);
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .ab-hero-card-topline::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #fb923c;
        box-shadow: 0 0 0 5px rgba(251,146,60,.18);
    }

    .ab-hero-card-stack {
        display: grid;
        gap: .75rem;
        margin-top: 1rem;
    }

    .ab-hero-note {
        padding: .95rem 1rem;
        border-radius: 20px;
        background: rgba(7, 12, 23, .32);
        border: 1px solid rgba(255,255,255,.1);
    }

    .ab-hero-note span {
        display: block;
        margin-bottom: .35rem;
        color: #fb923c;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .ab-hero-note strong {
        display: block;
        color: rgba(255,255,255,.84);
        font-size: .92rem;
        line-height: 1.55;
    }

    .ab-showcase-strip {
        padding: 0 1rem 4.5rem;
    }

    .ab-showcase-shell {
        max-width: 1180px;
        margin: -1.25rem auto 0;
        padding: 1.35rem;
        border-radius: 34px;
        background: linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.76));
        border: 1px solid rgba(255,255,255,.8);
        box-shadow: 0 28px 80px rgba(15,23,42,.12);
        backdrop-filter: blur(20px);
    }

    .ab-showcase-head {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr);
        gap: 1.5rem;
        align-items: end;
        margin-bottom: 1.5rem;
    }

    .ab-showcase-head h2 {
        margin: 0;
        color: var(--clr-dark);
        font-size: clamp(1.7rem, 3vw, 2.5rem);
        line-height: 1.08;
        letter-spacing: -.04em;
        text-wrap: balance;
    }

    .ab-showcase-head p {
        margin: 0;
        color: #64748b;
        line-height: 1.75;
    }

    .ab-showcase-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .ab-showcase-card {
        position: relative;
        padding: 1.4rem;
        border-radius: 28px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid rgba(15,23,42,.07);
        box-shadow: 0 16px 42px rgba(15,23,42,.07);
        overflow: hidden;
    }

    .ab-showcase-card::after {
        content: '';
        position: absolute;
        inset: auto -40px -60px auto;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(249,115,22,.18), transparent 70%);
    }

    .ab-showcase-kicker {
        display: inline-flex;
        margin-bottom: .85rem;
        padding: .38rem .72rem;
        border-radius: 999px;
        background: rgba(249,115,22,.1);
        color: #c2410c;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .ab-showcase-card h3 {
        position: relative;
        z-index: 1;
        margin: 0 0 .6rem;
        color: var(--clr-dark);
        font-size: 1.1rem;
        line-height: 1.38;
    }

    .ab-showcase-card p {
        position: relative;
        z-index: 1;
        margin: 0;
        color: #64748b;
        line-height: 1.72;
    }

    .ab-stats-intro {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(300px, .8fr);
        gap: 1.5rem;
        align-items: end;
        max-width: 1180px;
        margin: 0 auto 1.3rem;
    }

    .ab-stats-intro h2 {
        margin: 0;
        color: var(--clr-dark);
        font-size: clamp(1.65rem, 2.8vw, 2.3rem);
        line-height: 1.12;
        letter-spacing: -.04em;
        text-wrap: balance;
    }

    .ab-stats-intro p {
        margin: 0;
        color: #64748b;
        line-height: 1.72;
    }

    .ab-story-note {
        margin-top: 1.4rem;
        padding: 1rem 1.1rem;
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(249,115,22,.08), rgba(249,115,22,.03));
        border: 1px solid rgba(249,115,22,.12);
    }

    .ab-story-note strong {
        display: block;
        margin-bottom: .35rem;
        color: var(--clr-dark);
        font-size: 1rem;
    }

    .ab-story-note p {
        margin: 0;
        color: #64748b;
        font-size: .94rem;
        line-height: 1.7;
    }

    .ab-story-copy {
        padding: clamp(2rem, 3.2vw, 3rem) !important;
        border-radius: 34px !important;
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.94)) !important;
        border: 1px solid rgba(15,23,42,.07) !important;
        box-shadow: 0 24px 65px rgba(15,23,42,.08) !important;
    }

    .ab-story-copy .ab-eyebrow {
        width: fit-content;
        margin-bottom: 1.25rem;
    }

    .ab-story-copy h2 {
        margin: 0 0 1.35rem !important;
        font-size: clamp(2.15rem, 4.3vw, 4rem) !important;
        line-height: .98 !important;
        letter-spacing: -.05em !important;
        max-width: 12ch;
    }

    .ab-story-copy p {
        margin: 0;
        color: #64748b !important;
        font-size: 1.02rem !important;
        line-height: 1.9 !important;
        max-width: 62ch;
    }

    .ab-story-copy p + p {
        margin-top: 1rem;
    }

    .ab-story-pillars {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        margin-top: 1rem;
    }

    .ab-story-pillars span,
    .ab-cta-points span {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .6rem .82rem;
        border-radius: 999px;
        background: rgba(15,23,42,.04);
        border: 1px solid rgba(15,23,42,.08);
        color: var(--clr-dark);
        font-size: .83rem;
        font-weight: 700;
    }

    .ab-story-pillars span::before,
    .ab-cta-points span::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--clr-brand);
        box-shadow: 0 0 0 4px rgba(249,115,22,.15);
    }

    .ab-gallery-head {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(280px, .82fr);
        gap: 1.5rem;
        align-items: end;
        max-width: 1180px;
        margin: 0 auto 1.5rem;
    }

    .ab-gallery-head h2 {
        margin: 0;
        color: var(--clr-dark);
        font-size: clamp(1.7rem, 3vw, 2.35rem);
        line-height: 1.12;
        letter-spacing: -.04em;
    }

    .ab-gallery-head p {
        margin: 0;
        color: #64748b;
        line-height: 1.72;
    }

    .ab-gallery-overlay {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: flex-start;
        gap: .55rem;
    }

    .ab-gallery-overlay strong {
        position: relative;
        z-index: 1;
        color: #fff;
        font-size: 1.22rem;
        line-height: 1.15;
    }

    .ab-gallery-overlay p {
        position: relative;
        z-index: 1;
        max-width: 24ch;
        margin: 0;
        color: rgba(255,255,255,.82);
        line-height: 1.55;
        font-size: .9rem;
    }

    .ab-cta-copy {
        display: grid;
        gap: 0;
    }

    .ab-cta-points {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
        margin-top: 1.25rem;
    }

    .ab-cta-points span {
        background: rgba(255,255,255,.1);
        border-color: rgba(255,255,255,.16);
        color: rgba(255,255,255,.9);
    }

    .ab-cta-points span::before {
        background: #fb923c;
        box-shadow: 0 0 0 4px rgba(251,146,60,.18);
    }

    @media (max-width: 1024px) {
        .ab-hero-mini-stats,
        .ab-showcase-grid {
            grid-template-columns: 1fr;
        }

        .ab-showcase-head,
        .ab-stats-intro,
        .ab-gallery-head {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .ab-showcase-shell {
            padding: 1rem;
            border-radius: 24px;
        }

        .ab-story {
            padding: 4.5rem 1rem 4rem !important;
        }

        .ab-story-copy h2 {
            max-width: 11ch;
            margin-bottom: 1.15rem !important;
        }

        .ab-story-copy p + p {
            margin-top: 1.35rem;
        }

        .ab-hero-mini-stats {
            gap: .7rem;
        }

        .ab-gallery-overlay strong {
            font-size: 1.05rem;
        }
    }

    @media (max-width: 640px) {
        .ab-hero-mini-stats {
            grid-template-columns: 1fr;
        }

        .ab-showcase-strip {
            padding-inline: .75rem;
        }

        .ab-story-kicker {
            font-size: 2rem;
        }

        .ab-story-copy p {
            font-size: .96rem !important;
            line-height: 1.82 !important;
        }
    }

    /* Reference-style about sections */
    .ab-story {
        padding: 5.5rem 1rem 4.5rem !important;
    }

    .ab-story-shell {
        width: min(980px, 100%);
    }

    .ab-story-kicker {
        margin-bottom: 1.35rem;
        font-size: clamp(2.35rem, 4.2vw, 3.55rem);
    }

    .ab-story-copy {
        width: min(980px, 100%);
    }

    .ab-story-copy h2 {
        max-width: 9ch;
        margin: 0 auto 1.4rem !important;
        font-size: clamp(2.7rem, 5vw, 4.5rem) !important;
    }

    .ab-story-copy p {
        max-width: 1000px;
    }

    .ab-section-title {
        margin: 0 0 .9rem;
        color: var(--clr-dark);
        font-size: clamp(2.2rem, 4vw, 3.25rem);
        font-weight: 900;
        letter-spacing: -.04em;
        line-height: 1;
        text-transform: uppercase;
        text-align: center;
    }

    .ab-section-title span {
        color: var(--clr-brand);
    }

    .ab-section-intro {
        width: min(920px, 100%);
        margin: 0 auto 2.2rem;
        color: #475569;
        font-size: 1.04rem;
        line-height: 1.85;
        text-align: center;
    }

    .ab-awards {
        padding: 4.5rem 1rem 5rem;
        background: #fff;
    }

    .ab-awards-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.75rem;
        max-width: 1100px;
        margin: 0 auto;
        align-items: stretch;
    }

    .ab-award-item {
        position: relative;
        height: 100%;
        padding: 1.8rem 1.35rem 1.45rem;
        border-radius: 24px;
        border: 1px solid rgba(148,163,184,.2);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 18px 40px rgba(15,23,42,.06);
        text-align: center;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .ab-award-item:hover {
        transform: translateY(-5px);
        border-color: rgba(249,115,22,.22);
        box-shadow: 0 24px 48px rgba(15,23,42,.1);
    }

    .ab-award-item h3 {
        margin: 0 0 .6rem;
        color: #0f172a;
        font-size: 1.06rem;
        font-weight: 900;
        text-transform: uppercase;
        line-height: 1.25;
    }

    .ab-award-item p {
        margin: 0;
        color: #475569;
        font-size: .92rem;
        line-height: 1.65;
    }

    .ab-promise-ref {
        padding: 4.5rem 1rem 5rem;
        background: #fff;
    }

    .ab-promise-ref-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
        gap: 2.5rem;
        align-items: center;
        max-width: 1100px;
        margin: 0 auto;
    }

    .ab-promise-visual {
        position: relative;
        min-height: 420px;
    }

    .ab-promise-visual::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 120px;
        background: linear-gradient(to top, #fff, rgba(255,255,255,0));
    }

    .ab-promise-visual img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        border-radius: 10px;
        display: block;
        filter: saturate(.95) contrast(1.03);
    }

    .ab-promise-points {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.8rem 1.5rem;
    }

    .ab-promise-point {
        text-align: center;
    }

    .ab-promise-diamond {
        width: 92px;
        height: 92px;
        margin: 0 auto 1rem;
        transform: rotate(45deg);
        background: var(--clr-brand);
        display: grid;
        place-items: center;
    }

    .ab-promise-diamond svg {
        width: 38px;
        height: 38px;
        color: #fff;
        transform: rotate(-45deg);
    }

    .ab-promise-point h3 {
        margin: 0;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .ab-reach {
        padding: 4.75rem 1rem 6rem;
        background: #fff;
    }

    .ab-reach-stage {
        max-width: 1120px;
        margin: 0 auto;
        text-align: center;
    }

    .ab-reach-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.35rem;
    }

    .ab-reach-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .9rem 1.15rem;
        margin: 0 auto 2rem;
        border-radius: 999px;
        background: rgba(255,255,255,.94);
        border: 1px solid rgba(15,23,42,.08);
        box-shadow: 0 12px 30px rgba(15,23,42,.08);
        color: #0f172a;
        font-size: .92rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .ab-reach-stat {
        width: auto;
        padding: 1.7rem 1rem 1.45rem;
        border-radius: 24px;
        border: 1px solid rgba(148,163,184,.18);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 16px 36px rgba(15,23,42,.06);
        text-align: center;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .ab-reach-stat strong {
        display: block;
        color: var(--clr-brand);
        font-size: clamp(2.5rem, 5vw, 4rem);
        line-height: 1;
        font-weight: 900;
        letter-spacing: -.05em;
    }

    .ab-reach-stat span {
        display: block;
        margin-top: .4rem;
        color: #334155;
        font-size: .98rem;
        line-height: 1.3;
        font-weight: 700;
    }

    .ab-reach-stat:hover {
        transform: translateY(-5px);
        border-color: rgba(249,115,22,.2);
        box-shadow: 0 22px 44px rgba(15,23,42,.1);
    }

    @media (max-width: 1024px) {
        .ab-awards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ab-promise-ref-grid {
            grid-template-columns: 1fr;
        }

        .ab-promise-visual {
            min-height: 320px;
        }

        .ab-reach-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ab-reach-badge {
            z-index: 1;
        }
    }

    @media (max-width: 640px) {
        .ab-awards-grid,
        .ab-promise-points,
        .ab-reach-grid {
            grid-template-columns: 1fr;
        }

        .ab-promise-visual {
            min-height: 240px;
        }

        .ab-section-intro {
            font-size: .96rem;
        }
    }

    /* Final mobile polish for About page */
    @media (max-width: 900px) {
        .ab-hero {
            min-height: auto;
            padding: 2.4rem 1rem 3.4rem !important;
        }

        .ab-hero-grid {
            grid-template-columns: 1fr !important;
            gap: 1.75rem !important;
        }

        .ab-hero-card {
            width: min(560px, 100%);
            margin-inline: auto;
        }

        .ab-showcase-head,
        .ab-stats-intro,
        .ab-gallery-head,
        .ab-promise-ref-grid {
            grid-template-columns: 1fr !important;
        }

        .ab-awards-grid,
        .ab-reach-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .ab-wrap,
        .ab-hero .ab-wrap {
            padding-inline: 0;
            width: 100% !important;
        }

        .ab-hero {
            padding: 1.65rem .9rem 2.35rem !important;
        }

        .ab-hero::after {
            height: 150px;
        }

        .ab-hero-eyebrow {
            max-width: 100%;
            white-space: normal;
            line-height: 1.25;
        }

        .ab-hero-copy-h1 {
            font-size: clamp(1.95rem, 10vw, 2.75rem) !important;
            margin-bottom: .85rem !important;
        }

        .ab-hero-lead {
            font-size: .95rem !important;
            line-height: 1.62 !important;
            margin-bottom: 1.15rem !important;
        }

        .ab-hero-actions,
        .ab-cta-actions {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: .7rem;
        }

        .ab-btn {
            width: 100%;
            justify-content: center;
            min-height: 44px;
        }

        .ab-hero-trust,
        .ab-hero-mini-stats {
            grid-template-columns: 1fr !important;
            gap: .6rem !important;
        }

        .ab-hero-card {
            padding: .75rem !important;
            border-radius: 22px !important;
        }

        .ab-card-image img {
            height: clamp(185px, 58vw, 255px) !important;
            border-radius: 17px !important;
        }

        .ab-card-badge {
            left: .7rem;
            right: .7rem;
            bottom: .7rem;
            padding: .7rem .8rem;
            border-radius: 14px;
        }

        .ab-hero-card h2 {
            font-size: 1.08rem !important;
            line-height: 1.32;
        }

        .ab-hero-card p {
            font-size: .86rem !important;
            line-height: 1.65 !important;
        }

        .ab-hero-card-meta {
            grid-template-columns: 1fr !important;
            gap: .55rem !important;
        }

        .ab-hero-meta-chip {
            text-align: center;
        }

        .ab-story,
        .ab-awards,
        .ab-promise-ref,
        .ab-reach {
            padding: 3.2rem .9rem !important;
        }

        .ab-story-kicker {
            font-size: clamp(1.9rem, 11vw, 2.6rem) !important;
        }

        .ab-story-copy {
            padding: 1.15rem !important;
            border-radius: 22px !important;
        }

        .ab-story-copy h2 {
            max-width: 100%;
            font-size: clamp(1.65rem, 10vw, 2.35rem) !important;
            line-height: 1.05 !important;
            text-align: left;
        }

        .ab-story-copy p {
            font-size: .92rem !important;
            line-height: 1.76 !important;
        }

        .ab-section-title {
            font-size: clamp(1.7rem, 10vw, 2.35rem) !important;
        }

        .ab-section-intro {
            margin-bottom: 1.35rem;
            font-size: .92rem !important;
            line-height: 1.65;
        }

        .ab-awards-grid,
        .ab-promise-points,
        .ab-reach-grid {
            grid-template-columns: 1fr !important;
            gap: .9rem;
        }

        .ab-award-item,
        .ab-reach-stat {
            padding: 1.15rem 1rem !important;
            border-radius: 18px !important;
        }

        .ab-promise-visual {
            min-height: 210px !important;
        }

        .ab-promise-diamond {
            width: 72px;
            height: 72px;
        }

        .ab-promise-diamond svg {
            width: 30px;
            height: 30px;
        }

        .ab-reach-badge {
            width: 100%;
            white-space: normal;
            line-height: 1.35;
            padding: .75rem .85rem;
            font-size: .74rem;
            margin-bottom: 1rem;
        }

        .ab-reach-stat strong {
            font-size: clamp(2rem, 14vw, 3rem) !important;
        }
    }

</style>

<div class="about-page">

    <!-- ══════════════ HERO ══════════════ -->
    <section class="ab-hero">
        <div class="ab-blob ab-blob-1"></div>
        <div class="ab-blob ab-blob-2"></div>

        <div class="ab-wrap" style="width:100%;max-width:1180px;">
            <div class="ab-hero-grid">

                <!-- Copy -->
                <div class="ab-hero-copy-block ab-reveal ab-d1">
                    <div class="ab-hero-eyebrow">
                        <span class="ab-hero-eyebrow-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
                        About <?= htmlspecialchars(APP_NAME) ?>
                    </div>
                    <h1 class="ab-hero-copy-h1" style="color:#fff;font-size:clamp(2.4rem,5.5vw,4.2rem);line-height:1.03;font-weight:900;letter-spacing:-.04em;margin:0 0 1.25rem;">
                        Building homes,<br><em style="font-style:normal;color:var(--clr-brand);">building trust</em><br>since 2018.
                    </h1>
                    <p class="ab-hero-lead" style="color:rgba(255,255,255,.78);font-size:1.1rem;line-height:1.78;max-width:540px;margin:0 0 2rem;">
                        Southdev Home Depot delivers quality hardware, tiles, and construction supplies to homeowners and builders across Davao City — now with a modern online experience.
                    </p>
                    <div class="ab-hero-actions">
                        <a class="ab-btn ab-btn-primary" href="<?= APP_URL ?>/index.php?url=products">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            Browse Products
                        </a>
                        <a class="ab-btn ab-btn-ghost" href="<?= APP_URL ?>/index.php?url=locations">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Visit Our Store
                        </a>
                    </div>
                    <div class="ab-hero-trust" aria-label="Southdev Home Depot strengths">
                        <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Local Davao supplier</span>
                        <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Secure checkout</span>
                        <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 24/7 catalogue access</span>
                    </div>
                    <div class="ab-hero-mini-stats" aria-label="Southdev overview highlights">
                        <?php foreach ($heroHighlights as $highlight): ?>
                            <div class="ab-hero-mini-stat">
                                <strong><?= htmlspecialchars($highlight['value']) ?></strong>
                                <span><?= htmlspecialchars($highlight['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Card -->
                <div class="ab-hero-card ab-reveal ab-d2">
                    <div class="ab-card-image">
                        <img src="<?= $storyImage ?>" alt="<?= htmlspecialchars(APP_NAME) ?> showroom">
                        <div class="ab-card-badge">
                            <strong>Matina showroom</strong>
                            <span>Juna Avenue, Davao City</span>
                        </div>
                    </div>
                    <div class="ab-hero-card-topline">Signature showroom</div>
                    <h2><?= htmlspecialchars(APP_TAGLINE ?? "Davao City's Premier Tiles & Hardware Supply") ?></h2>
                    <p>From the beginning, the company has continued to grow in order to serve both individual customers and contractors with dependable products, low prices, and reliable customer service.</p>
                    <div class="ab-hero-card-meta">
                        <div class="ab-hero-meta-chip">
                            <strong>Davao-based</strong>
                            <span>Local supplier</span>
                        </div>
                        <div class="ab-hero-meta-chip">
                            <strong>Pickup ready</strong>
                            <span>In-store assistance</span>
                        </div>
                        <div class="ab-hero-meta-chip">
                            <strong>Secure</strong>
                            <span>Safe checkout</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════ STORY ══════════════ -->
    <section class="ab-story">
        <div class="ab-wrap">
            <div class="ab-story-shell ab-reveal">
                <div class="ab-story-header">
                    <div class="ab-story-kicker">Our <span>Story</span></div>
                </div>

                <div class="ab-story-copy">
                    <p>Southdev Home Depot, founded in 2018, supports the residential and commercial construction needs of Davao City and nearby areas through accessible hardware and home improvement products. Located on Juna Avenue in Matina, Davao City, the store has become a trusted source of building materials, hardware tools, electrical and plumbing fittings, tiles, and other essentials for both small home upgrades and large commercial developments.</p>
                    <p>Its mission is to provide a complete range of quality building and hardware products at reasonable prices, backed by outstanding customer service and dependable supply chain management. Southdev believes that every homeowner, contractor, and builder deserves premium-grade materials and professional guidance to complete projects with confidence.</p>
                    <p>To stay competitive in a fast-changing market, Southdev Home Depot is advancing through an Online Management System that modernizes operations, simplifies transactions, improves real-time inventory visibility, and gives customers 24/7 access to the store's full product lineup. By combining e-commerce convenience with safe payment options, Southdev continues to grow as a progressive hardware retailer that blends classic retail service with modern digital ease.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ab-awards" aria-label="Why choose Southdev">
        <div class="ab-wrap">
            <div class="ab-reveal">
                <h2 class="ab-section-title">Why Choose <span>Southdev</span></h2>
                <p class="ab-section-intro">Southdev Home Depot supports homeowners, builders, and contractors with dependable products, practical assistance, and a reliable local supply experience.</p>
            </div>

            <div class="ab-awards-grid">
                <?php foreach ($awards as $index => $award): ?>
                    <article class="ab-award-item ab-reveal ab-d<?= ($index % 4) + 1 ?>">
                        <h3><?= htmlspecialchars($award['title']) ?></h3>
                        <p><?= htmlspecialchars($award['subtitle']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="ab-promise-ref" aria-label="Southdev promise">
        <div class="ab-wrap">
            <div class="ab-reveal">
                <h2 class="ab-section-title">Our <span>Promise</span></h2>
                <p class="ab-section-intro">Southdev Home Depot remains committed to giving homeowners, builders, and contractors a more reliable, accessible, and customer-focused construction supply experience.</p>
            </div>

            <div class="ab-promise-ref-grid">
                <div class="ab-promise-visual ab-reveal ab-d1">
                    <img src="<?= $promiseVisual ?>" alt="Southdev Home Depot showroom interior" loading="lazy">
                </div>

                <div class="ab-promise-points">
                    <article class="ab-promise-point ab-reveal ab-d1">
                        <div class="ab-promise-diamond">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18"/><path d="M5 7l3-4h8l3 4-7 8-7-8Z"/><path d="M12 15v6"/></svg>
                        </div>
                        <h3>Product<br>Reliability</h3>
                    </article>

                    <article class="ab-promise-point ab-reveal ab-d2">
                        <div class="ab-promise-diamond">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="6" height="6"/><rect x="14" y="4" width="6" height="6"/><rect x="4" y="14" width="6" height="6"/><rect x="14" y="14" width="6" height="6"/></svg>
                        </div>
                        <h3>Wider<br>Selection</h3>
                    </article>

                    <article class="ab-promise-point ab-reveal ab-d3">
                        <div class="ab-promise-diamond">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 12l3 3 5-5"/><path d="M12 21c4.97 0 9-4.03 9-9s-4.03-9-9-9-9 4.03-9 9 4.03 9 9 9Z"/></svg>
                        </div>
                        <h3>Better Customer<br>Experience</h3>
                    </article>

                    <article class="ab-promise-point ab-reveal ab-d4">
                        <div class="ab-promise-diamond">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-4.35 7-10a7 7 0 1 0-14 0c0 5.65 7 10 7 10Z"/><circle cx="12" cy="11" r="2.5"/></svg>
                        </div>
                        <h3>Utmost<br>Accessibility</h3>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="ab-reach" aria-label="Southdev at a glance">
        <div class="ab-wrap">
            <div class="ab-reveal">
                <h2 class="ab-section-title">Southdev <span>at a Glance</span></h2>
                <p class="ab-section-intro">Serving customers from Matina, Davao City with dependable building materials, practical hardware essentials, and convenient online access.</p>
            </div>

            <div class="ab-reach-stage">
                <div class="ab-reach-badge ab-reveal ab-d1">Southdev • Matina, Davao City</div>

                <div class="ab-reach-grid">
                    <?php foreach ($reachStats as $index => $stat): ?>
                        <article class="ab-reach-stat ab-reveal ab-d<?= ($index % 4) + 1 ?>">
                            <strong><?= htmlspecialchars($stat['value']) ?></strong>
                            <span><?= htmlspecialchars($stat['label']) ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

</div><!-- .about-page -->

<script>
(function () {
    'use strict';

    /* ── Scroll reveal ── */
    var reveals = document.querySelectorAll('.ab-reveal');
    if ('IntersectionObserver' in window) {
        var ro = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                e.target.classList.add('ab-visible');
                obs.unobserve(e.target);
            });
        }, { threshold: 0.09, rootMargin: '0px 0px -4% 0px' });
        reveals.forEach(function (el) { ro.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add('ab-visible'); });
    }

    /* ── Stat counter animation ── */
    var counters = document.querySelectorAll('.ab-counter[data-target]');
    if (!counters.length) return;

    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-target'), 10);
        if (isNaN(target)) return;
        var duration = 1400;
        var start = null;
        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(ease * target) + (progress < 1 ? '' : '+');
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target + '+';
        }
        requestAnimationFrame(step);
    }

    if ('IntersectionObserver' in window) {
        var co = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                animateCounter(e.target);
                obs.unobserve(e.target);
            });
        }, { threshold: 0.3 });
        counters.forEach(function (c) { co.observe(c); });
    } else {
        counters.forEach(function (c) { animateCounter(c); });
    }
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>