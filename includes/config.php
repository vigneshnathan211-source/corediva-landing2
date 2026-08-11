<?php
/**
 * Base configuration.
 *
 * Everything here is a safe default / placeholder. Real credentials live in
 * includes/config.local.php, which is gitignored and overrides these values.
 * Copy config.example.php -> config.local.php on each machine/server.
 */

declare(strict_types=1);

$defaults = [

    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'corediva_landing',
        'user'    => '',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    'app' => [
        // No trailing slash. Used for canonical URLs, hreflang and schema.org.
        'base_url'        => 'https://www.corediva365.com',
        'default_country' => 'sg',
        'timezone'        => 'Asia/Singapore',
        // true on dev only: prints PHP errors to the page.
        'debug'           => false,
    ],

    'mail' => [
        // PHPMailer SMTP settings -- fill these in config.local.php.
        'host'        => 'smtp.example.com',
        'port'        => 587,
        'username'    => 'PLACEHOLDER',
        'password'    => 'PLACEHOLDER',
        'encryption'  => 'tls',            // 'tls' or 'ssl'
        'from_email'  => 'noreply@corediva365.com',
        'from_name'   => 'Corediva Tech Solutions',
        // Where new lead notifications are sent.
        'lead_notify' => 'support@corediva365.com',
    ],

    'security' => [
        // Max lead submissions from one IP per hour. Admin-panel security
        // settings (OTP TTL, password policy, session lifetime) live in
        // admin/includes/config.php instead -- nothing outside /admin/
        // reads them, so they don't belong in the public site's config.
        'lead_max_per_hour'   => 5,
    ],
];

$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (is_array($local)) {
        return array_replace_recursive($defaults, $local);
    }
}

return $defaults;
