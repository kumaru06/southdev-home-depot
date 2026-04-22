<?php
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<style>
.gallery-page {
    background: #f8fafc;
    padding: 3rem 1.5rem 4rem;
}
.gallery-shell {
    max-width: 1120px;
    margin: 0 auto;
}
.gallery-header {
    text-align: center;
    max-width: 760px;
    margin: 0 auto 2rem;
}
.gallery-header h1 {
    margin: 0 0 .9rem;
    font-size: clamp(2rem, 4vw, 3rem);
    color: #0f172a;
}
.gallery-header p {
    margin: 0;
    color: #64748b;
    line-height: 1.8;
}
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 1rem;
}
.gallery-tile {
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 24px;
    padding: 1.5rem;
    box-shadow: 0 18px 40px rgba(15,23,42,.06);
    text-align: center;
    min-height: 220px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.gallery-tile.large { grid-column: span 6; }
.gallery-tile.small { grid-column: span 3; }
.gallery-tile h2 {
    margin: 0 0 .65rem;
    font-size: 1.15rem;
    color: #0f172a;
}
.gallery-tile p {
    margin: 0;
    color: #64748b;
    line-height: 1.75;
}
@media (max-width: 900px) {
    .gallery-tile.large,
    .gallery-tile.small {
        grid-column: span 12;
    }
}

@media (max-width: 640px) {
    .gallery-page {
        padding: 2.2rem 1rem 3rem;
    }
    .gallery-header {
        margin-bottom: 1.35rem;
    }
    .gallery-header p,
    .gallery-tile p {
        font-size: .95rem;
        line-height: 1.68;
    }
    .gallery-grid {
        gap: .85rem;
    }
    .gallery-tile {
        min-height: 180px;
        padding: 1.1rem;
        border-radius: 20px;
    }
}

@media (max-width: 420px) {
    .gallery-page {
        padding: 2rem .85rem 2.6rem;
    }
    .gallery-header h1 {
        font-size: 1.8rem;
    }
    .gallery-tile {
        min-height: 160px;
        padding: 1rem;
        border-radius: 18px;
    }
}
</style>

<div class="gallery-page">
    <div class="gallery-shell">
        <header class="gallery-header">
            <h1>Room Gallery</h1>
            <p>Discover room-by-room inspiration for combining materials, finishes, and product styles that create attractive and functional spaces.</p>
        </header>

        <section class="gallery-grid">
            <article class="gallery-tile large">
                <h2>Living Room Ideas</h2>
                <p>Browse ideas for polished surfaces, warm palettes, and finishes that make common spaces feel welcoming and durable.</p>
            </article>
            <article class="gallery-tile small">
                <h2>Kitchen Looks</h2>
                <p>Explore combinations suited for daily use, easy maintenance, and modern style.</p>
            </article>
            <article class="gallery-tile small">
                <h2>Bathroom Concepts</h2>
                <p>Find inspiration for surfaces and accents that balance comfort, function, and visual appeal.</p>
            </article>
            <article class="gallery-tile small">
                <h2>Outdoor Spaces</h2>
                <p>See options for durable finishes built for weather resistance and curb appeal.</p>
            </article>
            <article class="gallery-tile large">
                <h2>Commercial Project Inspiration</h2>
                <p>View practical style directions for offices, retail spaces, and project environments where design and durability matter equally.</p>
            </article>
        </section>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
