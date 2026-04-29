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

$heroImage = APP_URL . '/assets/uploads/images/roomgallery/commercial/commercial4.png';
$storyImage = APP_URL . '/assets/uploads/images/image.png';
$ctaImage = APP_URL . '/assets/uploads/images/roomgallery/livingroom/livingroom4.png';
?>

<style>
.site-header .main-nav {
    margin-bottom: 0;
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
    inset: auto -10% -80px;
    height: 160px;
    background: radial-gradient(circle at center, rgba(249,115,22,.30), transparent 60%);
    filter: blur(10px);
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

/* ── Small mobile (≤ 420px) ── */
@media (max-width: 420px) {
    .about-hero {
        padding: 3rem .85rem 3rem;
    }
    .about-kicker {
        font-size: .7rem;
        letter-spacing: .05em;
    }
    .about-hero-copy h1 {
        font-size: clamp(1.7rem, 9vw, 2.1rem);
    }
    .about-hero-actions {
        grid-template-columns: 1fr;
    }
    .about-stats {
        grid-template-columns: 1fr 1fr;
    }
    .about-highlight-card img {
        height: 160px;
    }
    .about-media-thumbs img {
        height: 90px;
        max-height: 90px;
        object-fit: cover;
    }
    .about-section-media .about-media-main img {
        min-height: 190px;
        object-fit: cover;
    }
}
</style>

<div class="about-page">
    <section class="about-hero">
        <div class="about-hero-inner">
            <div class="about-hero-grid">
                <div class="about-hero-copy reveal-on-scroll reveal-left">
                    <span class="about-kicker">About <?= htmlspecialchars(APP_NAME) ?></span>
                    <h1>An expanding hardware and construction supply company.</h1>
                    <p>Southdev Home Depot aims at delivering quality goods and services to its clients while meeting the growing needs for construction materials and home improvement supplies in the community.</p>
                    <div class="about-hero-actions">
                        <a class="about-btn about-btn-primary" href="<?= APP_URL ?>/index.php?url=products">Browse Products</a>
                        <a class="about-btn about-btn-secondary" href="<?= APP_URL ?>/index.php?url=locations">Visit Our Store</a>
                    </div>
                </div>

                <aside class="about-highlight-card reveal-on-scroll reveal-right">
                    <img src="<?= $storyImage ?>" alt="<?= htmlspecialchars(APP_NAME) ?> showroom display">
                    <h2><?= htmlspecialchars(APP_TAGLINE) ?></h2>
                    <p>From the beginning, the company has continued to grow in order to serve both individual customers and contractors with dependable products, low prices, and reliable customer service.</p>
                </aside>
            </div>
        </div>
    </section>

    <section class="about-stats about-hero-inner" aria-label="Business highlights">
        <div class="about-stat reveal-on-scroll reveal-left">
            <strong><?= $yearsServing ?>+</strong>
            <span>Years supporting residential and commercial improvement projects.</span>
        </div>
        <div class="about-stat reveal-on-scroll reveal-right">
            <strong><?= $productCount > 0 ? number_format($productCount) . '+' : 'Wide' ?></strong>
            <span>Selection of active products ready for modern, practical spaces.</span>
        </div>
        <div class="about-stat reveal-on-scroll reveal-left">
            <strong><?= $categoryCount > 0 ? number_format($categoryCount) : 'Multiple' ?></strong>
            <span>Product categories covering surfaces, fixtures, tools, and more.</span>
        </div>
        <div class="about-stat reveal-on-scroll reveal-right">
            <strong>100%</strong>
            <span>Focused on a customer-first buying experience from inquiry to checkout.</span>
        </div>
    </section>

    <section class="about-section">
        <div class="about-section-media">
            <div class="about-media-main reveal-on-scroll reveal-left">
                <img src="<?= $heroImage ?>" alt="Interior tile and showroom display at <?= htmlspecialchars(APP_NAME) ?>">
                <div class="about-badge">
                    <strong>Trusted local source</strong>
                    <span>Serving customers across Davao City and nearby areas.</span>
                </div>
            </div>
            <div class="about-media-thumbs">
                <img class="reveal-up stagger-1" src="<?= APP_URL ?>/assets/uploads/images/roomgallery/kitchen/kitchen.png" alt="Kitchen showroom display" loading="lazy">
                <img class="reveal-up stagger-2" src="<?= APP_URL ?>/assets/uploads/images/roomgallery/livingroom/livingroom.png" alt="Living room showcase" loading="lazy">
                <img class="reveal-up stagger-3" src="<?= APP_URL ?>/assets/uploads/images/roomgallery/bathroom/bathroom.png" alt="Bathroom design" loading="lazy">
                <img class="reveal-up stagger-4" src="<?= APP_URL ?>/assets/uploads/images/roomgallery/dining/dining.png" alt="Dining area showcase" loading="lazy">
            </div>
        </div>

        <div class="about-section-copy reveal-on-scroll reveal-right">
            <h2>Supplying essential products for residential and commercial projects.</h2>
            <p>Since its inception, Southdev Home Depot has concentrated on selling a variety of products such as hardware tools, building materials, electrical supplies, and other useful items required in residential and commercial projects. The company prides itself on providing low prices, reliable supply of products, and service to its customers.</p>
            <p>As part of its drive towards innovation and efficiency, Southdev Home Depot is undergoing a digital transformation through an Online Management System. The goal of this system is to streamline transactions, improve inventory management, and give customers a more accessible and convenient shopping experience.</p>
            <p>Being keen to quality and improvement, Southdev Home Depot continues striving to be a reliable supplier of construction and hardware products while keeping up with the latest technological changes.</p>

            <div class="about-checklist">
                <div class="about-checklist-item">
                    <i>✓</i>
                    <div>
                        <strong>Wide product selection</strong>
                        <span>Hardware tools, building materials, electrical supplies, and other essential products are offered to support diverse project needs.</span>
                    </div>
                </div>
                <div class="about-checklist-item">
                    <i>✓</i>
                    <div>
                        <strong>Reliable value and service</strong>
                        <span>The business focuses on low prices, dependable supply, and customer service that supports both homeowners and contractors.</span>
                    </div>
                </div>
                <div class="about-checklist-item">
                    <i>✓</i>
                    <div>
                        <strong>Digital transformation</strong>
                        <span>The Online Management System is designed to streamline transactions, strengthen inventory control, and improve convenience for customers.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-values">
        <div class="about-values-header reveal-on-scroll reveal-left">
            <h2>What defines Southdev Home Depot</h2>
            <p>The company continues building its reputation around quality, dependable service, and a willingness to improve through technology so customers can enjoy a more efficient shopping experience.</p>
        </div>

        <div class="about-values-grid">
            <article class="about-value-card reveal-on-scroll reveal-left">
                <div class="icon">01</div>
                <h3>Quality goods and services</h3>
                <p>Southdev Home Depot aims to deliver dependable products and support that meet the expectations of customers working on important projects.</p>
            </article>

            <article class="about-value-card reveal-on-scroll reveal-right">
                <div class="icon">02</div>
                <h3>Reliable supply for the community</h3>
                <p>The company continues to serve both individual buyers and contractors by maintaining practical product availability for residential and commercial work.</p>
            </article>

            <article class="about-value-card reveal-on-scroll reveal-left">
                <div class="icon">03</div>
                <h3>Innovation and efficiency</h3>
                <p>Through its Online Management System, the business is embracing digital solutions that improve transactions, inventory management, and customer convenience.</p>
            </article>
        </div>
    </section>

    <section class="about-cta">
        <div class="about-cta-inner reveal-on-scroll reveal-right">
            <div class="about-cta-copy">
                <h2>Ready to explore products for your next project?</h2>
                <p>Browse available items online or stop by the store to see selections up close and plan with confidence.</p>
            </div>
            <div class="about-hero-actions">
                <a class="about-btn about-btn-primary" href="<?= APP_URL ?>/index.php?url=products">Start Shopping</a>
                <a class="about-btn about-btn-secondary" href="<?= APP_URL ?>/index.php?url=locations">Get Directions</a>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    var imgs = document.querySelectorAll('.about-media-thumbs .reveal-up');
    if (!imgs.length) return;

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        imgs.forEach(function (img) { img.classList.add('is-visible'); });
        return;
    }

    if (!('IntersectionObserver' in window)) {
        imgs.forEach(function (img) { img.classList.add('is-visible'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            obs.unobserve(entry.target);
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -5% 0px' });

    imgs.forEach(function (img) { observer.observe(img); });
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>