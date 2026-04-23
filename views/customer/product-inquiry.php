<?php
$inquiryRecipientEmail = 'natzumekirito@gmail.com';

$inquiryData = [
    'name' => trim((string) (($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''))),
    'email' => trim((string) ($_SESSION['email'] ?? '')),
    'phone' => '',
    'subject' => '',
    'message' => ''
];
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiryData = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'subject' => trim((string) ($_POST['subject'] ?? '')),
        'message' => trim((string) ($_POST['message'] ?? ''))
    ];

    if (!verify_csrf()) {
        $formError = 'Invalid security token. Please refresh the page and try again.';
    } elseif (
        $inquiryData['name'] === '' ||
        $inquiryData['email'] === '' ||
        $inquiryData['phone'] === '' ||
        $inquiryData['subject'] === '' ||
        $inquiryData['message'] === ''
    ) {
        $formError = 'Please complete all required inquiry fields.';
    } elseif (!filter_var($inquiryData['email'], FILTER_VALIDATE_EMAIL)) {
        $formError = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9+()\-\s]{7,20}$/', $inquiryData['phone'])) {
        $formError = 'Please enter a valid phone number.';
    } else {
        $escapedName = htmlspecialchars($inquiryData['name'], ENT_QUOTES, 'UTF-8');
        $escapedEmail = htmlspecialchars($inquiryData['email'], ENT_QUOTES, 'UTF-8');
        $escapedPhone = htmlspecialchars($inquiryData['phone'], ENT_QUOTES, 'UTF-8');
        $escapedSubject = htmlspecialchars($inquiryData['subject'], ENT_QUOTES, 'UTF-8');
        $escapedMessage = nl2br(htmlspecialchars($inquiryData['message'], ENT_QUOTES, 'UTF-8'));

        $htmlBody = '
            <h2 style="margin:0 0 16px;color:#111827;">New Product Inquiry</h2>
            <table cellpadding="10" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;color:#374151;">
                <tr><td style="width:140px;border:1px solid #e5e7eb;"><strong>Name</strong></td><td style="border:1px solid #e5e7eb;">' . $escapedName . '</td></tr>
                <tr><td style="border:1px solid #e5e7eb;"><strong>Email</strong></td><td style="border:1px solid #e5e7eb;">' . $escapedEmail . '</td></tr>
                <tr><td style="border:1px solid #e5e7eb;"><strong>Phone</strong></td><td style="border:1px solid #e5e7eb;">' . $escapedPhone . '</td></tr>
                <tr><td style="border:1px solid #e5e7eb;"><strong>Subject</strong></td><td style="border:1px solid #e5e7eb;">' . $escapedSubject . '</td></tr>
                <tr><td style="vertical-align:top;border:1px solid #e5e7eb;"><strong>Message</strong></td><td style="border:1px solid #e5e7eb;">' . $escapedMessage . '</td></tr>
            </table>
        ';

        $textBody = "New Product Inquiry\n\n"
            . "Name: {$inquiryData['name']}\n"
            . "Email: {$inquiryData['email']}\n"
            . "Phone: {$inquiryData['phone']}\n"
            . "Subject: {$inquiryData['subject']}\n\n"
            . "Message:\n{$inquiryData['message']}\n";

        $sent = false;

        try {
            require_once INCLUDES_PATH . '/Mailer.php';
            $mailer = new Mailer();
            $sent = (bool) $mailer->send($inquiryRecipientEmail, APP_NAME, 'Product Inquiry: ' . $inquiryData['subject'], $htmlBody, $textBody);
        } catch (Throwable $e) {
            $sent = false;
        }

        if (!$sent) {
            try {
                $mailDir = ROOT_PATH . '/storage/mails';
                if (!is_dir($mailDir)) {
                    @mkdir($mailDir, 0755, true);
                }
                $fileBase = $mailDir . '/inquiry_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
                file_put_contents($fileBase . '.html', $htmlBody);
                file_put_contents($fileBase . '.txt', $textBody);
                $sent = true;
            } catch (Throwable $e) {
                $sent = false;
            }
        }

        if ($sent) {
            flash('success', 'Your product inquiry has been submitted. Our team will review it shortly.');
            header('Location: ' . APP_URL . '/index.php?url=product-inquiry#inquiry-form');
            exit;
        }

        $formError = 'Unable to submit your inquiry right now. Please try again in a moment.';
    }
}

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<style>
.site-header .main-nav {
    margin-bottom: 0;
}
.support-page {
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    padding-bottom: 4rem;
}
.support-hero {
    background:
        linear-gradient(rgba(15,23,42,.18), rgba(15,23,42,.18)),
        url("<?= APP_URL ?>/assets/uploads/images/image.png") center/cover no-repeat;
    color: #fff;
    padding: 4.5rem 1.5rem 5rem;
}
.support-hero-inner,
.support-content {
    max-width: 1100px;
    margin: 0 auto;
}
.support-hero-inner {
    width: 100%;
}
.support-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: .5rem .9rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.12);
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.support-hero h1 {
    color: #fff;
    font-size: clamp(2.2rem, 4vw, 3.4rem);
    margin: 1rem 0 .85rem;
    line-height: 1.05;
    letter-spacing: -.03em;
}
.support-hero p {
    max-width: 720px;
    margin: 0;
    color: rgba(255,255,255,.82);
    font-size: 1.02rem;
    line-height: 1.8;
    text-wrap: balance;
}
.support-content {
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    gap: 1.5rem;
    margin-top: -2.7rem;
    padding: 0 1.5rem;
    position: relative;
    z-index: 2;
}
.support-card {
    background: #fff;
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 24px;
    padding: 1.75rem;
    box-shadow: 0 22px 48px rgba(15,23,42,.08);
    min-width: 0;
}
.support-card h2 {
    margin: 0 0 .65rem;
    font-size: 1.3rem;
}
.support-card p {
    margin: 0 0 1.1rem;
    line-height: 1.75;
}
.inquiry-list {
    display: grid;
    gap: .9rem;
    margin: 1.25rem 0 0;
}
.inquiry-item {
    display: flex;
    gap: .85rem;
    align-items: flex-start;
    padding: 1rem 1.05rem;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid rgba(15,23,42,.06);
}
.inquiry-item-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: rgba(249,115,22,.12);
    color: #f97316;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.inquiry-item-icon svg {
    width: 18px;
    height: 18px;
}
.inquiry-item strong {
    display: block;
    color: var(--charcoal);
    margin-bottom: .2rem;
}
.inquiry-item span {
    color: var(--text-secondary);
    font-size: .96rem;
    line-height: 1.6;
}
.support-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .8rem;
    margin-top: 1.35rem;
}
.support-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .9rem 1.2rem;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 700;
    text-align: center;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}
.support-btn.primary {
    background: var(--accent);
    color: #fff;
    box-shadow: 0 14px 28px rgba(249,115,22,.22);
}
.support-btn.primary:hover {
    background: var(--accent-hover);
    transform: translateY(-1px);
}
.support-btn.secondary {
    background: #fff;
    color: var(--charcoal);
    border: 1px solid rgba(15,23,42,.08);
}
.contact-stack {
    display: grid;
    gap: 1rem;
}
.contact-box {
    padding: 1rem 1.05rem;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    border: 1px solid rgba(15,23,42,.08);
}
.contact-box h3 {
    margin: 0 0 .35rem;
    font-size: 1rem;
}
.contact-box p,
.contact-box a {
    margin: 0;
    color: var(--text-secondary);
    line-height: 1.7;
    text-decoration: none;
    overflow-wrap: anywhere;
}
.contact-box a:hover {
    color: var(--accent);
}
.inquiry-form-section {
    max-width: 1100px;
    margin: 2.25rem auto 0;
    padding: 0 1.5rem;
}
.inquiry-form-shell {
    background: #fff;
    border-radius: 24px;
    border: 1px solid rgba(15,23,42,.08);
    box-shadow: 0 22px 48px rgba(15,23,42,.08);
    padding: 2rem;
}
.inquiry-form-shell h2 {
    margin: 0 0 .5rem;
    font-size: 1.45rem;
}
.inquiry-form-shell > p {
    margin: 0 0 1.35rem;
    max-width: 760px;
}
.inquiry-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}
.inquiry-field {
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.inquiry-field.full {
    grid-column: 1 / -1;
}
.inquiry-field label {
    font-size: .9rem;
    font-weight: 700;
    color: var(--charcoal);
}
.inquiry-field input,
.inquiry-field textarea {
    width: 100%;
    padding: .95rem 1rem;
    border: 1px solid #d1d5db;
    background: #fff;
    color: var(--charcoal);
    font: inherit;
    border-radius: 0;
    transition: border-color .18s ease, box-shadow .18s ease;
    -webkit-appearance: none;
    appearance: none;
}
.inquiry-field input::placeholder,
.inquiry-field textarea::placeholder {
    color: #9ca3af;
}
.inquiry-field input:focus,
.inquiry-field textarea:focus {
    outline: none;
    border-color: rgba(249,115,22,.55);
    box-shadow: 0 0 0 4px rgba(249,115,22,.12);
}
.inquiry-field textarea {
    min-height: 215px;
    resize: vertical;
}
.inquiry-form-footer {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
}
.inquiry-submit {
    min-width: 140px;
    border: 0;
    border-radius: 0;
    background: #c93414;
    color: #fff;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: .95rem 1.4rem;
    cursor: pointer;
    transition: background .18s ease, transform .18s ease;
}
.inquiry-submit:hover {
    background: #b22d10;
    transform: translateY(-1px);
}
.inquiry-submit:active {
    transform: translateY(0);
}
.inquiry-inline-alert {
    margin: 0 0 1rem;
    padding: .95rem 1rem;
    border-radius: 14px;
    font-size: .95rem;
    line-height: 1.6;
}
.inquiry-inline-alert.error {
    background: rgba(220,38,38,.08);
    color: #b91c1c;
    border: 1px solid rgba(220,38,38,.18);
}
@media (max-width: 1100px) {
    .support-hero {
        padding: 4rem 1.25rem 4.75rem;
    }
    .support-content {
        padding: 0 1.25rem;
        margin-top: -2.2rem;
    }
    .inquiry-form-section {
        padding: 0 1.25rem;
    }
}
@media (max-width: 900px) {
    .support-page {
        padding-bottom: 3rem;
    }
    .support-content {
        grid-template-columns: 1fr;
        padding: 0 1rem;
        margin-top: -1.6rem;
        gap: 1rem;
    }
    .inquiry-form-section {
        padding: 0 1rem;
        margin-top: 1.25rem;
    }
    .inquiry-form-grid {
        grid-template-columns: 1fr;
    }
    .support-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
    .support-btn {
        width: 100%;
        min-height: 48px;
    }
}
@media (max-width: 640px) {
    .support-hero {
        padding: 3rem 1rem 3.75rem;
    }
    .support-eyebrow {
        font-size: .72rem;
        letter-spacing: .06em;
        padding: .48rem .8rem;
    }
    .support-hero h1 {
        font-size: clamp(2rem, 9vw, 2.6rem);
        margin: .85rem 0 .75rem;
    }
    .support-hero p {
        font-size: .96rem;
        line-height: 1.7;
    }
    .support-content {
        margin-top: -1rem;
        gap: .9rem;
    }
    .support-card {
        padding: 1.3rem;
        border-radius: 20px;
    }
    .support-card h2 {
        font-size: 1.18rem;
    }
    .support-card p {
        font-size: .95rem;
        line-height: 1.68;
    }
    .inquiry-item {
        padding: .95rem;
        gap: .75rem;
    }
    .inquiry-item-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
    }
    .inquiry-item span {
        font-size: .93rem;
    }
    .support-actions {
        grid-template-columns: 1fr;
    }
    .contact-stack {
        gap: .85rem;
    }
    .contact-box {
        padding: .95rem;
        border-radius: 16px;
    }
    .inquiry-form-shell {
        padding: 1.3rem;
        border-radius: 20px;
    }
    .inquiry-form-shell h2 {
        font-size: 1.2rem;
    }
    .inquiry-form-shell > p {
        font-size: .95rem;
        line-height: 1.65;
        margin-bottom: 1.1rem;
    }
    .inquiry-form-grid {
        gap: .9rem;
    }
    .inquiry-field label {
        font-size: .86rem;
    }
    .inquiry-field input,
    .inquiry-field textarea {
        padding: .9rem .92rem;
        font-size: 16px;
    }
    .inquiry-field textarea {
        min-height: 180px;
    }
    .inquiry-form-footer {
        margin-top: .9rem;
    }
    .inquiry-submit {
        width: 100%;
        min-width: 0;
    }
}
@media (max-width: 420px) {
    .support-page {
        padding-bottom: 2.5rem;
    }
    .support-hero {
        padding: 2.6rem .85rem 3.4rem;
        background-position: center center;
    }
    .support-content,
    .inquiry-form-section {
        padding-left: .85rem;
        padding-right: .85rem;
    }
    .support-card,
    .inquiry-form-shell {
        padding: 1.05rem;
        border-radius: 18px;
    }
    .support-hero h1 {
        font-size: 1.95rem;
    }
    .support-hero p,
    .support-card p,
    .inquiry-form-shell > p {
        font-size: .92rem;
    }
    .inquiry-item {
        flex-direction: column;
    }
    .inquiry-item-icon {
        width: 36px;
        height: 36px;
    }
}
</style>

<div class="support-page">
    <section class="support-hero">
        <div class="support-hero-inner">
            <span class="support-eyebrow">Contact Us</span>
            <h1>Product Inquiry</h1>
            <p>Need help choosing tiles, sanitary ware, hardware, or finishing materials? Reach out to SouthDev Home Depot and our team can assist with product availability, pricing, recommended options, and store pickup guidance.</p>
        </div>
    </section>

    <section class="support-content">
        <article class="support-card">
            <h2>How we can help</h2>
            <p>Send us your product questions and include the item name, preferred quantity, and project type so our team can give you a faster response.</p>

            <div class="inquiry-list">
                <div class="inquiry-item">
                    <span class="inquiry-item-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                    </span>
                    <div>
                        <strong>Availability checks</strong>
                        <span>Ask if a specific tile, adhesive, vinyl, or hardware item is currently in stock.</span>
                    </div>
                </div>
                <div class="inquiry-item">
                    <span class="inquiry-item-icon" aria-hidden="true">
                        <span style="font-size: 1.1rem; font-weight: 700; line-height: 1;">₱</span>
                    </span>
                    <div>
                        <strong>Pricing and bulk orders</strong>
                        <span>Get guidance for larger quantities, project orders, and matching items for residential or commercial builds.</span>
                    </div>
                </div>
                <div class="inquiry-item">
                    <span class="inquiry-item-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v13H3z"></path><path d="M8 21h8"></path><path d="M12 16v5"></path></svg>
                    </span>
                    <div>
                        <strong>Product recommendations</strong>
                        <span>Share your room type or project goal and we can point you to suitable materials and finish options.</span>
                    </div>
                </div>
            </div>

            <div class="support-actions">
                <a class="support-btn primary" href="mailto:<?= htmlspecialchars($inquiryRecipientEmail, ENT_QUOTES, 'UTF-8') ?>?subject=Product%20Inquiry">Email Product Inquiry</a>
                <a class="support-btn secondary" href="<?= APP_URL ?>/index.php?url=locations">Visit Our Store</a>
            </div>
        </article>

        <aside class="support-card">
            <h2>Contact details</h2>
            <div class="contact-stack">
                <div class="contact-box">
                    <h3>Email</h3>
                    <a href="mailto:<?= htmlspecialchars($inquiryRecipientEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($inquiryRecipientEmail, ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="contact-box">
                    <h3>Phone</h3>
                    <a href="tel:+639399398250">+63 (939) 939 8250</a>
                </div>
                <div class="contact-box">
                    <h3>Store hours</h3>
                    <p>Monday to Saturday<br>8:00 AM to 5:00 PM</p>
                </div>
                <div class="contact-box">
                    <h3>Store location</h3>
                    <p>3H3W+MJ8, Juna Ave, Talomo, Davao City, Davao del Sur</p>
                </div>
            </div>
        </aside>
    </section>

    <section class="inquiry-form-section" id="inquiry-form">
        <div class="inquiry-form-shell">
            <h2>Send us your inquiry</h2>
            <p>Fill out the form below and include any product details, preferred quantity, or project notes.</p>

            <?php if ($formError !== ''): ?>
                <div class="inquiry-inline-alert error"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/index.php?url=product-inquiry" method="POST" novalidate>
                <?= csrf_field() ?>
                <div class="inquiry-form-grid">
                    <div class="inquiry-field">
                        <label for="inquiry-name">Name</label>
                        <input
                            type="text"
                            id="inquiry-name"
                            name="name"
                            placeholder="Name (Required)"
                            value="<?= htmlspecialchars($inquiryData['name'], ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="inquiry-field">
                        <label for="inquiry-email">Email</label>
                        <input
                            type="email"
                            id="inquiry-email"
                            name="email"
                            placeholder="Email (Required)"
                            value="<?= htmlspecialchars($inquiryData['email'], ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="inquiry-field">
                        <label for="inquiry-phone">Phone</label>
                        <input
                            type="text"
                            id="inquiry-phone"
                            name="phone"
                            placeholder="Phone (Required)"
                            value="<?= htmlspecialchars($inquiryData['phone'], ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="inquiry-field">
                        <label for="inquiry-subject">Subject</label>
                        <input
                            type="text"
                            id="inquiry-subject"
                            name="subject"
                            placeholder="Subject (Required)"
                            value="<?= htmlspecialchars($inquiryData['subject'], ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="inquiry-field full">
                        <label for="inquiry-message">Your Message</label>
                        <textarea
                            id="inquiry-message"
                            name="message"
                            placeholder="Your Message (Required)"
                            required><?= htmlspecialchars($inquiryData['message'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <div class="inquiry-form-footer">
                    <button type="submit" class="inquiry-submit">Submit</button>
                </div>
            </form>
        </div>
    </section>
</div>

<?php if ($formError !== ''): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var formSection = document.getElementById('inquiry-form');
    if (formSection) {
        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>