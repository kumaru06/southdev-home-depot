<?php
// Preserve any page title provided by the router; otherwise default to Home.
if (!isset($pageTitle) || empty($pageTitle)) {
    $pageTitle = 'Home';
}

$extraCss = ['customer.css'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$landingBase = 'assets/uploads/landing-page/';

$heroSlides = [
    [
        'image' => $landingBase . 'cover.png',
        'alt' => 'Southdev Home Depot showroom and tile displays',
    ],
    [
        'image' => $landingBase . 'wave-series.png',
        'alt' => 'Wave Series premium tile collection',
    ],
    [
        'image' => $landingBase . 'crafted-modern-spaces.png',
        'alt' => 'Crafted modern spaces with designer tiles',
    ],
    [
        'image' => $landingBase . 'wood-look.png',
        'alt' => 'Wood-look tile and flooring finishes',
    ],
    [
        'image' => $landingBase . 'everyday-luxury.png',
        'alt' => 'Everyday luxury tile selections',
    ],
];

$homeHero = [
    'eyebrow' => 'Tiles · Flooring · Fixtures',
    'title' => 'Shop Premium <span style="color:#f97316;">Tiles &amp; Materials</span> For Every Project',
    'copy' => 'Browse floor tiles, wall finishes, SPC flooring, bathroom fixtures, and structural supplies — curated for builders, designers, and homeowners who want store-ready quality.',
];

$showcaseSlides = [
    [
        'tag' => 'Wave Series',
        'title' => 'Sculpted surfaces with bold texture',
        'copy' => 'Statement wall and floor tiles designed for modern retail and residential spaces.',
        'image' => $landingBase . 'wave-series.png',
        'link' => 'products',
    ],
    [
        'tag' => 'Wood Look',
        'title' => 'Warm timber aesthetics, tile durability',
        'copy' => 'Natural wood visuals with easy maintenance — ideal for living areas and kitchens.',
        'image' => $landingBase . 'wood-look.png',
        'link' => 'products',
    ],
    [
        'tag' => 'SPC Flooring',
        'title' => 'Water-resistant planks for daily traffic',
        'copy' => 'Rigid core flooring built for busy homes, showrooms, and commercial fit-outs.',
        'image' => $landingBase . 'spc-floring.png',
        'link' => 'products',
    ],
];

$categoryCards = [
    [
        'title' => 'Floor & Wall Tiles',
        'label' => 'Porcelain, ceramic & designer series',
        'image' => $landingBase . 'laptopmockup.png',
        'link' => 'products',
    ],
    [
        'title' => 'Bathroom Fixtures',
        'label' => 'Sleek fittings & coordinated finishes',
        'image' => $landingBase . 'sleek-bathroom-fixtures.png',
        'link' => 'products',
    ],
    [
        'title' => 'SPC & Flooring',
        'label' => 'Rigid core & wood-look options',
        'image' => $landingBase . 'spc-floring.png',
        'link' => 'products',
    ],
    [
        'title' => 'Compact Solutions',
        'label' => 'Smart picks for small spaces',
        'image' => $landingBase . 'small-space.png',
        'link' => 'products',
    ],
];

$featuredCollections = [
    [
        'tag' => 'Best Seller',
        'title' => 'Crafted modern spaces with premium tile lines',
        'copy' => 'Explore coordinated palettes, large-format slabs, and accent pieces ready to order.',
        'image' => $landingBase . 'crafted-modern-spaces-2.png',
    ],
    [
        'tag' => 'New Arrival',
        'title' => 'Everyday luxury finishes at accessible price points',
        'copy' => 'Elevated surfaces that look high-end without compromising on practicality.',
        'image' => $landingBase . 'everyday-luxury.png',
    ],
    [
        'tag' => 'Collection',
        'title' => 'Refined modern living room & lounge combinations',
        'copy' => 'Pair floor tiles with wall accents for cohesive open-plan styling.',
        'image' => $landingBase . 'refined-modern-living.png',
    ],
    [
        'tag' => 'Compact',
        'title' => 'Small-space layouts that maximize every square meter',
        'copy' => 'Light tones, slim profiles, and space-smart product bundles.',
        'image' => $landingBase . 'small-space.png',
    ],
];

$highlightPanels = [
    [
        'title' => 'Zen Series Tiles',
        'copy' => 'Calm palettes and soft textures for spa bathrooms and quiet retreats.',
        'image' => $landingBase . 'zen.png',
    ],
    [
        'title' => 'Daily Upgrade Essentials',
        'copy' => 'Mix-and-match fixtures and finishes for quick bathroom refreshes.',
        'image' => $landingBase . 'upgrade-your-daily-routine.png',
    ],
    [
        'title' => 'Sleek Bathroom Suite',
        'copy' => 'Minimal chrome, clean lines, and coordinated basin and shower sets.',
        'image' => $landingBase . 'sleek-bathroom-fixtures-main.png',
    ],
];

$storePoints = [
    'In-store tile displays you can compare side by side',
    'Staff guidance on quantities, adhesives, and installation basics',
    'One-stop sourcing for tiles, flooring, fixtures, and structural materials',
];

$storeFeatureImage = $landingBase . 'crafted-modern-spaces-4.png';
?>

<style>
.site-header .main-nav {
    margin-bottom: 0;
}

body {
    overflow-x: hidden;
}

.home-shell {
    overflow-x: hidden;
    padding: 0 0 4rem;
}

.home-hero {
    position: relative;
    overflow: hidden;
    padding: clamp(2.4rem, 4.5vw, 3.4rem) 2rem 4rem;
    color: #fff;
    contain: layout style;
}

.home-hero-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.home-hero-bg-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transform: scale(1);
    transition: opacity 1.4s ease;
}

.home-hero-bg-slide.is-active {
    opacity: 1;
}

.home-hero-bg-slide::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(135deg, rgba(14, 24, 38, .9) 0%, rgba(25, 43, 67, .72) 48%, rgba(38, 59, 92, .55) 100%);
}

.home-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 1;
    background:
        radial-gradient(circle at top left, rgba(255,255,255,.14), transparent 28%),
        linear-gradient(180deg, rgba(255,255,255,.04), transparent 35%);
    pointer-events: none;
}

.home-hero::after {
    content: '';
    position: absolute;
    inset: auto 0 -1px 0;
    height: 200px;
    z-index: 1;
    background: linear-gradient(180deg, transparent 0%, rgba(255,255,255,.5) 45%, rgba(255,255,255,.9) 75%, #ffffff 100%);
    pointer-events: none;
}

.home-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 1220px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
    gap: 2rem;
    align-items: stretch;
}

.home-hero-copy {
    max-width: 640px;
    display: flex;
    flex-direction: column;
}

.home-eyebrow {
    display: inline-flex;
    align-items: center;
    align-self: flex-start; /* don't stretch to full hero column width */
    width: fit-content;
    gap: .65rem;
    padding: .55rem .95rem;
    border-radius: 999px;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.22);
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 1.4rem;
}

.home-eyebrow svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.home-hero-copy h1 {
    margin: 0;
    font-size: clamp(2.35rem, 4.8vw, 4.1rem);
    line-height: 1.02;
    font-weight: 900;
    letter-spacing: -.05em;
    color: #fff;
    max-width: 760px;
}

.home-hero-copy p {
    margin: 1.3rem 0 0;
    font-size: 1.08rem;
    line-height: 1.75;
    color: rgba(255,255,255,.86);
    max-width: 620px;
}

.home-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .9rem;
    margin-top: 2rem;
}

.home-hero-actions .btn {
    min-height: 48px;
    padding: .85rem 1.45rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    font-weight: 700;
    box-shadow: 0 14px 28px rgba(15, 23, 42, .14);
}

.home-hero-actions .btn-outline-light {
    border: 1px solid rgba(255,255,255,.24);
    background: rgba(255,255,255,.08);
    color: #fff;
}

.home-hero-actions .btn-outline-light:hover {
    background: rgba(255,255,255,.15);
    color: #fff;
}

.home-hero-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .9rem;
    margin-top: 2rem;
    max-width: 650px;
}

.home-stat {
    padding: 1rem 1.1rem;
    border-radius: 18px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
}

.home-stat strong {
    display: block;
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: .25rem;
}

.home-stat span {
    font-size: .92rem;
    color: rgba(255,255,255,.78);
}

.home-hero-note {
    /* auto margin + fixed padding: leftover column space spreads evenly across blocks */
    margin-top: auto;
    padding-top: 1.6rem;
    max-width: 650px;
}

.home-hero-note p {
    padding: 1.1rem 1.3rem;
    border-radius: 18px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.16);
}

.home-hero-visit {
    margin-top: auto;
    padding-top: 1.4rem;
    max-width: 650px;
}

.home-hero-visit-head {
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: rgba(255,255,255,.65);
    margin-bottom: .7rem;
}

.home-hero-visit-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .9rem;
}

.home-hero-visit-item {
    display: flex;
    align-items: flex-start;
    gap: .7rem;
    padding: .9rem .95rem;
    border-radius: 16px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.16);
}

.home-hero-visit-item strong {
    display: block;
    font-size: .82rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: .12rem;
}

.home-hero-visit-item span:not(.home-hero-perk-icon) {
    font-size: .78rem;
    line-height: 1.45;
    color: rgba(255,255,255,.72);
}

.home-hero-note p {
    margin: 0;
    font-size: .98rem;
    line-height: 1.7;
    color: rgba(255,255,255,.82);
    max-width: none;
}

.home-hero-perks {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .9rem;
    /* Pin to the bottom so the left column ends level with the right-side cards */
    margin-top: auto;
    padding-top: 1.4rem;
    max-width: 650px;
}

.home-hero-perk {
    display: flex;
    align-items: flex-start;
    gap: .8rem;
    padding: .95rem 1rem;
    border-radius: 16px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.16);
}

.home-hero-perk-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(249,115,22,.22);
    color: #fb923c;
}

.home-hero-perk-icon svg {
    width: 17px;
    height: 17px;
}

.home-hero-perk strong {
    display: block;
    font-size: .88rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: .15rem;
}

.home-hero-perk span:not(.home-hero-perk-icon) {
    font-size: .8rem;
    line-height: 1.5;
    color: rgba(255,255,255,.72);
}

.home-hero-side {
    display: grid;
    gap: 1rem;
}

.home-showcase {
    position: relative;
    overflow: hidden;
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,.14);
    box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
    background: transparent;
}

.home-showcase-track {
    display: flex;
    transition: transform .65s cubic-bezier(.4, 0, .2, 1);
    transform: translateZ(0);
}

.home-showcase-slide {
    position: relative;
    flex: 0 0 100%;
    width: 100%;
    min-width: 100%;
}

.home-showcase-slide img {
    width: 100%;
    height: auto;
    display: block;
    vertical-align: bottom;
}

.home-showcase-slide::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(105deg, transparent 42%, rgba(12,18,28,.22) 58%, rgba(12,18,28,.78) 100%);
    pointer-events: none;
}

.home-showcase-copy {
    position: absolute;
    left: auto;
    right: 0;
    bottom: 0;
    z-index: 1;
    width: min(56%, 420px);
    padding: 1.35rem;
    color: #fff;
    text-shadow: 0 2px 14px rgba(0, 0, 0, .35);
}

.home-showcase-copy span {
    display: inline-flex;
    padding: .35rem .7rem;
    border-radius: 999px;
    background: rgba(249, 115, 22, .92);
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: .7rem;
    color: #fff;
}

.home-showcase-copy h3 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 800;
    color: #fff;
}

.home-showcase-copy p {
    margin: .45rem 0 0;
    color: rgba(255,255,255,.82);
    line-height: 1.5;
    font-size: .95rem;
}

.home-hero-bg-dots {
    position: absolute;
    left: 50%;
    bottom: 2.2rem;
    z-index: 2;
    transform: translateX(-50%);
    display: flex;
    gap: .5rem;
}

.home-hero-bg-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 0;
    padding: 0;
    background: rgba(255,255,255,.35);
    cursor: pointer;
    transition: background .2s ease, transform .2s ease;
}

.home-hero-bg-dot.is-active {
    background: #fff;
    transform: scale(1.2);
}

.home-category-card {
    position: relative;
    overflow: hidden;
    border-radius: 22px;
    border: 1px solid rgba(255,255,255,.1);
    box-shadow: 0 16px 36px rgba(15, 23, 42, .18);
    display: block;
    text-decoration: none;
    color: inherit;
    background: #0f172a;
    aspect-ratio: 4 / 5;
    transition: transform .25s ease, box-shadow .25s ease;
}

.home-category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 22px 48px rgba(15, 23, 42, .28);
}

.home-category-card img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    object-position: center center;
}

.home-category-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(105deg, transparent 48%, rgba(12,18,28,.18) 62%, rgba(12,18,28,.76) 100%);
    pointer-events: none;
}

.home-category-card-copy {
    position: absolute;
    left: auto;
    right: 0;
    bottom: 0;
    z-index: 1;
    max-width: 62%;
    padding: 1.1rem;
    color: #fff;
    text-align: right;
    text-shadow: 0 2px 14px rgba(0, 0, 0, .35);
}

.home-category-card-copy span {
    display: inline-flex;
    padding: .3rem .65rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.14);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: .55rem;
    color: #fff;
}

.home-category-card-copy h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.25;
}

.home-mini-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.home-content {
    max-width: 1220px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem 0;
}

.home-section-head {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 1.4rem;
}

.home-section-head h2 {
    margin: 0;
    font-size: clamp(1.8rem, 3vw, 2.8rem);
    line-height: 1.02;
    color: #10233d;
}

.home-section-head p {
    margin: 0;
    max-width: 520px;
    color: #5d6d84;
    line-height: 1.7;
}

.home-signature-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    grid-template-rows: auto auto auto 1fr;
    gap: 1.15rem;
    align-items: start;
}

.home-signature-card {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid rgba(15, 35, 61, .08);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 16px 34px rgba(15, 23, 42, .07);
    transition: transform .25s ease, box-shadow .25s ease;
    display: grid;
    grid-row: span 4;
    grid-template-rows: subgrid;
    height: 100%;
}

@supports not (grid-template-rows: subgrid) {
    .home-signature-card {
        grid-row: auto;
        grid-template-rows: auto auto auto 1fr;
    }
}

.home-signature-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 44px rgba(15, 23, 42, .12);
}

.home-signature-media {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    background: #f3f6f9;
    overflow: hidden;
}

.home-signature-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top center;
    display: block;
}

.home-signature-tag {
    display: inline-flex;
    align-self: start;
    width: fit-content;
    margin: 1.15rem 1.2rem 0;
    padding: .35rem .7rem;
    border-radius: 999px;
    background: #fff4eb;
    color: #c2410c;
    font-size: .74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.home-signature-title {
    margin: .8rem 1.2rem 0;
    color: #12243f;
    font-size: 1.18rem;
    line-height: 1.3;
}

.home-signature-text {
    margin: .75rem 1.2rem 1.35rem;
    color: #66758b;
    line-height: 1.7;
}

.home-mosaic-wrap {
    margin-top: 1.1rem;
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
    gap: 1.15rem;
    align-items: start;
}

.home-mosaic-feature,
.home-mosaic-stack article {
    position: relative;
    overflow: hidden;
    border-radius: 28px;
    box-shadow: 0 24px 52px rgba(15, 23, 42, .12);
    background: transparent;
}

.home-mosaic-media {
    position: relative;
    overflow: hidden;
    border-radius: inherit;
}

.home-mosaic-feature img,
.home-mosaic-stack article img {
    width: 100%;
    height: auto;
    display: block;
}

.home-mosaic-media::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 50%, rgba(10,17,27,.78) 100%);
    pointer-events: none;
}

.home-mosaic-copy {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
    padding: 1.5rem;
    color: #fff;
    text-shadow: 0 2px 14px rgba(0, 0, 0, .35);
}

.home-mosaic-copy h3 {
    margin: 0;
    font-size: 1.5rem;
    color: #fff;
}

.home-mosaic-copy p {
    margin: .55rem 0 0;
    color: rgba(255,255,255,.8);
    line-height: 1.6;
}

.home-mosaic-stack {
    display: grid;
    gap: 1.15rem;
}

.home-polish {
    margin-top: 1.15rem;
    background: linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
    border: 1px solid rgba(15, 35, 61, .08);
    border-radius: 28px;
    display: grid;
    grid-template-columns: minmax(320px, .95fr) minmax(0, 1.05fr);
    overflow: hidden;
    box-shadow: 0 24px 52px rgba(15, 23, 42, .1);
}

.home-polish-media {
    position: relative;
    background: transparent;
}

.home-polish-media img {
    width: 100%;
    height: auto;
    display: block;
}

.home-polish-content {
    padding: 2rem 2.1rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.home-polish-kicker {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    width: fit-content;
    padding: .55rem .9rem;
    border-radius: 999px;
    background: #fff4eb;
    color: #c2410c;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 1rem;
}

.home-polish-kicker svg {
    width: 15px;
    height: 15px;
}

.home-polish h3 {
    margin: 0 0 .8rem;
    font-size: clamp(1.45rem, 3vw, 2.15rem);
    color: #12243f;
    line-height: 1.15;
}

.home-polish-copy {
    margin: 0 0 1.1rem;
    color: #66758b;
    line-height: 1.75;
}

.home-polish ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: .7rem;
}

.home-polish li {
    display: flex;
    align-items: center;
    gap: .7rem;
    color: #30445f;
}

.home-polish li::before {
    content: '';
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #f97316;
    box-shadow: 0 0 0 6px rgba(249, 115, 22, .16);
    flex-shrink: 0;
}

.home-polish .btn {
    width: fit-content;
    margin-top: 1.4rem;
    min-height: 50px;
    padding: .9rem 1.45rem;
    border-radius: 999px;
    font-weight: 700;
}

.home-shop-cta {
    margin-top: 1.15rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1.2rem 1.4rem;
    border-radius: 20px;
    background: linear-gradient(90deg, #fff7ed 0%, #ffffff 100%);
    border: 1px solid rgba(249, 115, 22, .16);
}

.home-shop-cta p {
    margin: 0;
    color: #475569;
    font-weight: 600;
}

.reveal-on-scroll {
    opacity: 0;
    transition: opacity .7s ease, transform .7s ease;
}

.reveal-left {
    transform: translate3d(-42px, 0, 0);
}

.reveal-right {
    transform: translate3d(42px, 0, 0);
}

.reveal-on-scroll.is-visible {
    opacity: 1;
    transform: translate3d(0, 0, 0);
}

@media (prefers-reduced-motion: reduce) {
    .home-hero-bg-slide,
    .home-showcase-track {
        transition: none;
    }

    .reveal-on-scroll,
    .reveal-left,
    .reveal-right {
        opacity: 1;
        transform: none;
        transition: none;
    }
}

@media (max-width: 1100px) {
    .home-hero-inner,
    .home-mosaic-wrap,
    .home-polish {
        grid-template-columns: 1fr;
    }

    .home-signature-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .home-hero-bg-dots {
        bottom: 1.5rem;
    }
}

@media (max-width: 760px) {
    .home-hero {
        padding: 2.35rem 1rem 3rem;
        min-height: 0;
    }

    .home-content {
        padding: 1.8rem 1rem 0;
    }

    .home-hero-meta,
    .home-hero-perks,
    .home-hero-visit-grid,
    .home-signature-grid,
    .home-mini-grid {
        grid-template-columns: 1fr;
    }

    .home-hero-visit-grid {
        gap: .7rem;
    }

    .home-section-head {
        flex-direction: column;
        align-items: start;
    }

    .home-showcase-copy {
        width: min(72%, 320px);
        padding: 1rem;
    }

    .home-showcase-slide {
        min-height: 280px;
    }

    .home-polish {
        grid-template-columns: 1fr;
    }

    .home-polish-content {
        padding: 1.5rem;
    }

    .home-hero-bg-dots {
        display: none;
    }

    .home-hero-stage,
    .home-hero-visual {
        min-height: 0;
    }
}

@media (max-width: 560px) {
    .home-hero-copy h1 {
        font-size: clamp(2rem, 9vw, 2.65rem);
    }

    .home-hero-copy p,
    .home-mosaic-copy p,
    .home-polish-copy {
        font-size: .95rem;
        line-height: 1.68;
    }

    .home-hero-actions {
        display: grid;
        grid-template-columns: 1fr;
    }

    .home-hero-actions .btn,
    .home-polish .btn,
    .home-shop-cta .btn {
        width: 100%;
        justify-content: center;
    }

    .home-stat,
    .home-polish-content {
        padding: 1rem;
    }

    .home-hero-note p {
        padding: .95rem 1.05rem;
        font-size: .92rem;
        line-height: 1.62;
    }

    .home-hero-visit-item,
    .home-hero-perk {
        padding: .8rem .9rem;
        align-items: center;
    }

    .home-hero-visit-item strong,
    .home-hero-perk strong {
        font-size: .84rem;
        margin-bottom: .05rem;
    }

    .home-hero-visit-item span:not(.home-hero-perk-icon),
    .home-hero-perk span:not(.home-hero-perk-icon) {
        font-size: .78rem;
        line-height: 1.45;
    }

    .home-hero-perk-icon {
        width: 30px;
        height: 30px;
    }

    .home-showcase-copy {
        width: min(68%, 280px);
        left: auto;
        right: 0;
        padding: .85rem;
    }

    .home-showcase-slide::after {
        background: linear-gradient(105deg, transparent 35%, rgba(12,18,28,.22) 52%, rgba(12,18,28,.78) 100%);
    }
}

@media (max-width: 420px) {
    .home-shell {
        padding-bottom: 2.8rem;
    }

    .home-hero {
        padding: 1.9rem .85rem 2.5rem;
    }

    .home-eyebrow {
        font-size: .7rem;
        letter-spacing: .08em;
        padding: .58rem .85rem;
        margin-bottom: 1rem;
    }

    .home-content {
        padding: 1.25rem .85rem 0;
    }

    .home-stat strong {
        font-size: 1.2rem;
    }

    .home-category-card,
    .home-signature-card,
    .home-polish,
    .home-showcase,
    .home-mosaic-feature,
    .home-mosaic-stack article {
        border-radius: 18px;
    }

    .home-showcase-copy,
    .home-mosaic-copy,
    .home-polish-content {
        padding: 1rem;
    }

    .home-polish li {
        align-items: flex-start;
    }
}
</style>

<div class="home-shell">
    <section class="home-hero">
        <div class="home-hero-bg" data-hero-bg-slider aria-hidden="true">
            <?php foreach ($heroSlides as $index => $slide): ?>
                <div
                    class="home-hero-bg-slide<?= $index === 0 ? ' is-active' : '' ?>"
                    style="background-image: url('<?= APP_URL ?>/<?= htmlspecialchars($slide['image']) ?>');"
                    data-bg-slide="<?= $index ?>"
                ></div>
            <?php endforeach; ?>
        </div>

        <div class="home-hero-bg-dots" aria-label="Hero background slideshow controls">
            <?php foreach ($heroSlides as $index => $slide): ?>
                <button
                    type="button"
                    class="home-hero-bg-dot<?= $index === 0 ? ' is-active' : '' ?>"
                    data-bg-slide-to="<?= $index ?>"
                    aria-label="Show hero background <?= $index + 1 ?>"
                ></button>
            <?php endforeach; ?>
        </div>

        <div class="home-hero-inner">
            <div class="home-hero-copy reveal-on-scroll reveal-left">
                <div class="home-eyebrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                        <path d="M2 7h20"></path>
                        <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.53.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.53.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.53.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.53.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"></path>
                    </svg>
                    <?= htmlspecialchars($homeHero['eyebrow']) ?>
                </div>

                <h1><?= $homeHero['title'] ?></h1>
                <p><?= htmlspecialchars($homeHero['copy']) ?></p>

                <div class="home-hero-actions">
                    <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">
                        Shop All Products
                    </a>
                    <a href="<?= APP_URL ?>/index.php?url=featured-collections" class="btn btn-outline-light">
                        Browse Collections
                    </a>
                </div>

                <div class="home-hero-meta">
                    <div class="home-stat">
                        <strong>500+</strong>
                        <span>Products in catalog</span>
                    </div>
                    <div class="home-stat">
                        <strong>Tiles</strong>
                        <span>Our core specialty category</span>
                    </div>
                    <div class="home-stat">
                        <strong>100%</strong>
                        <span>Quality-focused sourcing</span>
                    </div>
                </div>

                <div class="home-hero-note">
                    <p>
                        From single-room makeovers to full builds, our Davao City showroom carries the
                        tiles, flooring, and fixtures your project needs &mdash; backed by a team that can
                        guide you from inspiration to installation. Drop by the store or order online,
                        and we&rsquo;ll handle the rest.
                    </p>
                </div>

                <div class="home-hero-visit">
                    <div class="home-hero-visit-head">Visit Our Showroom</div>
                    <div class="home-hero-visit-grid">
                        <div class="home-hero-visit-item">
                            <span class="home-hero-perk-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <div>
                                <strong>Location</strong>
                                <span><?= APP_LOCATION ?></span>
                            </div>
                        </div>
                        <div class="home-hero-visit-item">
                            <span class="home-hero-perk-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </span>
                            <div>
                                <strong>Store Hours</strong>
                                <span>Mon&ndash;Sat: 8:00 AM &ndash; 5:00 PM</span>
                            </div>
                        </div>
                        <div class="home-hero-visit-item">
                            <span class="home-hero-perk-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <div>
                                <strong>Call Us</strong>
                                <span>+63 (939) 939 8250</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="home-hero-perks">
                    <div class="home-hero-perk">
                        <span class="home-hero-perk-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>
                        <div>
                            <strong>Store-Ready Stock</strong>
                            <span>Browse online, pick up in-store or have it delivered</span>
                        </div>
                    </div>
                    <div class="home-hero-perk">
                        <span class="home-hero-perk-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        </span>
                        <div>
                            <strong>Local Delivery</strong>
                            <span>Fast hauling around Davao City and nearby areas</span>
                        </div>
                    </div>
                    <div class="home-hero-perk">
                        <span class="home-hero-perk-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        </span>
                        <div>
                            <strong>Flexible Payments</strong>
                            <span>Cash on delivery, GCash, and card payments accepted</span>
                        </div>
                    </div>
                    <div class="home-hero-perk">
                        <span class="home-hero-perk-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                        </span>
                        <div>
                            <strong>Expert Guidance</strong>
                            <span>Product advice from our showroom team, free of charge</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="home-hero-side reveal-on-scroll reveal-right">
                <div class="home-showcase" data-home-showcase>
                    <div class="home-showcase-track" data-showcase-track>
                        <?php foreach ($showcaseSlides as $index => $slide): ?>
                            <article class="home-showcase-slide" aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                                <img src="<?= APP_URL ?>/<?= htmlspecialchars($slide['image']) ?>" alt="<?= htmlspecialchars($slide['title']) ?>">
                                <div class="home-showcase-copy">
                                    <span><?= htmlspecialchars($slide['tag']) ?></span>
                                    <h3><?= htmlspecialchars($slide['title']) ?></h3>
                                    <p><?= htmlspecialchars($slide['copy']) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="home-mini-grid">
                    <?php foreach ($categoryCards as $card): ?>
                        <a href="<?= APP_URL ?>/index.php?url=<?= htmlspecialchars($card['link']) ?>" class="home-category-card">
                            <img src="<?= APP_URL ?>/<?= htmlspecialchars($card['image']) ?>" alt="<?= htmlspecialchars($card['title']) ?>">
                            <div class="home-category-card-copy">
                                <span>Shop</span>
                                <h3><?= htmlspecialchars($card['title']) ?></h3>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="home-content">
        <section>
            <div class="home-section-head reveal-on-scroll reveal-left">
                <div>
                    <h2>Featured collections built around real product lines.</h2>
                </div>
                <p>Each card highlights a tile series, flooring option, or fixture grouping you can browse and order — not just styled room mockups.</p>
            </div>

            <div class="home-signature-grid">
                <?php foreach ($featuredCollections as $index => $space): ?>
                    <article class="home-signature-card reveal-on-scroll <?= $index % 2 === 0 ? 'reveal-left' : 'reveal-right' ?>">
                        <div class="home-signature-media">
                            <img src="<?= APP_URL ?>/<?= htmlspecialchars($space['image']) ?>" alt="<?= htmlspecialchars($space['tag']) ?> collection">
                        </div>
                        <span class="home-signature-tag"><?= htmlspecialchars($space['tag']) ?></span>
                        <h3 class="home-signature-title"><?= htmlspecialchars($space['title']) ?></h3>
                        <p class="home-signature-text"><?= htmlspecialchars($space['copy']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section style="margin-top: 2rem;">
            <div class="home-section-head reveal-on-scroll reveal-right">
                <div>
                    <h2>Product highlights worth exploring first.</h2>
                </div>
                <p>From zen-inspired wall tiles to complete bathroom upgrades — curated visuals that point shoppers straight to what you sell.</p>
            </div>

            <div class="home-mosaic-wrap">
                <article class="home-mosaic-feature reveal-on-scroll reveal-left">
                    <div class="home-mosaic-media">
                        <img src="<?= APP_URL ?>/<?= htmlspecialchars($highlightPanels[0]['image']) ?>" alt="<?= htmlspecialchars($highlightPanels[0]['title']) ?>">
                        <div class="home-mosaic-copy">
                            <h3><?= htmlspecialchars($highlightPanels[0]['title']) ?></h3>
                            <p><?= htmlspecialchars($highlightPanels[0]['copy']) ?></p>
                        </div>
                    </div>
                </article>

                <div class="home-mosaic-stack">
                    <?php foreach (array_slice($highlightPanels, 1) as $index => $panel): ?>
                        <article class="reveal-on-scroll <?= $index % 2 === 0 ? 'reveal-right' : 'reveal-left' ?>">
                            <div class="home-mosaic-media">
                                <img src="<?= APP_URL ?>/<?= htmlspecialchars($panel['image']) ?>" alt="<?= htmlspecialchars($panel['title']) ?>">
                                <div class="home-mosaic-copy">
                                    <h3><?= htmlspecialchars($panel['title']) ?></h3>
                                    <p><?= htmlspecialchars($panel['copy']) ?></p>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="home-shop-cta reveal-on-scroll reveal-left">
                <p>Ready to compare sizes, finishes, and stock?</p>
                <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">View Full Product Catalog</a>
            </div>
        </section>

        <section class="home-polish reveal-on-scroll reveal-right">
            <div class="home-polish-media">
                <img src="<?= APP_URL ?>/<?= htmlspecialchars($storeFeatureImage) ?>" alt="Southdev Home Depot tile showroom">
            </div>

            <div class="home-polish-content">
                <div class="home-polish-kicker">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 10.5 12 4l9 6.5"></path>
                        <path d="M5 9.5V20h14V9.5"></path>
                        <path d="M9 20v-6h6v6"></path>
                    </svg>
                    Visit The Store
                </div>
                <h3>See tiles, flooring, and fixtures in person before you buy.</h3>
                <p class="home-polish-copy">Walk our showroom displays, compare textures under real lighting, and get practical advice for your project — whether you are renovating one bathroom or supplying a full build.</p>
                <ul>
                    <?php foreach ($storePoints as $point): ?>
                        <li><?= htmlspecialchars($point) ?></li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= APP_URL ?>/index.php?url=locations" class="btn btn-accent">Find Our Location</a>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function initSlider(config) {
        var root = document.querySelector(config.root);
        if (!root) {
            return null;
        }

        var slides = Array.prototype.slice.call(root.querySelectorAll(config.slideSelector));
        var dots = config.dotSelector ? Array.prototype.slice.call(document.querySelectorAll(config.dotSelector)) : [];
        var prevBtn = config.prevSelector ? document.querySelector(config.prevSelector) : null;
        var nextBtn = config.nextSelector ? document.querySelector(config.nextSelector) : null;
        var track = config.trackSelector ? root.querySelector(config.trackSelector) : null;
        var currentIndex = 0;
        var intervalId = null;
        var delay = config.delay || 5000;
        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function renderSlide(index) {
            currentIndex = (index + slides.length) % slides.length;

            if (track) {
                track.style.transform = 'translate3d(-' + (currentIndex * 100) + '%, 0, 0)';
            }

            slides.forEach(function (slide, slideIndex) {
                slide.classList.toggle('is-active', slideIndex === currentIndex);
                if (slide.hasAttribute('aria-hidden')) {
                    slide.setAttribute('aria-hidden', slideIndex === currentIndex ? 'false' : 'true');
                }
            });

            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle('is-active', dotIndex === currentIndex);
            });
        }

        function nextSlide() {
            renderSlide(currentIndex + 1);
        }

        function prevSlide() {
            renderSlide(currentIndex - 1);
        }

        function stopAutoplay() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }

        function startAutoplay() {
            if (reducedMotion || !config.autoplay) {
                return;
            }

            stopAutoplay();
            intervalId = window.setInterval(nextSlide, delay);
        }

        dots.forEach(function (dot, index) {
            dot.addEventListener('click', function () {
                renderSlide(index);
                startAutoplay();
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                prevSlide();
                startAutoplay();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                nextSlide();
                startAutoplay();
            });
        }

        root.addEventListener('mouseenter', stopAutoplay);
        root.addEventListener('mouseleave', startAutoplay);

        renderSlide(0);
        startAutoplay();

        return {
            stop: stopAutoplay,
            start: startAutoplay
        };
    }

    var heroBgSlider = initSlider({
        root: '[data-hero-bg-slider]',
        slideSelector: '.home-hero-bg-slide',
        dotSelector: '[data-bg-slide-to]',
        autoplay: true,
        delay: 5500
    });

    var showcaseSlider = initShowcaseInfiniteSlider({
        root: '[data-home-showcase]',
        trackSelector: '[data-showcase-track]',
        slideSelector: '.home-showcase-slide',
        autoplay: true,
        delay: 4500
    });

    function initShowcaseInfiniteSlider(config) {
        var root = document.querySelector(config.root);
        if (!root) {
            return null;
        }

        var track = root.querySelector(config.trackSelector);
        if (!track) {
            return null;
        }

        var originalSlides = Array.prototype.slice.call(track.querySelectorAll(config.slideSelector));
        var slideCount = originalSlides.length;
        if (!slideCount) {
            return null;
        }

        if (slideCount > 1) {
            originalSlides.forEach(function (slide) {
                var clone = slide.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                clone.classList.remove('is-active');
                track.appendChild(clone);
            });
        }

        var allSlides = Array.prototype.slice.call(track.querySelectorAll(config.slideSelector));
        var currentIndex = 0;
        var intervalId = null;
        var delay = config.delay || 5000;
        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var isResetting = false;

        function getStepWidth() {
            return root.clientWidth;
        }

        function setPosition(animate) {
            if (animate === false) {
                track.style.transition = 'none';
            } else {
                track.style.transition = '';
            }

            track.style.transform = 'translate3d(-' + (currentIndex * getStepWidth()) + 'px, 0, 0)';

            if (animate === false) {
                track.offsetHeight;
                track.style.transition = '';
            }
        }

        function updateSlides() {
            allSlides.forEach(function (slide, slideIndex) {
                var isActive = slideIndex === currentIndex;
                slide.classList.toggle('is-active', isActive);
                if (slide.hasAttribute('aria-hidden')) {
                    slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                }
            });
        }

        function goTo(index, animate) {
            currentIndex = index;
            setPosition(animate !== false);
            updateSlides();
        }

        function nextSlide() {
            if (isResetting || slideCount <= 1) {
                return;
            }

            goTo(currentIndex + 1, true);
        }

        function handleTransitionEnd(event) {
            if (event.target !== track || event.propertyName !== 'transform' || slideCount <= 1) {
                return;
            }

            if (currentIndex >= slideCount) {
                isResetting = true;
                goTo(currentIndex - slideCount, false);
                isResetting = false;
            }
        }

        function stopAutoplay() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }

        function startAutoplay() {
            if (reducedMotion || !config.autoplay || slideCount <= 1) {
                return;
            }

            stopAutoplay();
            intervalId = window.setInterval(nextSlide, delay);
        }

        function handleResize() {
            setPosition(false);
        }

        track.addEventListener('transitionend', handleTransitionEnd);
        root.addEventListener('mouseenter', stopAutoplay);
        root.addEventListener('mouseleave', startAutoplay);
        window.addEventListener('resize', handleResize);

        goTo(0, false);
        startAutoplay();

        return {
            stop: stopAutoplay,
            start: startAutoplay
        };
    }

    var heroSection = document.querySelector('.home-hero');
    if (heroSection && 'IntersectionObserver' in window) {
        var heroObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    if (heroBgSlider) heroBgSlider.start();
                    if (showcaseSlider) showcaseSlider.start();
                } else {
                    if (heroBgSlider) heroBgSlider.stop();
                    if (showcaseSlider) showcaseSlider.stop();
                }
            });
        }, { threshold: 0.05 });

        heroObserver.observe(heroSection);
    }
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
