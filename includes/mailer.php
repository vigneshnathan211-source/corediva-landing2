<?php
/**
 * Thin PHPMailer wrapper. Only pulled in by pages that actually send mail
 * (admin OTP today), so the public marketing pages never pay for the
 * autoloader.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send an HTML email via the configured SMTP relay.
 *
 * Returns true on success. Never throws -- send failures (including an
 * unconfigured host, which is the default until config.local.php's
 * `mail.*` placeholders are filled in) are logged and reported back as
 * false so callers can decide how to degrade instead of fataling the
 * request.
 */
function send_mail(string $toEmail, string $toName, string $subject, string $bodyHtml, string $bodyText = ''): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = (string) cfg('mail.host');
        $mail->SMTPAuth   = true;
        $mail->Username   = (string) cfg('mail.username');
        $mail->Password   = (string) cfg('mail.password');
        $mail->SMTPSecure = (string) cfg('mail.encryption', 'tls');
        $mail->Port       = (int) cfg('mail.port', 587);
        // Fail fast against a placeholder/unreachable host instead of
        // hanging on the OS-level TCP timeout.
        $mail->Timeout    = 8;
        $mail->SMTPDebug  = 0;

        $mail->setFrom((string) cfg('mail.from_email'), (string) cfg('mail.from_name', ''));
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = $bodyText !== '' ? $bodyText : strip_tags($bodyHtml);

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('Mail send failed: ' . $e->getMessage());
        return false;
    }
}
