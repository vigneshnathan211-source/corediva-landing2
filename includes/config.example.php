<?php
/**
 * Copy this file to config.local.php and fill in real values.
 * config.local.php is gitignored and must NEVER be committed.
 *
 *   cp includes/config.example.php includes/config.local.php
 *
 * Only include the keys you want to override -- they are merged over the
 * defaults in config.php, so anything omitted keeps its default.
 */

return [

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'corediva_landing',
        'user' => 'YOUR_DB_USER',
        'pass' => 'YOUR_DB_PASSWORD',
    ],

    'app' => [
        'base_url' => 'https://www.corediva365.com',
        'debug'    => false,
    ],

    'mail' => [
        'host'        => 'smtp.hostinger.com',
        'port'        => 587,
        'username'    => 'noreply@corediva365.com',
        'password'    => 'YOUR_SMTP_PASSWORD',
        'encryption'  => 'tls',
        'from_email'  => 'noreply@corediva365.com',
        'from_name'   => 'Corediva Tech Solutions',
        'lead_notify' => 'support@corediva365.com',
    ],
];
