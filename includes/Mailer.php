<?php
/**
 * PHPMailer wrapper for sending system emails
 */

use PHPMailer\PHPMailer\PHPMailer;

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

class Mailer {
    private $mailer;
    private $useFallback = false;

    public function __construct() {
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            // PHPMailer not available; use file-based fallback to allow local testing
            $this->useFallback = true;
            return;
        }

        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure() {
        if (empty(MAIL_HOST) || empty(MAIL_FROM_EMAIL)) {
            throw new \Exception('Mail host/from settings are missing. Check MAIL_HOST and MAIL_FROM_EMAIL in config/config.php.');
        }

        if (empty(MAIL_USERNAME) || empty(MAIL_PASSWORD)) {
            throw new \Exception('SMTP credentials are missing. Set MAIL_USERNAME and MAIL_PASSWORD in config/config.php.');
        }

        $this->mailer->isSMTP();
        $this->mailer->Host = MAIL_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = MAIL_USERNAME;
        $this->mailer->Password = MAIL_PASSWORD;
        $this->mailer->Port = (int) MAIL_PORT;

        $encryption = strtolower(trim((string) MAIL_ENCRYPTION));
        if ($encryption === 'tls' || $encryption === 'starttls') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption !== '' && $encryption !== 'none') {
            throw new \Exception('Invalid MAIL_ENCRYPTION value. Use tls, ssl, or none.');
        }

        $this->mailer->CharSet = 'UTF-8';

        // Improve deliverability for Yahoo/Outlook
        $this->mailer->XMailer = 'SouthDev Home Depot Mailer';
        $this->mailer->MessageID = '<' . bin2hex(random_bytes(16)) . '@southdev-home-depot>';
        // Avoid spam triggers: set proper Sender header
        $this->mailer->Sender = MAIL_FROM_EMAIL;

        $this->mailer->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $this->mailer->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    }

    public function send($toEmail, $toName, $subject, $htmlBody, $textBody = '') {
        if ($this->useFallback) {
            return $this->fallbackSend($toEmail, $toName, $subject, $htmlBody, $textBody);
        }
        $this->mailer->clearAllRecipients();
        $this->mailer->addAddress($toEmail, $toName);
        $this->mailer->Subject = $subject;
        $this->mailer->isHTML(true);
        $this->mailer->Body = $htmlBody;
        $this->mailer->AltBody = $textBody ?: strip_tags($htmlBody);

        return $this->mailer->send();
    }

    /**
     * File-based fallback mailer for local development when PHPMailer isn't installed.
     * Writes an HTML and text file to storage/mails and returns true.
     */
    private function fallbackSend($toEmail, $toName, $subject, $htmlBody, $textBody = '') {
        $dir = ROOT_PATH . '/storage/mails';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $time = date('Ymd_His');
        $safeTo = preg_replace('/[^a-z0-9@._-]/i', '_', $toEmail);
        $id = bin2hex(random_bytes(6));
        $base = $dir . "/{$time}_{$safeTo}_{$id}";
        $meta = [
            'to' => $toEmail,
            'name' => $toName,
            'subject' => $subject,
            'time' => date('c')
        ];
        file_put_contents($base . '.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($base . '.html', $htmlBody);
        file_put_contents($base . '.txt', $textBody ?: strip_tags($htmlBody));
        return true;
    }
}
