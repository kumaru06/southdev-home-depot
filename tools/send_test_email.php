<?php
// CLI script to test PHPMailer setup
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Mailer.php';

$to = $argv[1] ?? null;
$first = $argv[2] ?? 'Tester';

if (!$to) {
    echo "Usage: php send_test_email.php recipient@example.com [FirstName]\n";
    exit(1);
}

try {
    $mailer = new Mailer();
    $subject = 'Test email from ' . APP_NAME;
    $html = "<p>Hi {$first},</p><p>This is a test email sent from the Southdev application.</p>";
    $text = "Hi {$first},\n\nThis is a test email sent from the Southdev application.";
    $sent = $mailer->send($to, $first, $subject, $html, $text);
    if ($sent) {
        echo "Email successfully sent to {$to}\n";
        exit(0);
    } else {
        echo "Mailer->send returned false\n";
        exit(2);
    }
} catch (Throwable $e) {
    echo "Error sending email: " . $e->getMessage() . "\n";
    exit(3);
}
