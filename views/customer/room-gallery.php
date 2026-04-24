<?php
$roomGalleryConfig = [
    'livingroom' => [
        'title' => 'Living Room Ideas',
        'eyebrow' => 'Warm & welcoming',
        'description' => 'Layer tile, wood, and stone looks that make gathering spaces feel polished, comfortable, and easy to maintain.',
        'accent' => '#f97316',
    ],
    'kitchen' => [
        'title' => 'Kitchen Looks',
        'eyebrow' => 'Clean & hardworking',
        'description' => 'Explore surfaces and finishes designed for daily use, quick cleaning, and a timeless modern feel.',
        'accent' => '#0f766e',
    ],
    'bathroom' => [
        'title' => 'Bathroom Concepts',
        'eyebrow' => 'Calm & refined',
        'description' => 'See combinations that balance spa-like comfort, moisture resistance, and visual detail.',
        'accent' => '#2563eb',
    ],
    'bedroom' => [
        'title' => 'Bedroom Retreats',
        'eyebrow' => 'Soft & restful',
        'description' => 'Find finishes and mood boards that turn private rooms into elegant, relaxing retreats.',
        'accent' => '#7c3aed',
    ],
    'dining' => [
        'title' => 'Dining Inspirations',
        'eyebrow' => 'Gather in style',
        'description' => 'Browse statement surfaces and coordinated tones built for conversation-worthy dining areas.',
        'accent' => '#dc2626',
    ],
    'commercial' => [
        'title' => 'Commercial Spaces',
        'eyebrow' => 'Durable & professional',
        'description' => 'Review practical concepts for offices, retail spaces, and projects that demand performance and style.',
        'accent' => '#111827',
    ],
    'outside' => [
        'title' => 'Outdoor Spaces',
        'eyebrow' => 'Built for the elements',
        'description' => 'Discover weather-ready designs for facades, patios, and exterior areas with strong curb appeal.',
        'accent' => '#15803d',
    ],
];

$roomGalleryBaseDir = ROOT_PATH . '/assets/uploads/images/roomgallery';
$roomGalleryBaseUrl = APP_URL . '/assets/uploads/images/roomgallery';
$roomGalleryCollections = [];
$lightboxCollections = [];
$totalGalleryImages = 0;

foreach ($roomGalleryConfig as $slug => $config) {
    $imagePaths = glob($roomGalleryBaseDir . '/' . $slug . '/*.{jpg,jpeg,png,webp,avif}', GLOB_BRACE) ?: [];
    natsort($imagePaths);
    $imagePaths = array_values($imagePaths);

    if (empty($imagePaths)) {
        continue;
    }

    $images = [];
    foreach ($imagePaths as $imagePath) {
        $fileName = basename($imagePath);
        $images[] = [
            'src' => $roomGalleryBaseUrl . '/' . rawurlencode($slug) . '/' . rawurlencode($fileName),
            'alt' => $config['title'] . ' inspiration - ' . preg_replace('/\.[^.]+$/', '', $fileName),
        ];
    }

    $totalGalleryImages += count($images);

    $roomGalleryCollections[] = [
        'slug' => $slug,
        'title' => $config['title'],
        'eyebrow' => $config['eyebrow'],
        'description' => $config['description'],
        'accent' => $config['accent'],
        'cover' => $images[0]['src'],
        'count' => count($images),
        'images' => $images,
    ];

    $lightboxCollections[$slug] = [
        'title' => $config['title'],
        'eyebrow' => $config['eyebrow'],
        'description' => $config['description'],
        'images' => $images,
    ];
}

$featuredCollection = $roomGalleryCollections[0] ?? null;
$secondaryCollections = array_slice($roomGalleryCollections, 1, 2);

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<style>
.site-header .main-nav {
    margin-bottom: 0;
}

.gallery-page {
    --gallery-surface: #ffffff;
    --gallery-border: rgba(15, 23, 42, 0.08);
    --gallery-text: #0f172a;
    --gallery-muted: #64748b;
    --gallery-shadow: 0 20px 55px rgba(15, 23, 42, 0.08);
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.14), transparent 28%),
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 24%),
        linear-gradient(180deg, #fff7ed 0%, #f8fafc 14%, #eff6ff 100%);
    padding: .75rem 1.25rem 4.5rem;
}

.gallery-shell {
    max-width: 1240px;
    margin: 0 auto;
}

.gallery-hero {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
    gap: 1.5rem;
    padding: 2rem;
    border-radius: 32px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.72);
    box-shadow: var(--gallery-shadow);
    backdrop-filter: blur(16px);
}

.gallery-hero::before,
.gallery-hero::after {
    content: '';
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
}

.gallery-hero::before {
    width: 320px;
    height: 320px;
    right: -120px;
    top: -160px;
    background: rgba(249, 115, 22, 0.18);
}

.gallery-hero::after {
    width: 280px;
    height: 280px;
    left: -160px;
    bottom: -180px;
    background: rgba(37, 99, 235, 0.12);
}

.gallery-hero-content,
.gallery-hero-visual {
    position: relative;
    z-index: 1;
}

.gallery-hero h1 {
    margin: 0;
    font-size: clamp(2.5rem, 4.5vw, 4.3rem);
    line-height: 1.05;
    color: var(--gallery-text);
    letter-spacing: -.03em;
}

.gallery-hero p {
    max-width: 620px;
    margin: 1rem 0 0;
    color: var(--gallery-muted);
    font-size: 1.04rem;
    line-height: 1.85;
}

.gallery-highlight-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .85rem;
    margin-top: 1.6rem;
}

.gallery-highlight-card {
    padding: 1rem 1.1rem;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.88);
    border: 1px solid rgba(15, 23, 42, 0.08);
}

.gallery-highlight-card strong {
    display: block;
    margin-bottom: .25rem;
    font-size: 1.2rem;
    color: var(--gallery-text);
}

.gallery-highlight-card span {
    color: var(--gallery-muted);
    font-size: .92rem;
}

.gallery-hero-visual {
    display: grid;
    grid-template-columns: 1.15fr .85fr;
    gap: .85rem;
    min-height: 460px;
}

.gallery-feature-card,
.gallery-mini-card {
    position: relative;
    overflow: hidden;
    border: 0;
    padding: 0;
    box-shadow: 0 24px 40px rgba(15, 23, 42, 0.14);
    cursor: pointer;
}

.gallery-feature-card,
.gallery-mini-card,
.gallery-card-link,
.gallery-thumb,
.gallery-modal-thumb,
.gallery-modal-close,
.gallery-modal-arrow,
.gallery-chip {
    transition: transform .22s ease, box-shadow .22s ease, opacity .22s ease, background .22s ease, border-color .22s ease;
}

.gallery-feature-card:hover,
.gallery-mini-card:hover,
.gallery-card-link:hover,
.gallery-thumb:hover,
.gallery-modal-thumb:hover,
.gallery-modal-close:hover {
    transform: translateY(-2px);
}

.gallery-feature-card {
    min-height: 460px;
    border-radius: 28px;
}

.gallery-feature-card img,
.gallery-mini-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.gallery-feature-overlay,
.gallery-mini-overlay {
    position: absolute;
    inset: auto 0 0 0;
    padding: 1.25rem;
    color: #fff;
    background: linear-gradient(180deg, transparent 0%, rgba(15, 23, 42, 0.9) 100%);
}

.gallery-feature-overlay strong,
.gallery-mini-overlay strong {
    display: block;
    font-size: 1.05rem;
    margin-bottom: .25rem;
}

.gallery-feature-overlay span,
.gallery-mini-overlay span {
    font-size: .88rem;
    opacity: .92;
}

.gallery-mini-stack {
    display: grid;
    gap: .85rem;
}

.gallery-mini-card {
    border-radius: 24px;
    min-height: 222px;
}

.gallery-section-head {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
    margin: 2.1rem 0 1.15rem;
}

.gallery-section-head h2 {
    margin: 0;
    color: var(--gallery-text);
    font-size: clamp(1.6rem, 2.4vw, 2.2rem);
}

.gallery-section-head p {
    margin: .35rem 0 0;
    color: var(--gallery-muted);
    max-width: 700px;
    line-height: 1.75;
}

.gallery-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: .7rem;
}

.gallery-chip {
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: rgba(255, 255, 255, 0.84);
    color: var(--gallery-text);
    border-radius: 999px;
    padding: .8rem 1.05rem;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
}

.gallery-chip:hover,
.gallery-chip.is-active {
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
    background: #fff;
    border-color: rgba(249, 115, 22, 0.35);
}

.gallery-collection-grid {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 1.25rem;
}

.gallery-collection-card {
    grid-column: span 6;
    position: relative;
    overflow: hidden;
    background: rgba(255, 255, 255, 1);
    border: 1px solid rgba(255, 255, 255, 1);
    border-radius: 28px;
    box-shadow: var(--gallery-shadow);
    backdrop-filter: blur(12px);
}

.gallery-card-media {
    position: relative;
    aspect-ratio: 16 / 10;
    overflow: hidden;
}

.gallery-card-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .45s ease;
}

.gallery-collection-card:hover .gallery-card-media img {
    transform: scale(1.04);
}

.gallery-card-media::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.04) 10%, rgba(15, 23, 42, 0.72) 100%);
}

.gallery-card-body {
    position: relative;
    margin-top: -70px;
    z-index: 1;
    padding: 0 1.25rem 1.25rem;
}

.gallery-card-panel {
    background: rgba(255, 255, 255, 1);
    border: 1px solid rgba(15, 23, 42, 0.06);
    border-radius: 24px;
    padding: 1.3rem;
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
}

.gallery-card-panel h3 {
    margin: .2rem 0 .65rem;
    color: var(--gallery-text);
    font-size: 1.4rem;
}

.gallery-card-panel p {
    margin: 0;
    color: var(--gallery-muted);
    line-height: 1.75;
}

.gallery-thumb-row {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .7rem;
    margin-top: 1rem;
}

.gallery-thumb {
    position: relative;
    overflow: hidden;
    border: 0;
    border-radius: 16px;
    padding: 0;
    cursor: pointer;
    background: #e2e8f0;
    aspect-ratio: 1 / 1;
}

.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .25s ease, opacity .25s ease;
}

.gallery-thumb:hover img,
.gallery-thumb:focus-visible img {
    transform: scale(1.06);
    opacity: .95;
}

.gallery-thumb-more {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.58);
    color: #fff;
    font-weight: 700;
    letter-spacing: .02em;
}

.gallery-card-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.1rem;
}

.gallery-card-link {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    border: 0;
    border-radius: 999px;
    padding: .85rem 1.1rem;
    font: inherit;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.14);
}

.gallery-card-link i {
    width: 16px;
    height: 16px;
}

.gallery-card-note {
    color: var(--gallery-muted);
    font-size: .92rem;
}

.gallery-empty-state {
    padding: 2rem;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.82);
    border: 1px dashed rgba(15, 23, 42, 0.15);
    text-align: center;
    color: var(--gallery-muted);
}

.gallery-modal {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity .28s ease, visibility 0s linear .28s;
}

.gallery-modal.is-open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transition: opacity .28s ease;
}

.gallery-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.72);
    backdrop-filter: blur(8px);
    opacity: 0;
    transition: opacity .28s ease;
}

.gallery-modal-dialog {
    position: relative;
    z-index: 1;
    width: min(1140px, 100%);
    max-height: min(92vh, 900px);
    display: grid;
    grid-template-columns: 92px minmax(0, 1fr);
    gap: .85rem;
    align-items: start;
    opacity: 0;
    transform: translateY(18px) scale(.985);
    transition: opacity .28s ease, transform .28s ease;
}

.gallery-modal.is-open .gallery-modal-backdrop,
.gallery-modal.is-open .gallery-modal-dialog {
    opacity: 1;
}

.gallery-modal.is-open .gallery-modal-dialog {
    transform: translateY(0) scale(1);
}

.gallery-modal-rail {
    display: flex;
    flex-direction: column;
    gap: .6rem;
    overflow-y: auto;
    overflow-x: visible;
    padding: .35rem .45rem .35rem .15rem;
}

.gallery-modal-thumb {
    border: 0;
    padding: .38rem;
    overflow: hidden;
    display: block;
    width: 100%;
    border-radius: 20px;
    cursor: pointer;
    opacity: .82;
    background: rgba(255, 255, 255, 0.96);
    aspect-ratio: 1 / 1;
    box-sizing: border-box;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
}

.gallery-modal-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    border-radius: 15px;
    background: #ffffff;
}

.gallery-modal-thumb.is-active {
    opacity: 1;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.9), 0 14px 28px rgba(15, 23, 42, 0.22);
}

.gallery-modal-panel {
    position: relative;
    overflow: hidden;
    align-self: start;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.3);
}

.gallery-modal-header {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 1rem;
    padding: .9rem 1rem 0;
}

.gallery-modal-header small {
    display: block;
    margin-bottom: .35rem;
    color: #f97316;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
}

.gallery-modal-header h3 {
    margin: 0;
    color: var(--gallery-text);
    font-size: 1.4rem;
}

.gallery-modal-header p {
    margin: .45rem 0 0;
    color: var(--gallery-muted);
    line-height: 1.7;
}

.gallery-modal-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: #fff;
    color: var(--gallery-text);
    cursor: pointer;
    flex-shrink: 0;
}

.gallery-modal-stage {
    position: relative;
    padding: .7rem .7rem 0;
}

.gallery-modal-figure {
    position: relative;
    background: #0f172a;
    border-radius: 20px;
    overflow: hidden;
    min-height: min(66vh, 720px);
}

.gallery-modal-figure img {
    width: 100%;
    height: min(66vh, 720px);
    object-fit: contain;
    display: block;
    background: #0f172a;
}

.gallery-modal-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 54px;
    height: 54px;
    border: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.92);
    color: var(--gallery-text);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
    cursor: pointer;
}

.gallery-modal-arrow:hover,
.gallery-modal-arrow:focus-visible {
    transform: translateY(-50%);
    background: #ffffff;
    box-shadow: 0 18px 34px rgba(15, 23, 42, 0.22);
}

.gallery-modal-arrow--prev { left: 1.2rem; }
.gallery-modal-arrow--next { right: 1.2rem; }

.gallery-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .8rem 1rem 1rem;
}

.gallery-modal-counter {
    color: var(--gallery-muted);
    font-weight: 600;
}

.gallery-modal-hint {
    color: var(--gallery-muted);
    font-size: .92rem;
}

.gallery-hidden {
    display: none !important;
}

body.gallery-modal-open {
    overflow: hidden;
}

@media (max-width: 1100px) {
    .gallery-hero,
    .gallery-modal-dialog {
        grid-template-columns: 1fr;
    }

    .gallery-hero-visual {
        min-height: auto;
    }

    .gallery-feature-card {
        min-height: 360px;
    }

    .gallery-modal-rail {
        order: 2;
        flex-direction: row;
        padding: 0;
    }

    .gallery-modal-thumb {
        width: 68px;
        min-width: 68px;
    }
}

@media (max-width: 900px) {
    .gallery-collection-card {
        grid-column: span 12;
    }

    .gallery-highlight-grid,
    .gallery-hero-visual {
        grid-template-columns: 1fr;
    }

    .gallery-section-head,
    .gallery-card-actions,
    .gallery-modal-footer {
        flex-direction: column;
        align-items: start;
    }
}

@media (max-width: 640px) {
    .gallery-page {
        padding: 1.25rem .85rem 3.25rem;
    }

    .gallery-hero {
        padding: 1.2rem;
        border-radius: 26px;
    }

    .gallery-hero h1 {
        font-size: 2.15rem;
    }

    .gallery-feature-card {
        min-height: 290px;
    }

    .gallery-card-body {
        margin-top: -44px;
        padding: 0 .9rem .9rem;
    }

    .gallery-card-panel,
    .gallery-modal-panel {
        border-radius: 22px;
    }

    .gallery-thumb-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .gallery-modal {
        padding: .75rem;
    }

    .gallery-modal-header,
    .gallery-modal-footer {
        padding-left: .85rem;
        padding-right: .85rem;
    }

    .gallery-modal-stage {
        padding: .55rem .55rem 0;
    }

    .gallery-modal-arrow {
        width: 46px;
        height: 46px;
    }

    .gallery-modal-arrow--prev { left: .8rem; }
    .gallery-modal-arrow--next { right: .8rem; }
}
</style>

<div class="gallery-page">
    <div class="gallery-shell">
        <section class="gallery-hero">
            <div class="gallery-hero-content reveal-on-scroll reveal-left">
                <h1>Room Gallery</h1>
                <p>Explore real design directions for every space in the home and beyond. Tap any image set to open a full-screen preview, then move through the collection using the next and previous controls.</p>

                <div class="gallery-highlight-grid">
                    <div class="gallery-highlight-card">
                        <strong>Interactive preview</strong>
                        <span>Click any image to launch a large viewer with next and previous navigation.</span>
                    </div>
                    <div class="gallery-highlight-card">
                        <strong>Room-based browsing</strong>
                        <span>Jump between curated looks for living rooms, kitchens, baths, bedrooms, and more.</span>
                    </div>
                    <div class="gallery-highlight-card">
                        <strong>Material inspiration</strong>
                        <span>Use each concept as a visual guide for tile, finishes, and surface combinations.</span>
                    </div>
                </div>
            </div>

            <div class="gallery-hero-visual reveal-on-scroll reveal-right">
                <?php if ($featuredCollection): ?>
                    <button
                        type="button"
                        class="gallery-feature-card"
                        data-open-gallery="<?= htmlspecialchars($featuredCollection['slug']) ?>"
                        data-start-index="0"
                        aria-label="Open <?= htmlspecialchars($featuredCollection['title']) ?> gallery"
                    >
                        <img src="<?= htmlspecialchars($featuredCollection['cover']) ?>" alt="<?= htmlspecialchars($featuredCollection['title']) ?> showcase image">
                        <span class="gallery-feature-overlay">
                            <strong><?= htmlspecialchars($featuredCollection['title']) ?></strong>
                            <span><?= (int) $featuredCollection['count'] ?> photos · Click to open gallery</span>
                        </span>
                    </button>
                <?php endif; ?>

                <div class="gallery-mini-stack">
                    <?php foreach ($secondaryCollections as $collection): ?>
                        <button
                            type="button"
                            class="gallery-mini-card"
                            data-open-gallery="<?= htmlspecialchars($collection['slug']) ?>"
                            data-start-index="0"
                            aria-label="Open <?= htmlspecialchars($collection['title']) ?> gallery"
                        >
                            <img src="<?= htmlspecialchars($collection['cover']) ?>" alt="<?= htmlspecialchars($collection['title']) ?> preview">
                            <span class="gallery-mini-overlay">
                                <strong><?= htmlspecialchars($collection['title']) ?></strong>
                                <span><?= htmlspecialchars($collection['eyebrow']) ?></span>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <div class="gallery-section-head reveal-on-scroll reveal-left">
            <div>
                <h2>Browse all room concepts</h2>
                <p>Select a collection to focus on a room type, then click any preview image to explore it in a large lightbox just like a next/previous slideshow.</p>
            </div>
            <div class="gallery-chip-row" role="tablist" aria-label="Room gallery filters">
                <button type="button" class="gallery-chip is-active" data-filter="all">All rooms</button>
                <?php foreach ($roomGalleryCollections as $collection): ?>
                    <button type="button" class="gallery-chip" data-filter="<?= htmlspecialchars($collection['slug']) ?>"><?= htmlspecialchars($collection['title']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($roomGalleryCollections)): ?>
            <section class="gallery-collection-grid" id="galleryCollectionGrid">
                <?php foreach ($roomGalleryCollections as $index => $collection): ?>
                    <article class="gallery-collection-card reveal-on-scroll <?= $index % 2 === 0 ? 'reveal-left' : 'reveal-right' ?>" data-category="<?= htmlspecialchars($collection['slug']) ?>">
                        <div class="gallery-card-media">
                            <img src="<?= htmlspecialchars($collection['cover']) ?>" alt="<?= htmlspecialchars($collection['title']) ?> cover image">
                        </div>

                        <div class="gallery-card-body">
                            <div class="gallery-card-panel">
                                <h3><?= htmlspecialchars($collection['title']) ?></h3>
                                <p><?= htmlspecialchars($collection['description']) ?></p>

                                <div class="gallery-thumb-row">
                                    <?php foreach (array_slice($collection['images'], 0, 4) as $index => $image): ?>
                                        <button
                                            type="button"
                                            class="gallery-thumb"
                                            data-open-gallery="<?= htmlspecialchars($collection['slug']) ?>"
                                            data-start-index="<?= (int) $index ?>"
                                            aria-label="Open <?= htmlspecialchars($collection['title']) ?> image <?= (int) ($index + 1) ?>"
                                        >
                                            <img src="<?= htmlspecialchars($image['src']) ?>" alt="<?= htmlspecialchars($image['alt']) ?>">
                                            <?php if ($index === 3 && $collection['count'] > 4): ?>
                                                <span class="gallery-thumb-more">+<?= (int) ($collection['count'] - 4) ?></span>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <div class="gallery-card-actions">
                                    <span class="gallery-card-note">Use arrows, keyboard keys, or thumbnails inside the preview.</span>
                                    <button
                                        type="button"
                                        class="gallery-card-link"
                                        data-open-gallery="<?= htmlspecialchars($collection['slug']) ?>"
                                        data-start-index="0"
                                        style="background: <?= htmlspecialchars($collection['accent']) ?>;"
                                    >
                                        <i data-lucide="expand"></i>
                                        Open gallery
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <div class="gallery-empty-state">
                Room gallery images are not available yet. Add images to the room gallery folders to populate this page.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="gallery-modal" id="roomGalleryModal" aria-hidden="true">
    <div class="gallery-modal-backdrop" data-close-gallery></div>
    <div class="gallery-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="roomGalleryModalTitle">
        <div class="gallery-modal-rail" id="roomGalleryThumbRail"></div>

        <div class="gallery-modal-panel">
            <div class="gallery-modal-header">
                <div>
                    <small id="roomGalleryModalEyebrow">Room collection</small>
                    <h3 id="roomGalleryModalTitle">Room Gallery</h3>
                    <p id="roomGalleryModalDescription">Browse inspiration images.</p>
                </div>
                <button type="button" class="gallery-modal-close" data-close-gallery aria-label="Close gallery">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="gallery-modal-stage">
                <div class="gallery-modal-figure">
                    <button type="button" class="gallery-modal-arrow gallery-modal-arrow--prev" id="roomGalleryPrev" aria-label="Previous image">
                        <i data-lucide="chevron-left"></i>
                    </button>
                    <img id="roomGalleryModalImage" src="" alt="">
                    <button type="button" class="gallery-modal-arrow gallery-modal-arrow--next" id="roomGalleryNext" aria-label="Next image">
                        <i data-lucide="chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="gallery-modal-footer">
                <div class="gallery-modal-counter" id="roomGalleryCounter">1 / 1</div>
                <div class="gallery-modal-hint">Tip: use ← and → keys for previous and next.</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const collections = <?= json_encode($lightboxCollections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const chips = Array.from(document.querySelectorAll('.gallery-chip'));
    const cards = Array.from(document.querySelectorAll('.gallery-collection-card'));
    const openers = Array.from(document.querySelectorAll('[data-open-gallery]'));
    const modal = document.getElementById('roomGalleryModal');
    const modalImage = document.getElementById('roomGalleryModalImage');
    const modalTitle = document.getElementById('roomGalleryModalTitle');
    const modalEyebrow = document.getElementById('roomGalleryModalEyebrow');
    const modalDescription = document.getElementById('roomGalleryModalDescription');
    const modalCounter = document.getElementById('roomGalleryCounter');
    const thumbRail = document.getElementById('roomGalleryThumbRail');
    const prevButton = document.getElementById('roomGalleryPrev');
    const nextButton = document.getElementById('roomGalleryNext');
    const closeButtons = Array.from(document.querySelectorAll('[data-close-gallery]'));

    let activeCollection = null;
    let activeIndex = 0;
    let lastTrigger = null;

    function refreshIcons() {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function setFilter(filter) {
        chips.forEach(function (chip) {
            chip.classList.toggle('is-active', chip.dataset.filter === filter);
        });

        cards.forEach(function (card) {
            const isVisible = filter === 'all' || card.dataset.category === filter;
            card.classList.toggle('gallery-hidden', !isVisible);
        });
    }

    function renderThumbRail(collectionKey) {
        const collection = collections[collectionKey];
        if (!collection) {
            thumbRail.innerHTML = '';
            return;
        }

        thumbRail.innerHTML = collection.images.map(function (image, index) {
            const activeClass = index === activeIndex ? ' is-active' : '';
            const safeAlt = String(image.alt || collection.title || 'Gallery image').replace(/"/g, '&quot;');
            return '' +
                '<button type="button" class="gallery-modal-thumb' + activeClass + '" data-thumb-index="' + index + '" aria-label="View image ' + (index + 1) + '">' +
                    '<img src="' + image.src + '" alt="' + safeAlt + '">' +
                '</button>';
        }).join('');
    }

    function updateModal() {
        const collection = collections[activeCollection];
        if (!collection || !collection.images.length) {
            return;
        }

        const image = collection.images[activeIndex];
        modalImage.src = image.src;
        modalImage.alt = image.alt || collection.title;
        modalTitle.textContent = collection.title || 'Room Gallery';
        modalEyebrow.textContent = collection.eyebrow || 'Room collection';
        modalDescription.textContent = collection.description || '';
        modalCounter.textContent = (activeIndex + 1) + ' / ' + collection.images.length;
        renderThumbRail(activeCollection);
        refreshIcons();
    }

    function openGallery(collectionKey, startIndex, trigger) {
        const collection = collections[collectionKey];
        if (!collection || !collection.images.length) {
            return;
        }

        activeCollection = collectionKey;
        activeIndex = Math.max(0, Math.min(startIndex || 0, collection.images.length - 1));
        lastTrigger = trigger || document.activeElement;
        updateModal();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('gallery-modal-open');
    }

    function closeGallery() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('gallery-modal-open');

        if (lastTrigger && typeof lastTrigger.focus === 'function') {
            lastTrigger.focus();
        }
    }

    function stepGallery(direction) {
        const collection = collections[activeCollection];
        if (!collection || !collection.images.length) {
            return;
        }

        activeIndex = (activeIndex + direction + collection.images.length) % collection.images.length;
        updateModal();
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            setFilter(chip.dataset.filter || 'all');
        });
    });

    openers.forEach(function (opener) {
        opener.addEventListener('click', function () {
            openGallery(opener.dataset.openGallery, Number(opener.dataset.startIndex || 0), opener);
        });
    });

    thumbRail.addEventListener('click', function (event) {
        const thumb = event.target.closest('[data-thumb-index]');
        if (!thumb) {
            return;
        }

        activeIndex = Number(thumb.dataset.thumbIndex || 0);
        updateModal();
    });

    prevButton.addEventListener('click', function () {
        stepGallery(-1);
    });

    nextButton.addEventListener('click', function () {
        stepGallery(1);
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeGallery);
    });

    document.addEventListener('keydown', function (event) {
        if (!modal.classList.contains('is-open')) {
            return;
        }

        if (event.key === 'Escape') {
            closeGallery();
        } else if (event.key === 'ArrowLeft') {
            stepGallery(-1);
        } else if (event.key === 'ArrowRight') {
            stepGallery(1);
        }
    });

    setFilter('all');
    refreshIcons();
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
