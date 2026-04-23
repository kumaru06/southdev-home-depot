<?php
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$featuredCollections = [
    [
        'slug' => 'grove',
        'name' => 'Grove',
        'accent' => '#d97706',
        'eyebrow' => 'Earth-Led Finish',
        'headline' => 'Grounded tones that make living spaces feel warm and custom-styled.',
        'description' => 'Grove brings together layered neutrals, soft movement, and a clean natural character that works beautifully across lounge areas, accent walls, and refined everyday interiors.',
        'mainImage' => 'assets/uploads/images/featuredcollections/grove/grove.png',
        'tiles' => [
            'assets/uploads/images/featuredcollections/grove/tiles.png',
            'assets/uploads/images/featuredcollections/grove/tiles2.png',
            'assets/uploads/images/featuredcollections/grove/tiles3.png',
        ],
        'highlights' => ['Warm beige movement', 'Balanced for walls and floors', 'Easy match for wood and matte accents'],
        'bestFor' => 'Living rooms and open-plan interiors',
        'styleNote' => 'Soft organic warmth with a tailored feel',
    ],
    [
        'slug' => 'lithos',
        'name' => 'Lithos',
        'accent' => '#2563eb',
        'eyebrow' => 'Architectural Minimal',
        'headline' => 'Crisp stone styling for modern spaces that lean clean and premium.',
        'description' => 'Lithos is ideal for projects that need a brighter and more architectural visual direction, pairing sharp lines with a polished showroom-ready finish.',
        'mainImage' => 'assets/uploads/images/featuredcollections/lithos/lithos.png',
        'tiles' => [
            'assets/uploads/images/featuredcollections/lithos/tiles.png',
            'assets/uploads/images/featuredcollections/lithos/tiles2.png',
            'assets/uploads/images/featuredcollections/lithos/tiles3.png',
        ],
        'highlights' => ['Bright neutral palette', 'Minimal and professional tone', 'Great for kitchens, baths, and facades'],
        'bestFor' => 'Kitchens, baths, and refined commercial zones',
        'styleNote' => 'Sharp, bright, and confidently modern',
    ],
    [
        'slug' => 'slate',
        'name' => 'Slate',
        'accent' => '#475569',
        'eyebrow' => 'Bold Surface Story',
        'headline' => 'Textured character and strong pattern movement for statement-led rooms.',
        'description' => 'Slate delivers visual depth and a richer material look, making it a strong fit for feature zones where you want the flooring or wall finish to lead the room.',
        'mainImage' => 'assets/uploads/images/featuredcollections/slate/slate.png',
        'tiles' => [
            'assets/uploads/images/featuredcollections/slate/tiles.png',
            'assets/uploads/images/featuredcollections/slate/tiles2.png',
            'assets/uploads/images/featuredcollections/slate/tiles3.png',
        ],
        'highlights' => ['Layered stone texture', 'Best for focal-point surfaces', 'Pairs well with black and bronze details'],
        'bestFor' => 'Accent walls and dramatic focal surfaces',
        'styleNote' => 'Deeper texture with a showroom statement',
    ],
    [
        'slug' => 'solarstone',
        'name' => 'Solarstone',
        'accent' => '#f59e0b',
        'eyebrow' => 'Light-Filled Finish',
        'headline' => 'A brighter collection that keeps interiors open, airy, and polished.',
        'description' => 'Solarstone is made for spaces that need clarity and softness, helping smaller rooms feel more open while still keeping a refined, high-end material language.',
        'mainImage' => 'assets/uploads/images/featuredcollections/solarstone/solarstone.png',
        'tiles' => [
            'assets/uploads/images/featuredcollections/solarstone/tiles.png',
            'assets/uploads/images/featuredcollections/solarstone/tiles2.png',
            'assets/uploads/images/featuredcollections/solarstone/tiles3.png',
        ],
        'highlights' => ['Light reflective finish', 'Helps spaces feel larger', 'Clean base for soft contemporary styling'],
        'bestFor' => 'Compact rooms and bright hospitality-inspired spaces',
        'styleNote' => 'Open, luminous, and quietly luxurious',
    ],
    [
        'slug' => 'textile',
        'name' => 'Textile',
        'accent' => '#b45309',
        'eyebrow' => 'Soft Pattern Finish',
        'headline' => 'Subtle woven-inspired texture for interiors that need calm sophistication.',
        'description' => 'Textile introduces a more tactile, understated expression that fits well in bedrooms, comfort areas, and commercial spaces that benefit from a gentler finish profile.',
        'mainImage' => 'assets/uploads/images/featuredcollections/textile/textile.png',
        'tiles' => [
            'assets/uploads/images/featuredcollections/textile/tiles.png',
            'assets/uploads/images/featuredcollections/textile/tiles2.png',
            'assets/uploads/images/featuredcollections/textile/tiles3.png',
        ],
        'highlights' => ['Soft visual texture', 'Understated premium look', 'Works beautifully with warm lighting'],
        'bestFor' => 'Bedrooms, lounges, and comfort-first spaces',
        'styleNote' => 'Quiet texture with boutique-hotel softness',
    ],
];
?>

<style>
.site-header .main-nav {
    margin-bottom: 0;
}

.collections-page {
    background:
        radial-gradient(circle at top left, rgba(249,115,22,.08), transparent 28%),
        radial-gradient(circle at top right, rgba(37,99,235,.07), transparent 24%),
        linear-gradient(180deg, #ffffff 0%, #f6f8fc 52%, #eef3f8 100%);
    margin-top: 0;
    padding: 0 1.5rem 4.75rem;
}

.collections-shell {
    max-width: 1280px;
    margin: 0 auto;
}

.collections-header {
    text-align: center;
    max-width: 920px;
    margin: 0 auto 2.4rem;
}

.collections-kicker {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .5rem .9rem;
    border-radius: 999px;
    background: rgba(249,115,22,.1);
    color: #c2410c;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-top: .55rem;
    margin-bottom: 1rem;
}

.collections-kicker::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    box-shadow: 0 0 0 6px rgba(249,115,22,.12);
}

.collections-header h1 {
    margin: 0 0 .9rem;
    font-size: clamp(2rem, 4vw, 3.35rem);
    color: #0f172a;
    line-height: 1.05;
}

.collections-header p {
    margin: 0;
    color: #64748b;
    line-height: 1.8;
}

.collections-metrics {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1.6rem;
}

.collections-metric-card {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 1.1rem 1.2rem;
    text-align: left;
    background: rgba(255,255,255,.82);
    border: 1px solid rgba(148,163,184,.18);
    box-shadow: 0 16px 38px rgba(15,23,42,.06);
}

.collections-metric-card::before {
    content: '';
    position: absolute;
    inset: auto 0 0 0;
    height: 4px;
    background: linear-gradient(90deg, #f97316, #fb923c);
}

.collections-metric-card strong {
    display: block;
    margin-bottom: .35rem;
    font-size: 1.05rem;
    color: #0f172a;
}

.collections-metric-card span {
    color: #64748b;
    line-height: 1.65;
    font-size: .95rem;
}

.collections-showcase {
    display: grid;
    gap: 1.5rem;
}

.collections-nav {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 1rem;
}

.collection-tab {
    --collection-accent: #f97316;
    width: 100%;
    border: 1px solid rgba(15,23,42,.08);
    background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.98));
    border-radius: 28px;
    padding: .85rem;
    box-shadow: 0 16px 32px rgba(15,23,42,.06);
    text-align: left;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, background .22s ease;
    cursor: pointer;
    display: grid;
    gap: .85rem;
}

.collection-tab:hover,
.collection-tab:focus-visible {
    transform: translateY(-2px);
    box-shadow: 0 22px 38px rgba(15,23,42,.1);
    border-color: color-mix(in srgb, var(--collection-accent) 28%, white);
    outline: none;
}

.collection-tab.is-active {
    background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,247,237,.95));
    border-color: color-mix(in srgb, var(--collection-accent) 32%, white);
    box-shadow: 0 26px 44px rgba(15,23,42,.12);
}

.collection-tab-media {
    position: relative;
    height: 120px;
    border-radius: 20px;
    overflow: hidden;
    background: #e2e8f0;
}

.collection-tab-media::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15,23,42,.08), rgba(15,23,42,.28));
}

.collection-tab-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.collection-tab-body {
    display: grid;
    gap: .35rem;
}

.collection-tab-topline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}

.collection-tab-order {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    padding: .38rem .6rem;
    border-radius: 999px;
    background: rgba(241,245,249,.96);
    color: #315d92;
    font-size: .8rem;
    font-weight: 800;
}

.collection-tab.is-active .collection-tab-order {
    background: var(--collection-accent);
    color: #fff;
}

.collection-tab small {
    display: block;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--collection-accent);
    margin-bottom: 0;
}

.collection-tab strong {
    display: block;
    font-size: 1.15rem;
    color: #10233d;
    margin-bottom: 0;
}

.collection-tab-summary {
    display: block;
    color: #64748b;
    font-size: .9rem;
    line-height: 1.55;
}

.collections-stage {
    position: relative;
}

.collection-panel {
    display: none;
    border-radius: 36px;
    border: 1px solid rgba(148,163,184,.16);
    background: rgba(255,255,255,.72);
    box-shadow: 0 30px 60px rgba(15,23,42,.08);
    overflow: hidden;
}

.collection-panel.is-active {
    display: block;
}

.collection-panel-wrap {
    --collection-accent: #f97316;
    display: grid;
    grid-template-columns: minmax(0, 1.02fr) minmax(0, 1.18fr);
    align-items: stretch;
}

.collection-panel-copy {
    padding: 2.4rem 2.25rem;
    display: grid;
    gap: 1.25rem;
    align-content: center;
    background: linear-gradient(180deg, rgba(255,255,255,.88), rgba(248,250,252,.94));
}

.collection-copy-head {
    display: grid;
    gap: .9rem;
}

.collection-copy-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .85rem;
}

.collection-chip {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    padding: .55rem .85rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--collection-accent) 12%, white);
    color: color-mix(in srgb, var(--collection-accent) 88%, black 12%);
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.collection-index-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .45rem .8rem;
    border-radius: 999px;
    background: rgba(15,23,42,.05);
    color: #475569;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
}

.collection-panel-copy h2 {
    margin: 0;
    font-size: clamp(2rem, 3vw, 3.15rem);
    line-height: 1.04;
    color: #10233d;
    max-width: 520px;
}

.collection-panel-copy p {
    margin: 0;
    color: #64748b;
    line-height: 1.8;
    max-width: 520px;
}

.collection-insight-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .9rem;
}

.collection-insight-card {
    border-radius: 22px;
    padding: 1rem 1.05rem;
    background: #fff;
    border: 1px solid rgba(148,163,184,.16);
    box-shadow: 0 14px 28px rgba(15,23,42,.05);
}

.collection-insight-card span {
    display: block;
    margin-bottom: .35rem;
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: color-mix(in srgb, var(--collection-accent) 78%, black 22%);
}

.collection-insight-card strong {
    display: block;
    color: #10233d;
    line-height: 1.5;
    font-size: .96rem;
}

.collection-points {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: .85rem;
}

.collection-points li {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    color: #30445f;
    line-height: 1.6;
    background: rgba(255,255,255,.88);
    padding: 1rem 1.05rem;
    border-radius: 22px;
    border: 1px solid rgba(15,23,42,.07);
    box-shadow: 0 12px 24px rgba(15,23,42,.04);
}

.collection-points li::before {
    content: '';
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--collection-accent);
    box-shadow: 0 0 0 6px color-mix(in srgb, var(--collection-accent) 14%, white);
    flex-shrink: 0;
    margin-top: .45rem;
}

.collection-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .9rem;
    margin-top: .2rem;
}

.collection-actions .btn,
.collection-next-btn {
    min-height: 50px;
    border-radius: 999px;
    padding: .9rem 1.4rem;
    font-weight: 700;
}

.collection-next-btn {
    border: 1px solid rgba(15,23,42,.12);
    background: #fff;
    color: #10233d;
    box-shadow: 0 14px 28px rgba(15,23,42,.07);
}

.collection-next-btn:hover,
.collection-next-btn:focus-visible {
    border-color: color-mix(in srgb, var(--collection-accent) 28%, white);
    color: color-mix(in srgb, var(--collection-accent) 82%, black 18%);
    outline: none;
}

.collection-panel-media {
    position: relative;
    min-height: 100%;
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--collection-accent) 18%, white) 0%, transparent 42%),
        linear-gradient(180deg, rgba(247,250,252,.94), rgba(234,241,248,.9));
}

.collection-stage-shell {
    position: relative;
    width: 100%;
    min-height: 100%;
}

.collection-stage-shell::before {
    content: '';
    position: relative;
    inset: 0;
    border-radius: 36px;
    background: linear-gradient(180deg, rgba(255,255,255,.65), rgba(255,255,255,.28));
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.35);
}

.collection-showcase-frame {
    position: relative;
    z-index: 1;
    min-height: 620px;
}

.collection-visual-card {
    position: absolute;
    inset: 70px 84px 86px 72px;
    overflow: hidden;
    border-radius: 34px;
    box-shadow: 0 28px 52px rgba(15,23,42,.14);
    background: #dbe4ee;
}

.collection-hero-hit {
    appearance: none;
    border: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    background: transparent;
    position: relative;
    display: block;
}

.collection-hero-hit img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}

.collection-hero-hit::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(8,12,20,.05) 0%, rgba(8,12,20,.38) 100%);
    pointer-events: none;
}

.collection-overlay-head,
.collection-overlay-micro,
.collection-side-swatch,
.collection-swatch-stack,
.collection-nav-arrow {
    position: absolute;
    z-index: 2;
}

.collection-overlay-head {
    left: 2rem;
    right: 2rem;
    bottom: 1.65rem;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
}

.collection-overlay-head strong {
    display: block;
    font-size: clamp(1.8rem, 3.2vw, 2.8rem);
    color: #fff;
    line-height: 1;
    margin-bottom: .45rem;
}

.collection-overlay-head span,
.collection-overlay-micro {
    color: rgba(255,255,255,.84);
    font-size: .92rem;
    line-height: 1.6;
    max-width: 280px;
}

.collection-overlay-micro {
    top: 1.4rem;
    left: 1.4rem;
    max-width: none;
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    width: fit-content;
    padding: .7rem .9rem;
    border-radius: 999px;
    background: rgba(15,23,42,.44);
    backdrop-filter: blur(12px);
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.collection-swatch-stack {
    right: 0;
    top: 0;
    width: 178px;
    display: grid;
    gap: .85rem;
}

.collection-side-swatch {
    position: relative;
    overflow: hidden;
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 24px;
    background: #fff;
    box-shadow: 0 22px 38px rgba(15,23,42,.12);
    border: 8px solid rgba(255,255,255,.92);
}

.collection-side-swatch img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.collection-side-swatch-label {
    position: absolute;
    left: .85rem;
    bottom: .8rem;
    padding: .35rem .55rem;
    border-radius: 999px;
    background: rgba(15,23,42,.62);
    color: #fff;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.collection-spec-card {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 230px;
    border-radius: 28px;
    background: rgba(255,255,255,.94);
    border: 1px solid rgba(148,163,184,.16);
    box-shadow: 0 24px 42px rgba(15,23,42,.1);
    padding: 1rem;
    z-index: 3;
}

.collection-spec-card + .collection-spec-card-secondary {
    margin-top: .7rem;
}

.collection-spec-card span {
    display: block;
    margin-bottom: .3rem;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: color-mix(in srgb, var(--collection-accent) 78%, black 22%);
}

.collection-spec-card strong {
    display: block;
    font-size: .98rem;
    color: #10233d;
    line-height: 1.55;
}

.collection-gallery-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .85rem;
    margin-top: .85rem;
}

.collection-gallery-tile {
    position: relative;
    overflow: hidden;
    border-radius: 18px;
    background: #eef2f7;
    aspect-ratio: 1 / 1;
}

.collection-gallery-tile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.collection-nav-arrow {
    top: 50%;
    transform: translateY(-50%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 1px solid rgba(15,23,42,.08);
    background: rgba(255,255,255,.96);
    color: #10233d;
    box-shadow: 0 18px 34px rgba(15,23,42,.12);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease, color .2s ease, border-color .2s ease;
}

.collection-nav-arrow:hover,
.collection-nav-arrow:focus-visible {
    color: #f97316;
    border-color: rgba(249,115,22,.26);
    box-shadow: 0 22px 40px rgba(249,115,22,.14);
    outline: none;
}

.collection-nav-arrow.is-prev {
    left: 14px;
}

.collection-nav-arrow.is-next {
    right: 14px;
}

.collection-nav-arrow svg {
    width: 24px;
    height: 24px;
}

.collections-helper {
    margin-top: 1.15rem;
    text-align: center;
    color: #64748b;
    font-size: .95rem;
}

@media (max-width: 1100px) {
    .collections-metrics {
        grid-template-columns: 1fr;
    }

    .collections-nav {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .collection-panel-wrap {
        grid-template-columns: 1fr;
    }

    .collection-panel-copy,
    .collection-panel-media {
        padding: 2rem;
    }

    .collection-showcase-frame {
        min-height: 560px;
    }
}

@media (max-width: 768px) {
    .collections-page {
        padding: 0 1rem 3.25rem;
    }

    .collections-header {
        margin-bottom: 1.35rem;
    }

    .collections-header p,
    .collection-panel-copy p,
    .collections-helper {
        font-size: .95rem;
        line-height: 1.68;
    }

    .collections-nav {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .collection-tab-media {
        height: 104px;
    }

    .collection-insight-grid {
        grid-template-columns: 1fr;
    }

    .collection-showcase-frame {
        min-height: 520px;
    }

    .collection-visual-card {
        inset: 84px 0 136px 0;
    }

    .collection-swatch-stack {
        position: relative;
        top: auto;
        right: auto;
        width: 100%;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        padding-top: 418px;
    }

    .collection-side-swatch {
        border-radius: 18px;
    }

    .collection-spec-card {
        position: relative;
        width: 100%;
        margin-top: 1rem;
    }

    .collection-nav-arrow {
        width: 52px;
        height: 52px;
        top: auto;
        bottom: 70px;
        transform: none;
    }

    .collection-nav-arrow.is-prev {
        left: 14px;
    }

    .collection-nav-arrow.is-next {
        right: 14px;
    }

    .collection-overlay-head {
        left: 1.3rem;
        right: 1.3rem;
        bottom: 1.3rem;
        align-items: flex-start;
        flex-direction: column;
    }
}

@media (max-width: 420px) {
    .collections-page {
        padding: 0 .85rem 2.6rem;
    }

    .collections-header h1 {
        font-size: 1.8rem;
    }

    .collection-panel-media {
        min-height: 320px;
    }

    .collections-nav {
        grid-template-columns: 1fr;
    }

    .collection-panel-copy,
    .collection-panel-media {
        padding: 1.2rem;
    }

    .collection-showcase-frame {
        min-height: 470px;
    }

    .collection-visual-card {
        inset: 74px 0 154px 0;
    }

    .collection-swatch-stack {
        padding-top: 350px;
        gap: .55rem;
    }

    .collection-overlay-head strong {
        font-size: 1.5rem;
    }

    .collection-overlay-head span,
    .collection-overlay-micro,
    .collection-tab-summary {
        font-size: .85rem;
    }
}
</style>

<div class="collections-page">
    <div class="collections-shell">
        <header class="collections-header">
            <div class="collections-kicker">Design-Led Selections</div>
            <h1>Featured Collections</h1>
            <p>Curated surface stories built from your featured collections image assets. Explore each concept through a polished editorial layout with hero scenes, finish swatches, and styling cues that make every collection feel premium at first glance.</p>

            <div class="collections-metrics" aria-hidden="true">
                <div class="collections-metric-card">
                    <strong>5 curated concepts</strong>
                    <span>Each collection presents a distinct interior mood so shoppers can compare finishes faster.</span>
                </div>
                <div class="collections-metric-card">
                    <strong>Scene-led presentation</strong>
                    <span>Main visuals and tile swatches now work together in a cleaner, showroom-inspired composition.</span>
                </div>
                <div class="collections-metric-card">
                    <strong>Professional browsing flow</strong>
                    <span>Clear navigation, strong hierarchy, and refined spacing make the experience feel high-end.</span>
                </div>
            </div>
        </header>

        <section class="collections-showcase">
            <div class="collections-nav" role="tablist" aria-label="Featured collection selector">
                <?php foreach ($featuredCollections as $index => $collection): ?>
                    <button
                        type="button"
                        class="collection-tab<?= $index === 0 ? ' is-active' : '' ?>"
                        data-collection-trigger="<?= $index ?>"
                        style="--collection-accent: <?= htmlspecialchars($collection['accent']) ?>;"
                        role="tab"
                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                        aria-controls="collection-panel-<?= htmlspecialchars($collection['slug']) ?>"
                    >
                        <span class="collection-tab-media" aria-hidden="true">
                            <img src="<?= APP_URL ?>/<?= htmlspecialchars($collection['mainImage']) ?>" alt="">
                        </span>
                        <span class="collection-tab-body">
                            <span class="collection-tab-topline">
                                <small><?= htmlspecialchars($collection['eyebrow']) ?></small>
                                <span class="collection-tab-order"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            </span>
                            <strong><?= htmlspecialchars($collection['name']) ?></strong>
                            <span class="collection-tab-summary"><?= htmlspecialchars($collection['styleNote']) ?></span>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="collections-stage">
                <?php foreach ($featuredCollections as $index => $collection): ?>
                    <article
                        id="collection-panel-<?= htmlspecialchars($collection['slug']) ?>"
                        class="collection-panel<?= $index === 0 ? ' is-active' : '' ?>"
                        data-collection-panel="<?= $index ?>"
                        style="--collection-accent: <?= htmlspecialchars($collection['accent']) ?>;"
                        role="tabpanel"
                        aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>"
                    >
                        <div class="collection-panel-wrap">
                            <div class="collection-panel-copy">
                                <div class="collection-copy-head">
                                    <div class="collection-copy-meta">
                                        <div class="collection-chip"><?= htmlspecialchars($collection['eyebrow']) ?></div>
                                        <div class="collection-index-pill">Collection <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></div>
                                    </div>
                                    <h2><?= htmlspecialchars($collection['headline']) ?></h2>
                                </div>

                                <p><?= htmlspecialchars($collection['description']) ?></p>

                                <div class="collection-insight-grid">
                                    <div class="collection-insight-card">
                                        <span>Best for</span>
                                        <strong><?= htmlspecialchars($collection['bestFor']) ?></strong>
                                    </div>
                                    <div class="collection-insight-card">
                                        <span>Design note</span>
                                        <strong><?= htmlspecialchars($collection['styleNote']) ?></strong>
                                    </div>
                                </div>

                                <ul class="collection-points">
                                    <?php foreach ($collection['highlights'] as $highlight): ?>
                                        <li><?= htmlspecialchars($highlight) ?></li>
                                    <?php endforeach; ?>
                                </ul>

                                <div class="collection-actions">
                                    <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">Explore Products</a>
                                    <a href="<?= APP_URL ?>/index.php?url=room-gallery" class="btn btn-outline">View Room Gallery</a>
                                    <button type="button" class="collection-next-btn" data-collection-next>Next Collection</button>
                                </div>
                            </div>

                            <div class="collection-panel-media">
                                <div class="collection-stage-shell">
                                    <div class="collection-showcase-frame">
                                        <div class="collection-visual-card">
                                            <button
                                                type="button"
                                                class="collection-hero-hit"
                                                data-collection-next
                                                aria-label="Show next featured collection"
                                            >
                                                <img class="collection-primary-image" src="<?= APP_URL ?>/<?= htmlspecialchars($collection['mainImage']) ?>" alt="<?= htmlspecialchars($collection['name']) ?> featured collection preview">
                                                <span class="collection-overlay-micro">Tap hero image to keep browsing</span>
                                                <span class="collection-overlay-head">
                                                    <span>
                                                        <strong><?= htmlspecialchars($collection['name']) ?></strong>
                                                        <?= htmlspecialchars($collection['styleNote']) ?>
                                                    </span>
                                                    <span><?= count($collection['tiles']) ?> coordinated tile variations</span>
                                                </span>
                                            </button>
                                        </div>

                                        <div class="collection-swatch-stack" aria-hidden="true">
                                            <?php foreach ($collection['tiles'] as $tileIndex => $tile): ?>
                                                <div class="collection-side-swatch">
                                                    <img src="<?= APP_URL ?>/<?= htmlspecialchars($tile) ?>" alt="<?= htmlspecialchars($collection['name']) ?> tile variation <?= $tileIndex + 1 ?>">
                                                    <span class="collection-side-swatch-label">Finish <?= $tileIndex + 1 ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="collection-spec-card">
                                            <span>Signature mood</span>
                                            <strong><?= htmlspecialchars($collection['bestFor']) ?></strong>

                                            <div class="collection-gallery-strip">
                                                <?php foreach ($collection['tiles'] as $tileIndex => $tile): ?>
                                                    <div class="collection-gallery-tile">
                                                        <img src="<?= APP_URL ?>/<?= htmlspecialchars($tile) ?>" alt="<?= htmlspecialchars($collection['name']) ?> finish detail <?= $tileIndex + 1 ?>">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <button type="button" class="collection-nav-arrow is-prev" data-collection-prev aria-label="Show previous featured collection">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="m15 18-6-6 6-6"></path>
                                            </svg>
                                        </button>

                                        <button type="button" class="collection-nav-arrow is-next" data-collection-next aria-label="Show next featured collection">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="m9 6 6 6-6 6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <p class="collections-helper">Tip: use the selector cards, the next button, or tap the hero image to move through Grove, Lithos, Slate, Solarstone, and Textile.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(document.querySelectorAll('[data-collection-trigger]'));
    const panels = Array.from(document.querySelectorAll('[data-collection-panel]'));
    const nextTriggers = Array.from(document.querySelectorAll('[data-collection-next]'));
    const prevTriggers = Array.from(document.querySelectorAll('[data-collection-prev]'));

    if (!tabs.length || !panels.length) {
        return;
    }

    let activeIndex = tabs.findIndex((tab) => tab.classList.contains('is-active'));
    activeIndex = activeIndex >= 0 ? activeIndex : 0;

    const setActiveCollection = (index) => {
        activeIndex = index;

        tabs.forEach((tab, tabIndex) => {
            const isActive = tabIndex === index;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach((panel, panelIndex) => {
            const isActive = panelIndex === index;
            panel.classList.toggle('is-active', isActive);
            panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', function () {
            setActiveCollection(index);
        });
    });

    nextTriggers.forEach((trigger) => {
        trigger.addEventListener('click', function () {
            setActiveCollection((activeIndex + 1) % panels.length);
        });

        if (trigger.classList.contains('collection-nav-arrow')) {
            trigger.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }
    });

    prevTriggers.forEach((trigger) => {
        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            setActiveCollection((activeIndex - 1 + panels.length) % panels.length);
        });
    });

    panels.forEach((panel) => {
        const primaryHit = panel.querySelector('.collection-image-hit');

        if (!primaryHit) {
            return;
        }

        primaryHit.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            setActiveCollection((activeIndex + 1) % panels.length);
        });
    });
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
