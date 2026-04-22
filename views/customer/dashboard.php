<?php
// Preserve any page title provided by the router; otherwise default to Home.
if (!isset($pageTitle) || empty($pageTitle)) {
    $pageTitle = 'Home';
}

$extraCss = ['customer.css'];
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$homeHero = [
    'image' => 'assets/uploads/images/home/houseoutside.png',
    'eyebrow' => 'DESIGN-LED SPACES',
    'title' => 'Turn everyday rooms into polished spaces that feel custom-built.',
    'copy' => 'From statement living areas to refined bath zones and sleek kitchen corners, Southdev Home Depot brings together the finishes, textures, and inspiration you need to design with confidence.',
];

$heroCards = [
    [
        'title' => 'Living Lounge',
        'label' => 'Soft textures & layered seating',
        'image' => 'assets/uploads/images/home/sofa.png',
    ],
    [
        'title' => 'Kitchen Edge',
        'label' => 'Clean lines for daily function',
        'image' => 'assets/uploads/images/home/kitchensinks.png',
    ],
    [
        'title' => 'Outdoor Calm',
        'label' => 'Resort-inspired finishes',
        'image' => 'assets/uploads/images/home/swimmingpool.png',
    ],
    [
        'title' => 'Bedroom',
        'label' => 'Restful spaces done right',
        'image' => 'assets/uploads/images/home/bedroom.png',
    ],
];

$signatureSpaces = [
    [
        'tag' => 'Living Room',
        'title' => 'Warm seating compositions with a showroom finish',
        'copy' => 'Build a welcoming focal point with balanced tones, tactile surfaces, and elevated styling details.',
        'image' => 'assets/uploads/images/home/sofa.png',
    ],
    [
        'tag' => 'Kitchen',
        'title' => 'Functional kitchen corners that still feel premium',
        'copy' => 'Pair practical fixtures with refined materials for a clean, modern setup that works beautifully every day.',
        'image' => 'assets/uploads/images/home/kitchensinks.png',
    ],
    [
        'tag' => 'Bath',
        'title' => 'Spa-like bathrooms with crisp and professional detailing',
        'copy' => 'Use coordinated finishes and timeless surfaces to create a bathroom that feels calm, light, and intentional.',
        'image' => 'assets/uploads/images/home/bathroom.png',
    ],
    [
        'tag' => 'Dining',
        'title' => 'Dining areas styled for modern family living',
        'copy' => 'Combine texture, warmth, and proportion to craft a dining space that feels both polished and lived-in.',
        'image' => 'assets/uploads/images/home/dinningarea.png',
    ],
];

$mosaicPanels = [
    [
        'title' => 'Entertaining Corners',
        'copy' => 'Sleek bar styling that adds personality without losing sophistication.',
        'image' => 'assets/uploads/images/home/homebar.png',
    ],
    [
        'title' => 'Private Retreats',
        'copy' => 'Comfort rooms made lighter, cleaner, and more refined.',
        'image' => 'assets/uploads/images/home/comfortroom.png',
    ],
    [
        'title' => 'Outdoor Statements',
        'copy' => 'Poolside surfaces and accents that bring resort energy home.',
        'image' => 'assets/uploads/images/home/swimmingpool.png',
    ],
];

$designPoints = [
    'Professionally styled room inspiration',
    'Finish combinations that feel premium and cohesive',
    'A cleaner, more editorial showcase for your home collection',
];

$storeFeatureImage = 'assets/uploads/images/home/storeinside.png';
?>

<style>
.site-header .main-nav {
    margin-bottom: 0;
}

.home-shell {
    padding: 0 0 4rem;
}

.home-hero {
    position: relative;
    overflow: hidden;
    background:
        linear-gradient(135deg, rgba(14, 24, 38, .88) 0%, rgba(25, 43, 67, .68) 48%, rgba(38, 59, 92, .58) 100%),
        url("<?= APP_URL ?>/<?= htmlspecialchars($homeHero['image']) ?>") center/cover no-repeat;
    padding: 4.5rem 2rem 4rem;
    color: #fff;
    box-shadow: 0 30px 80px rgba(15, 23, 42, .18);
}

.home-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at top left, rgba(255,255,255,.18), transparent 28%),
        linear-gradient(180deg, rgba(255,255,255,.05), transparent 35%);
    pointer-events: none;
}

.home-hero-inner {
    position: relative;
    z-index: 1;
    max-width: 1220px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
    gap: 2rem;
    align-items: center;
}

.home-hero-copy {
    max-width: 640px;
}

.home-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    padding: .7rem 1rem;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(14px);
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
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    line-height: .98;
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
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.14);
    backdrop-filter: blur(16px);
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

.home-hero-side {
    display: grid;
    gap: 1rem;
}

.home-primary-card,
.home-mini-card {
    position: relative;
    overflow: hidden;
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,.14);
    box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
}

.home-primary-card {
    min-height: 360px;
}

.home-primary-card img,
.home-mini-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.home-primary-card::after,
.home-mini-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 30%, rgba(12,18,28,.78) 100%);
}

.home-primary-card-copy,
.home-mini-card-copy {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
    padding: 1.35rem;
    color: #fff;
    text-shadow: 0 2px 14px rgba(0, 0, 0, .35);
}

.home-primary-card-copy span,
.home-mini-card-copy span {
    display: inline-flex;
    padding: .35rem .7rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.14);
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: .7rem;
    color: #fff;
}

.home-primary-card-copy h3,
.home-mini-card-copy h3 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 800;
    color: #fff;
}

.home-primary-card-copy p,
.home-mini-card-copy p {
    margin: .45rem 0 0;
    color: rgba(255,255,255,.78);
    line-height: 1.5;
}

.home-mini-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.home-mini-card {
    min-height: 220px;
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
    gap: 1.15rem;
}

.home-signature-card {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid rgba(15, 35, 61, .08);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 16px 34px rgba(15, 23, 42, .07);
    transition: transform .25s ease, box-shadow .25s ease;
}

.home-signature-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 44px rgba(15, 23, 42, .12);
}

.home-signature-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
}

.home-signature-copy {
    padding: 1.25rem 1.2rem 1.35rem;
}

.home-signature-copy span {
    display: inline-flex;
    padding: .35rem .7rem;
    border-radius: 999px;
    background: #eef5ff;
    color: #2d5d92;
    font-size: .74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: .8rem;
}

.home-signature-copy h3 {
    margin: 0;
    color: #12243f;
    font-size: 1.18rem;
    line-height: 1.3;
}

.home-signature-copy p {
    margin: .75rem 0 0;
    color: #66758b;
    line-height: 1.7;
}

.home-mosaic-wrap {
    margin-top: 1.1rem;
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
    gap: 1.15rem;
}

.home-mosaic-feature,
.home-mosaic-stack article {
    position: relative;
    overflow: hidden;
    border-radius: 28px;
    min-height: 280px;
    box-shadow: 0 24px 52px rgba(15, 23, 42, .12);
}

.home-mosaic-feature img,
.home-mosaic-stack article img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.home-mosaic-feature::after,
.home-mosaic-stack article::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(10,17,27,.05) 20%, rgba(10,17,27,.76) 100%);
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
    min-height: 320px;
}

.home-polish-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.home-polish-media::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(8, 16, 28, .16) 0%, rgba(8, 16, 28, .02) 100%);
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
    background: #eef5ff;
    color: #315d92;
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

@media (max-width: 1100px) {
    .home-hero-inner,
    .home-mosaic-wrap,
    .home-polish {
        grid-template-columns: 1fr;
    }

    .home-signature-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .home-hero {
        padding: 3.2rem 1rem 3rem;
    }

    .home-content {
        padding: 1.8rem 1rem 0;
    }

    .home-hero-meta,
    .home-signature-grid,
    .home-mini-grid {
        grid-template-columns: 1fr;
    }

    .home-section-head {
        flex-direction: column;
        align-items: start;
    }

    .home-primary-card {
        min-height: 300px;
    }

    .home-mini-card {
        min-height: 200px;
    }

    .home-polish {
        grid-template-columns: 1fr;
    }

    .home-polish-media {
        min-height: 240px;
    }

    .home-polish-content {
        padding: 1.5rem;
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
    .home-polish .btn {
        width: 100%;
        justify-content: center;
    }

    .home-stat,
    .home-polish-content {
        padding: 1rem;
    }

    .home-primary-card {
        min-height: 260px;
    }
}

@media (max-width: 420px) {
    .home-shell {
        padding-bottom: 2.8rem;
    }

    .home-hero {
        padding: 2.6rem .85rem 2.5rem;
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

    .home-primary-card,
    .home-mini-card,
    .home-signature-card,
    .home-polish,
    .home-mosaic-feature,
    .home-mosaic-stack article {
        border-radius: 18px;
    }

    .home-primary-card-copy,
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
        <div class="home-hero-inner">
            <div class="home-hero-copy">
                <div class="home-eyebrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4Z"></path>
                        <path d="m9.5 12 1.8 1.8L15 10"></path>
                    </svg>
                    <?= htmlspecialchars($homeHero['eyebrow']) ?>
                </div>

                <h1><?= htmlspecialchars($homeHero['title']) ?></h1>
                <p><?= htmlspecialchars($homeHero['copy']) ?></p>

                <div class="home-hero-actions">
                    <a href="<?= APP_URL ?>/index.php?url=products" class="btn btn-accent">
                        Explore Products
                    </a>
                    <a href="<?= APP_URL ?>/index.php?url=room-gallery" class="btn btn-outline-light">
                        View Room Gallery
                    </a>
                </div>

                <div class="home-hero-meta">
                    <div class="home-stat">
                        <strong>08</strong>
                        <span>Styled room inspirations</span>
                    </div>
                    <div class="home-stat">
                        <strong>Premium</strong>
                        <span>Finish-led visual direction</span>
                    </div>
                    <div class="home-stat">
                        <strong>Ready</strong>
                        <span>Ideas for real store projects</span>
                    </div>
                </div>
            </div>

            <div class="home-hero-side">
                <article class="home-primary-card">
                    <img src="<?= APP_URL ?>/<?= htmlspecialchars($homeHero['image']) ?>" alt="Southdev Home Depot exterior showcase">
                    <div class="home-primary-card-copy">
                        <span>Flagship Style</span>
                        <h3>Professional design inspiration starts the moment visitors arrive.</h3>
                        <p>Use a cleaner, architectural visual direction that feels premium, confident, and store-ready.</p>
                    </div>
                </article>

                <div class="home-mini-grid">
                    <?php foreach ($heroCards as $card): ?>
                        <article class="home-mini-card">
                            <img src="<?= APP_URL ?>/<?= htmlspecialchars($card['image']) ?>" alt="<?= htmlspecialchars($card['title']) ?> inspiration">
                            <div class="home-mini-card-copy">
                                <span><?= htmlspecialchars($card['title']) ?></span>
                                <h3><?= htmlspecialchars($card['label']) ?></h3>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="home-content">
        <section>
            <div class="home-section-head">
                <div>
                    <h2>Signature spaces with a more elevated showroom feel.</h2>
                </div>
                <p>Each zone is styled to feel polished, modern, and more aspirational—so the homepage reads like a professional interior showcase instead of a simple image block.</p>
            </div>

            <div class="home-signature-grid">
                <?php foreach ($signatureSpaces as $space): ?>
                    <article class="home-signature-card">
                        <img src="<?= APP_URL ?>/<?= htmlspecialchars($space['image']) ?>" alt="<?= htmlspecialchars($space['tag']) ?> showcase">
                        <div class="home-signature-copy">
                            <span><?= htmlspecialchars($space['tag']) ?></span>
                            <h3><?= htmlspecialchars($space['title']) ?></h3>
                            <p><?= htmlspecialchars($space['copy']) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section style="margin-top: 2rem;">
            <div class="home-section-head">
                <div>
                    <h2>Curated corners that make the page feel more editorial.</h2>
                </div>
                <p>Large-format imagery, darker overlays, and balanced content blocks give the home section a stronger premium identity while still keeping it approachable.</p>
            </div>

            <div class="home-mosaic-wrap">
                <article class="home-mosaic-feature">
                    <img src="<?= APP_URL ?>/<?= htmlspecialchars($mosaicPanels[0]['image']) ?>" alt="<?= htmlspecialchars($mosaicPanels[0]['title']) ?>">
                    <div class="home-mosaic-copy">
                        <h3><?= htmlspecialchars($mosaicPanels[0]['title']) ?></h3>
                        <p><?= htmlspecialchars($mosaicPanels[0]['copy']) ?></p>
                    </div>
                </article>

                <div class="home-mosaic-stack">
                    <?php foreach (array_slice($mosaicPanels, 1) as $panel): ?>
                        <article>
                            <img src="<?= APP_URL ?>/<?= htmlspecialchars($panel['image']) ?>" alt="<?= htmlspecialchars($panel['title']) ?>">
                            <div class="home-mosaic-copy">
                                <h3><?= htmlspecialchars($panel['title']) ?></h3>
                                <p><?= htmlspecialchars($panel['copy']) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="home-polish">
            <div class="home-polish-media">
                <img src="<?= APP_URL ?>/<?= htmlspecialchars($storeFeatureImage) ?>" alt="Southdev Home Depot showroom interior">
            </div>

            <div class="home-polish-content">
                <div class="home-polish-kicker">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 10.5 12 4l9 6.5"></path>
                        <path d="M5 9.5V20h14V9.5"></path>
                        <path d="M9 20v-6h6v6"></path>
                    </svg>
                    Store Experience
                </div>
                <h3>A more professional home section, built to make a stronger first impression.</h3>
                <p class="home-polish-copy">Swap the heavy dark block for a cleaner showroom feature that feels brighter, more realistic, and more aligned with the actual Southdev in-store experience.</p>
                <ul>
                    <?php foreach ($designPoints as $point): ?>
                        <li><?= htmlspecialchars($point) ?></li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= APP_URL ?>/index.php?url=locations" class="btn btn-accent">Visit Our Store</a>
            </div>
        </section>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
