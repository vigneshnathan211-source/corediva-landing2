<?php
/**
 * Thin PHPMailer wrapper, plus the lead-notification email templates.
 * Only pulled in by code paths that actually send mail (admin OTP,
 * save_lead()) -- required lazily from inside those functions rather than
 * unconditionally from functions.php, so pages that don't send mail never
 * pay for the autoloader.
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

/**
 * Shared branded shell for transactional emails: table layout, inline
 * styles only, no <style> block -- same reasoning as the admin OTP email
 * (admin/includes/auth.php::admin_otp_email_html()), duplicated here
 * rather than shared across files, since the two templates have no other
 * coupling and email HTML is fragile enough that copy-paste is safer than
 * a shared abstraction two callers would have to renegotiate.
 *
 * $bodyHtml is inserted as-is inside the content cell -- callers build
 * their own inner markup (paragraphs, a details table) and pass it in.
 */
function email_shell_html(string $eyebrow, string $heading, string $bodyHtml, string $footerNote): string
{
    $siteName = esc(setting('site_name', 'Corediva Tech Solutions'));
    $logoUrl  = esc(public_asset('imgs/corediva-logo.png'));

    return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="color-scheme" content="light">
        <title>{$heading}</title>
        </head>
        <body style="margin:0; padding:0; background-color:#F4F6FB; -webkit-text-size-adjust:100%;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F4F6FB;">
        <tr>
        <td align="center" style="padding:40px 16px;">

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px; background-color:#FFFFFF; border-radius:16px; border:1px solid #E4E7F0;">
                <tr>
                    <td style="padding:32px 36px 0;">
                        <img src="{$logoUrl}" width="140" height="18" alt="{$siteName}" style="display:block; border:0; outline:0;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 36px 0; font-family:'DM Sans',Arial,Helvetica,sans-serif;">
                        <p style="margin:0 0 6px; font-size:12.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#1351D8;">{$eyebrow}</p>
                        <h1 style="margin:0 0 12px; font-size:21px; line-height:1.35; font-weight:700; color:#1C1C1C;">{$heading}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 36px 4px; font-family:'DM Sans',Arial,Helvetica,sans-serif; font-size:14.5px; line-height:1.6; color:#454545;">
                        {$bodyHtml}
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 36px 32px; font-family:'DM Sans',Arial,Helvetica,sans-serif;">
                        <p style="margin:0; font-size:13px; line-height:1.6; color:#8A8F9C;">{$footerNote}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 36px; border-top:1px solid #EDEFF5; background-color:#FAFBFD; border-radius:0 0 16px 16px; font-family:'DM Sans',Arial,Helvetica,sans-serif;">
                        <p style="margin:0; font-size:12px; color:#9198A6;">{$siteName} &middot; This is an automated message, please don't reply.</p>
                    </td>
                </tr>
            </table>

        </td>
        </tr>
        </table>
        </body>
        </html>
    HTML;
}

/**
 * Internal notification, sent to `mail.lead_notify` whenever a lead form
 * (hero, RFQ, or any future variant -- they all funnel through save_lead())
 * is submitted successfully.
 */
function lead_internal_notification_html(array $lead, ?array $country): string
{
    $rows = [
        'Name'          => $lead['name'],
        'Email'         => $lead['email'],
        'Phone'         => $lead['phone'] ?? null,
        'Country'       => $country['name'] ?? $lead['country_code'] ?? null,
        'Interested in' => $lead['service_interest'] ?? null,
        'Message'       => $lead['message'] ?? null,
        'Source page'   => $lead['source_url'] ?? null,
    ];

    $rowsHtml = '';
    foreach ($rows as $label => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $rowsHtml .= '<tr>'
            . '<td style="padding:8px 12px; border-bottom:1px solid #EDEFF5; font-weight:600; color:#1C1C1C; white-space:nowrap; vertical-align:top;">' . esc($label) . '</td>'
            . '<td style="padding:8px 12px; border-bottom:1px solid #EDEFF5; color:#454545;">' . nl2br(esc((string) $value)) . '</td>'
            . '</tr>';
    }

    $body = '<p style="margin:0 0 16px;">A new enquiry landed on the site. Reply directly to the address below --'
          . ' it goes straight to the person who submitted it.</p>'
          . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #EDEFF5; border-radius:10px; overflow:hidden;">'
          . $rowsHtml
          . '</table>';

    return email_shell_html(
        'New enquiry',
        esc($lead['name']) . ' just submitted a lead',
        $body,
        'Manage this and every other enquiry from the Leads screen in the admin panel.'
    );
}

/**
 * Auto-reply confirmation, sent to the person who submitted the form.
 */
function lead_confirmation_html(array $lead): string
{
    $hours = esc(setting('response_time_hours', '4'));
    $name  = esc(explode(' ', trim($lead['name']))[0] ?: $lead['name']);

    $body = '<p style="margin:0 0 16px;">Hi ' . $name . ', thanks for reaching out. Your message has reached our'
          . ' engineering team, and we reply within ' . $hours . ' hours on business days.</p>'
          . '<p style="margin:0 0 16px;">If it is easier, you can also message us directly on WhatsApp using the'
          . ' number on our site, and we will pick up the same enquiry.</p>';

    return email_shell_html(
        'Enquiry received',
        'We\'ve got your message',
        $body,
        'If you did not submit this enquiry, you can safely ignore this email.'
    );
}

/**
 * Sends both the internal notification and the submitter's confirmation
 * for a just-saved lead. Called from save_lead() after the DB insert
 * succeeds -- never blocks or fails the submission itself; a lead that's
 * in the database is already captured regardless of whether either email
 * goes out, so both sends are best-effort and failures only get logged
 * (inside send_mail()), not surfaced to the visitor.
 */
function send_lead_notifications(array $lead, ?array $country): void
{
    $notifyTo = (string) cfg('mail.lead_notify', '');
    if ($notifyTo !== '') {
        send_mail(
            $notifyTo,
            (string) setting('site_name', 'Corediva Tech Solutions'),
            'New enquiry: ' . $lead['name'] . ($country ? ' (' . $country['name'] . ')' : ''),
            lead_internal_notification_html($lead, $country)
        );
    }

    send_mail(
        $lead['email'],
        $lead['name'],
        'We\'ve received your enquiry -- ' . (string) setting('site_name', 'Corediva Tech Solutions'),
        lead_confirmation_html($lead)
    );
}
