<?php
/**
 * Admin-panel-only configuration: password policy, OTP and session
 * lifetimes.
 *
 * Split out from the public site's includes/config.php because nothing
 * outside /admin/ reads any of this -- otp_ttl_minutes, session_hours
 * and the rest only ever meant "the admin panel's" values, so keeping
 * them under a generic `security` key in the public config risked the
 * two meanings drifting apart as the site grows past this one module.
 *
 * No secrets live here (db/mail credentials stay in the public site's
 * config.local.php, which is what's gitignored), so unlike that file
 * there's no environment-specific override to keep out of version
 * control -- this array is the same in every environment.
 */

declare(strict_types=1);

return [
    'password' => [
        'min_length'            => 8,
        // Failed email+password attempts allowed per IP per hour, before
        // the login step itself is throttled (separate from OTP throttling
        // below, since a password can be brute-forced far faster than a
        // 6-digit code can be requested).
        'max_attempts_per_hour' => 10,
    ],

    'otp' => [
        'ttl_minutes'  => 10,
        'max_attempts' => 5,
        'max_per_hour' => 5,
    ],

    'session' => [
        'hours' => 8,
    ],
];
