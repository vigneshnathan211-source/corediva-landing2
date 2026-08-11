<?php
/**
 * Site root.
 *
 * Unconditional 302 to the primary market. Deliberately NOT geo-IP based:
 * Googlebot crawls predominantly from US IPs, so a geo-redirect would send
 * the crawler to /us/ every time and leave /sg/ under-crawled. A plain
 * redirect keeps `x-default = /sg/` coherent for every visitor and crawler.
 *
 * 302 (not 301) because which market is "primary" is a business decision
 * that may change -- a cached 301 would be very hard to walk back.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$default = (string) cfg('app.default_country', 'sg');

header('Location: ' . country_url($default), true, 302);
header('Cache-Control: no-store');
exit;
