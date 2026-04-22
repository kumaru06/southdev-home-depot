<?php
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<style>
.collections-page {
    background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    padding: 3rem 1.5rem 4rem;
}
.collections-shell {
    max-width: 1120px;
    margin: 0 auto;
}
.collections-header {
    text-align: center;
    max-width: 780px;
    margin: 0 auto 2rem;
}
.collections-header h1 {
    margin: 0 0 .9rem;
    font-size: clamp(2rem, 4vw, 3rem);
    color: #0f172a;
}
.collections-header p {
    margin: 0;
    color: #64748b;
    line-height: 1.8;
}
.collections-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}
.collection-card {
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(15,23,42,.06);
}
.collection-image {
    height: 220px;
    background: linear-gradient(135deg, rgba(249,115,22,.9), rgba(27,42,74,.88));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.15rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.collection-body {
    padding: 1.4rem;
    text-align: center;
}
.collection-body h2 {
    margin: 0 0 .65rem;
    font-size: 1.15rem;
    color: #0f172a;
}
.collection-body p {
    margin: 0;
    color: #64748b;
    line-height: 1.75;
}
@media (max-width: 900px) {
    .collections-grid { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .collections-page {
        padding: 2.2rem 1rem 3rem;
    }
    .collections-header {
        margin-bottom: 1.35rem;
    }
    .collections-header p,
    .collection-body p {
        font-size: .95rem;
        line-height: 1.68;
    }
    .collection-card {
        border-radius: 20px;
    }
    .collection-image {
        height: 180px;
        font-size: 1rem;
    }
    .collection-body {
        padding: 1.1rem;
    }
}

@media (max-width: 420px) {
    .collections-page {
        padding: 2rem .85rem 2.6rem;
    }
    .collections-header h1 {
        font-size: 1.8rem;
    }
    .collection-card {
        border-radius: 18px;
    }
    .collection-image {
        height: 160px;
    }
}
</style>

<div class="collections-page">
    <div class="collections-shell">
        <header class="collections-header">
            <h1>Featured Collections</h1>
            <p>Browse curated selections designed to highlight style, practicality, and product combinations that work beautifully across different types of spaces.</p>
        </header>

        <section class="collections-grid">
            <article class="collection-card">
                <div class="collection-image">Modern Spaces</div>
                <div class="collection-body">
                    <h2>Modern Finishes</h2>
                    <p>Clean lines, neutral palettes, and versatile materials for contemporary homes and business interiors.</p>
                </div>
            </article>
            <article class="collection-card">
                <div class="collection-image">Warm Texture</div>
                <div class="collection-body">
                    <h2>Natural Tones</h2>
                    <p>Collections that bring warmth, comfort, and texture into kitchens, bathrooms, and living areas.</p>
                </div>
            </article>
            <article class="collection-card">
                <div class="collection-image">Built to Last</div>
                <div class="collection-body">
                    <h2>Practical Essentials</h2>
                    <p>Reliable product combinations made for everyday performance in residential and commercial settings.</p>
                </div>
            </article>
        </section>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
