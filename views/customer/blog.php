<?php
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$heroSlides = [
    [
        'image' => APP_URL . '/assets/uploads/images/blog/transitiondisplay/mansion.png',
        'title' => 'Luxury Exterior Inspiration',
        'description' => 'See premium house concepts that highlight elegant finishes, layered textures, and refined architectural detail.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/blog/transitiondisplay/mansion2.png',
        'title' => 'Modern Mansion Facades',
        'description' => 'Explore bold front elevations, clean lines, and durable material combinations suited for statement builds.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/blog/transitiondisplay/mansion3.png',
        'title' => 'Polished Residential Design',
        'description' => 'Discover ideas for creating impressive residential spaces with balanced color, texture, and finishing touches.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/blog/transitiondisplay/mansion4.png',
        'title' => 'High-End Project Concepts',
        'description' => 'Get visual inspiration for exterior materials and details that give homes a timeless, upscale look.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/blog/transitiondisplay/mansion5.png',
        'title' => 'Grand Home Styling',
        'description' => 'Browse large-scale home concepts that combine practical materials with strong visual appeal.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/blog/transitiondisplay/mansion6.png',
        'title' => 'Architectural Statement Spaces',
        'description' => 'View exterior design inspiration that showcases quality construction and carefully selected finishing products.'
    ],
];

$blogPosts = [
    [
        'image' => APP_URL . '/assets/uploads/images/blog/house.png',
        'title' => 'Modern Exterior Ideas',
        'description' => 'Explore clean architectural lines, welcoming entryways, and finishing touches that give modern homes a polished and durable look.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/blog/house2.png',
        'title' => 'Warm and Practical Spaces',
        'description' => 'See how balanced textures, natural tones, and reliable materials can create home spaces that feel both stylish and functional.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/blog/house3.png',
        'title' => 'Project Inspiration for Renovations',
        'description' => 'Get ideas for upgrades that improve comfort, appearance, and everyday use across residential and commercial projects.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/blog/house4.png',
        'title' => 'Design Trends and Finishing Choices',
        'description' => 'Discover visual inspiration for selecting surfaces, accents, and product combinations that support a more refined result.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/kichenlooks/kictchenlooks.png',
        'title' => 'Kitchen Looks',
        'description' => 'View bright kitchen inspiration with clean layouts, practical surfaces, and finishes that support a polished everyday space.'
    ],
    [
        'image' => APP_URL . '/assets/uploads/images/kichenlooks/kitchenlooks2.png',
        'title' => 'Contemporary Kitchen Styling',
        'description' => 'Explore kitchen ideas that combine warmth, functionality, and modern detail for a more refined interior feel.'
    ],
];
?>

<style>
.site-header .main-nav {
    margin-bottom: 0;
}
.inspire-page {
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    padding: 0 0 4rem;
}
.inspire-shell {
    max-width: 1120px;
    margin: 0 auto;
    padding: 2rem 1.5rem 0;
}
.inspire-hero {
    position: relative;
    border-radius: 0;
    min-height: 460px;
    color: #fff;
    box-shadow: none;
    margin-bottom: 2rem;
    overflow: hidden;
}
.inspire-hero-track {
    display: flex;
    width: 100%;
    height: 100%;
    min-height: 460px;
    transition: transform .75s ease;
}
.inspire-slide {
    min-width: 100%;
    position: relative;
    display: flex;
    align-items: flex-end;
}
.inspire-slide::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15,23,42,.18) 0%, rgba(15,23,42,.48) 45%, rgba(15,23,42,.82) 100%);
    z-index: 1;
}
.inspire-slide img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.inspire-hero-content {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 3rem;
    text-align: center;
}
.inspire-kicker {
    display: inline-block;
    padding: .45rem .85rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 1rem;
}
.inspire-hero h1 {
    margin: 0 0 1rem;
    font-size: clamp(2rem, 4vw, 3.2rem);
    color: #fff;
    text-shadow: 0 10px 30px rgba(15,23,42,.3);
}
.inspire-hero p {
    margin: 0 auto;
    max-width: 760px;
    color: rgba(255,255,255,.82);
    line-height: 1.8;
    font-size: 1rem;
}
.inspire-slider-nav {
    position: absolute;
    left: 50%;
    bottom: 1.4rem;
    transform: translateX(-50%);
    z-index: 3;
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
    justify-content: center;
}
.inspire-slider-dot {
    width: 10px;
    height: 10px;
    border: 0;
    border-radius: 999px;
    background: rgba(255,255,255,.45);
    cursor: pointer;
    transition: transform .2s ease, background .2s ease, box-shadow .2s ease, width .2s ease;
    padding: 0;
}
.inspire-slider-dot:hover,
.inspire-slider-dot:focus-visible {
    background: rgba(255,255,255,.72);
    transform: translateY(-1px);
}
.inspire-slider-dot.is-active {
    width: 28px;
    background: #fff;
    box-shadow: 0 8px 18px rgba(255,255,255,.18);
}
.inspire-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}
.inspire-card {
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(15,23,42,.06);
}
.inspire-card-image {
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #e2e8f0;
    cursor: zoom-in;
    border: 0;
    padding: 0;
    width: 100%;
}
.inspire-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .25s ease;
}
.inspire-card:hover .inspire-card-image img {
    transform: scale(1.04);
}
.inspire-card-body {
    padding: 1.5rem;
    text-align: center;
}
.inspire-card h2 {
    margin: 0 0 .75rem;
    font-size: 1.15rem;
    color: #0f172a;
}
.inspire-card p {
    margin: 0;
    color: #64748b;
    line-height: 1.75;
}
.inspire-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .86);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    z-index: 1200;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity .22s ease, visibility .22s ease;
}
.inspire-lightbox.is-open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
.inspire-lightbox-dialog {
    position: relative;
    max-width: min(1100px, 92vw);
    max-height: 88vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.inspire-lightbox-image {
    max-width: 100%;
    max-height: 88vh;
    border-radius: 18px;
    box-shadow: 0 28px 60px rgba(0,0,0,.35);
    display: block;
}
.inspire-lightbox-close {
    position: absolute;
    top: -14px;
    right: -14px;
    width: 42px;
    height: 42px;
    border-radius: 999px;
    border: 0;
    background: #fff;
    color: #0f172a;
    font-size: 1.35rem;
    line-height: 1;
    cursor: pointer;
    box-shadow: 0 14px 30px rgba(0,0,0,.2);
}
@media (max-width: 900px) {
    .inspire-grid { grid-template-columns: 1fr; }
    .inspire-shell { padding: 2rem 1rem 0; }
    .inspire-hero,
    .inspire-hero-track {
        min-height: 400px;
    }
    .inspire-hero-content { padding: 2rem 1.25rem 4.5rem; }
}

@media (max-width: 640px) {
    .inspire-hero,
    .inspire-hero-track {
        min-height: 360px;
    }
    .inspire-slider-dot {
        width: 8px;
        height: 8px;
    }
    .inspire-slider-dot.is-active {
        width: 24px;
    }
}

@media (max-width: 420px) {
    .inspire-shell {
        padding: 1.4rem .85rem 0;
    }
    .inspire-hero,
    .inspire-hero-track {
        min-height: 320px;
    }
    .inspire-hero-content {
        padding: 1.35rem .9rem 3.8rem;
    }
    .inspire-kicker {
        font-size: .7rem;
        letter-spacing: .06em;
    }
    .inspire-hero h1 {
        font-size: clamp(1.8rem, 8vw, 2.2rem);
        margin-bottom: .8rem;
    }
    .inspire-hero p,
    .inspire-card p {
        font-size: .93rem;
        line-height: 1.68;
    }
    .inspire-card {
        border-radius: 18px;
    }
    .inspire-card-body {
        padding: 1.1rem .95rem;
    }
    .inspire-lightbox {
        padding: 1rem;
    }
    .inspire-lightbox-close {
        top: -10px;
        right: -4px;
    }
}
</style>

<div class="inspire-page">
    <section class="inspire-hero" data-inspire-slider>
        <div class="inspire-hero-track" data-slider-track>
            <?php foreach ($heroSlides as $index => $slide): ?>
                <article class="inspire-slide" aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                    <img src="<?= htmlspecialchars($slide['image']) ?>" alt="<?= htmlspecialchars($slide['title']) ?>">
                    <div class="inspire-hero-content">
                        <span class="inspire-kicker">Blog</span>
                        <h1><?= htmlspecialchars($slide['title']) ?></h1>
                        <p><?= htmlspecialchars($slide['description']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="inspire-slider-nav" aria-label="Blog image slider controls">
            <?php foreach ($heroSlides as $index => $slide): ?>
                <button
                    type="button"
                    class="inspire-slider-dot<?= $index === 0 ? ' is-active' : '' ?>"
                    data-slide-to="<?= $index ?>"
                    aria-label="Go to slide <?= $index + 1 ?>"
                    aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>"
                ></button>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="inspire-shell">
        <section class="inspire-grid">
            <?php foreach ($blogPosts as $post): ?>
                <article class="inspire-card">
                    <button
                        type="button"
                        class="inspire-card-image"
                        data-lightbox-image="<?= htmlspecialchars($post['image']) ?>"
                        data-lightbox-title="<?= htmlspecialchars($post['title']) ?>"
                        aria-label="View larger image for <?= htmlspecialchars($post['title']) ?>"
                    >
                        <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                    </button>
                    <div class="inspire-card-body">
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p><?= htmlspecialchars($post['description']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </div>
</div>

<div class="inspire-lightbox" data-lightbox aria-hidden="true">
    <div class="inspire-lightbox-dialog" role="dialog" aria-modal="true" aria-label="Image preview">
        <button type="button" class="inspire-lightbox-close" data-lightbox-close aria-label="Close image preview">&times;</button>
        <img src="" alt="" class="inspire-lightbox-image" data-lightbox-preview>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var slider = document.querySelector('[data-inspire-slider]');
    if (slider) {
        var track = slider.querySelector('[data-slider-track]');
        var slides = Array.prototype.slice.call(slider.querySelectorAll('.inspire-slide'));
        var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-slide-to]'));
        var currentIndex = 0;
        var autoplay = true;
        var intervalId = null;
        var delay = 2000;

        function renderSlide(index) {
            currentIndex = index;
            track.style.transform = 'translateX(-' + (index * 100) + '%)';

            slides.forEach(function (slide, slideIndex) {
                slide.setAttribute('aria-hidden', slideIndex === index ? 'false' : 'true');
            });

            dots.forEach(function (dot, dotIndex) {
                var active = dotIndex === index;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }

        function startAutoplay() {
            if (!autoplay || slides.length <= 1) return;
            stopAutoplay();
            intervalId = window.setInterval(function () {
                renderSlide((currentIndex + 1) % slides.length);
            }, delay);
        }

        function stopAutoplay() {
            if (intervalId) {
                window.clearInterval(intervalId);
                intervalId = null;
            }
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                autoplay = false;
                stopAutoplay();
                renderSlide(Number(dot.getAttribute('data-slide-to')) || 0);
            });
        });

        slider.addEventListener('mouseenter', stopAutoplay);
        slider.addEventListener('mouseleave', function () {
            if (autoplay) startAutoplay();
        });

        renderSlide(0);
        startAutoplay();
    }

    var lightbox = document.querySelector('[data-lightbox]');
    var lightboxImage = document.querySelector('[data-lightbox-preview]');
    var lightboxClose = document.querySelector('[data-lightbox-close]');
    var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox-image]'));

    if (lightbox && lightboxImage && lightboxClose && triggers.length) {
        function openLightbox(src, alt) {
            lightboxImage.src = src;
            lightboxImage.alt = alt || 'Preview image';
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            lightboxImage.src = '';
            lightboxImage.alt = '';
            document.body.style.overflow = '';
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                openLightbox(
                    trigger.getAttribute('data-lightbox-image') || '',
                    trigger.getAttribute('data-lightbox-title') || ''
                );
            });
        });

        lightboxClose.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
                closeLightbox();
            }
        });
    }
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
