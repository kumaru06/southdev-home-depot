<?php
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$faqItems = [
    [
        'question' => 'How can I ask about product availability before visiting the store?',
        'answer' => 'You can use the Product Inquiry page, call our store, or email us with the product name, quantity, and preferred finish so we can assist you faster.'
    ],
    [
        'question' => 'Do you accept bulk or project-based orders?',
        'answer' => 'Yes. For larger orders, include your estimated quantity and project type in your inquiry so our team can help you with suitable options and pricing guidance.'
    ],
    [
        'question' => 'Where is SouthDev Home Depot located?',
        'answer' => 'Our store is located at 3H3W+MJ8, Juna Ave, Talomo, Davao City, Davao del Sur. You can also open the Location page for the embedded map and directions.'
    ],
    [
        'question' => 'What are your store hours?',
        'answer' => 'We are open Monday to Saturday from 8:00 AM to 5:00 PM. The store is closed on Sundays.'
    ],
    [
        'question' => 'Can I browse products online before contacting you?',
        'answer' => 'Yes. You can explore the Products section first, then contact us if you need help comparing options, checking stock, or confirming details.'
    ],
];
?>

<style>
.site-header .main-nav {
    margin-bottom: 0;
}
.faq-page {
    background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    padding-bottom: 4rem;
}
.faq-hero {
    padding: 4rem 1.5rem 2rem;
    text-align: center;
}
.faq-hero-inner,
.faq-shell {
    max-width: 980px;
    margin: 0 auto;
}
.faq-hero-inner {
    width: 100%;
}
.faq-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .55rem .95rem;
    border-radius: 999px;
    background: rgba(249,115,22,.1);
    color: #f97316;
    font-size: .8rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.faq-hero h1 {
    margin: 1rem 0 .85rem;
    font-size: clamp(2.1rem, 4vw, 3.2rem);
    line-height: 1.08;
}
.faq-hero p {
    max-width: 760px;
    margin: 0 auto;
    line-height: 1.8;
    text-wrap: balance;
}
.faq-shell {
    padding: 0 1.5rem;
}
.faq-panel {
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 24px;
    padding: 1rem;
    box-shadow: 0 20px 48px rgba(15,23,42,.06);
}
.faq-item {
    border-bottom: 1px solid rgba(15,23,42,.08);
}
.faq-item:last-child {
    border-bottom: 0;
}
.faq-item details {
    padding: .35rem 0;
}
.faq-item summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.05rem 1rem;
    font-weight: 700;
    color: var(--charcoal);
    min-width: 0;
}
.faq-item summary::-webkit-details-marker {
    display: none;
}
.faq-item summary::after {
    content: '+';
    width: 28px;
    height: 28px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    color: #f97316;
    font-size: 1.15rem;
    font-weight: 800;
    flex-shrink: 0;
}
.faq-item details[open] summary::after {
    content: '–';
}
.faq-answer {
    padding: 0 1rem 1.1rem;
}
.faq-answer p {
    margin: 0;
    line-height: 1.8;
}
.faq-help {
    margin-top: 1.5rem;
    padding: 1.35rem 1.4rem;
    border-radius: 20px;
    background: linear-gradient(135deg, rgba(249,115,22,.1) 0%, rgba(255,255,255,1) 100%);
    border: 1px solid rgba(249,115,22,.16);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.faq-help h2 {
    margin: 0 0 .35rem;
    font-size: 1.15rem;
}
.faq-help p {
    margin: 0;
}
.faq-help a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .9rem 1.15rem;
    border-radius: 14px;
    background: var(--accent);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    white-space: nowrap;
}
.faq-help a:hover {
    background: var(--accent-hover);
}
@media (max-width: 960px) {
    .faq-hero {
        padding: 3.5rem 1.25rem 1.75rem;
    }
    .faq-shell {
        padding: 0 1.25rem;
    }
}
@media (max-width: 700px) {
    .faq-hero,
    .faq-shell {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .faq-page {
        padding-bottom: 3rem;
    }
    .faq-hero {
        padding-top: 3rem;
        padding-bottom: 1.25rem;
    }
    .faq-hero h1 {
        font-size: clamp(1.9rem, 8vw, 2.45rem);
        line-height: 1.12;
    }
    .faq-hero p {
        font-size: .96rem;
        line-height: 1.7;
    }
    .faq-panel {
        padding: .7rem;
        border-radius: 20px;
    }
    .faq-item summary {
        align-items: flex-start;
        padding: .95rem .9rem;
        font-size: .96rem;
        line-height: 1.5;
    }
    .faq-answer {
        padding: 0 .9rem 1rem;
    }
    .faq-answer p {
        font-size: .94rem;
        line-height: 1.7;
    }
    .faq-help {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.2rem;
        border-radius: 18px;
    }
    .faq-help a {
        width: 100%;
    }
}
@media (max-width: 420px) {
    .faq-hero,
    .faq-shell {
        padding-left: .85rem;
        padding-right: .85rem;
    }
    .faq-pill {
        font-size: .72rem;
        padding: .48rem .82rem;
    }
    .faq-hero {
        padding-top: 2.6rem;
    }
    .faq-hero h1 {
        font-size: 1.78rem;
    }
    .faq-panel {
        padding: .55rem;
        border-radius: 18px;
    }
    .faq-item summary {
        gap: .75rem;
        padding: .9rem .8rem;
        font-size: .92rem;
    }
    .faq-item summary::after {
        width: 26px;
        height: 26px;
        font-size: 1rem;
    }
    .faq-answer {
        padding: 0 .8rem .95rem;
    }
    .faq-help {
        margin-top: 1.1rem;
        padding: 1rem;
    }
    .faq-help h2 {
        font-size: 1.02rem;
    }
    .faq-help p {
        font-size: .92rem;
        line-height: 1.65;
    }
}
</style>

<div class="faq-page">
    <section class="faq-hero">
        <div class="faq-hero-inner">
            <span class="faq-pill">Contact Us</span>
            <h1>Frequently Asked Questions</h1>
            <p>Here are quick answers to common questions about product inquiries, store information, and how to get help from SouthDev Home Depot.</p>
        </div>
    </section>

    <section class="faq-shell">
        <div class="faq-panel">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="faq-item">
                    <details <?= $index === 0 ? 'open' : '' ?>>
                        <summary><?= htmlspecialchars($faq['question']) ?></summary>
                        <div class="faq-answer">
                            <p><?= htmlspecialchars($faq['answer']) ?></p>
                        </div>
                    </details>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="faq-help">
            <div>
                <h2>Need a more specific answer?</h2>
                <p>Send us a product-specific question and our team can help you with availability, pricing, and recommendations.</p>
            </div>
            <a href="<?= APP_URL ?>/index.php?url=product-inquiry">Go to Product Inquiry</a>
        </div>
    </section>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>