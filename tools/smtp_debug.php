<?php
/**
 * SMTP debug helper — runs a PHPMailer send with verbose debug output.
 * Usage: php smtp_debug.php recipient@example.com [Name]
 */

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}
require_once __DIR__ . '/../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$to = $argv[1] ?? null;
$name = $argv[2] ?? 'Tester';

if (!$to) {
    echo "Usage: php smtp_debug.php recipient@example.com [Name]\n";
    exit(1);
}

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->Port = (int) MAIL_PORT;

    $enc = strtolower(trim((string) MAIL_ENCRYPTION));
    if ($enc === 'tls' || $enc === 'starttls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } elseif ($enc === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }

    // Verbose debug output to stdout
    $mail->SMTPDebug = 3; // 0 = off, 1 = client, 2 = client+server, 3 = more
    $mail->Debugoutput = function($str, $level) { echo $str . PHP_EOL; };

    // Allow self-signed for local test environments (optional)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress($to, $name);
    $mail->Subject = 'SMTP Debug — ' . APP_NAME;
    $mail->isHTML(true);
    $mail->Body = "<p>Hi {$name},</p><p>This is a SMTP debug test from " . APP_NAME . "</p>";
    $mail->AltBody = "Hi {$name},\n\nThis is a SMTP debug test from " . APP_NAME;

    echo "Attempting SMTP send to {$to} using host " . MAIL_HOST . ":" . MAIL_PORT . "\n";
    $ok = $mail->send();
    if ($ok) {
        echo "SMTP send succeeded.\n";
        exit(0);
    } else {
        echo "SMTP send returned false.\n";
        exit(2);
    }

} catch (Exception $e) {
    echo "PHPMailer Exception: " . $e->getMessage() . "\n";
    exit(3);
} catch (Throwable $t) {
    echo "Error: " . $t->getMessage() . "\n";
    exit(4);
}
