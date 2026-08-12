<?php

use Valet\Drivers\BasicValetDriver;

/**
 * Mirrors the .htaccess extensionless-URL rewrite for local dev under
 * Herd/Valet, which serves this site through nginx and never reads
 * .htaccess. Without this, a clean URL like /sg/services/ falls through
 * Valet's BasicValetDriver straight to the site's root index.php (a 302 to
 * /sg/), because no sg/services/index.php file exists on disk -- production
 * Apache resolves the same URL correctly via mod_rewrite, so only local dev
 * needs the assist. See .htaccess for the rule this mirrors.
 */
class LocalValetDriver extends BasicValetDriver
{
    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        $trimmed = rtrim($uri, '/');

        if ($trimmed !== '' && $this->isActualFile($candidate = $sitePath.$trimmed.'.php')) {
            $_SERVER['SCRIPT_FILENAME'] = $candidate;
            $_SERVER['SCRIPT_NAME']     = str_replace($sitePath, '', $candidate);
            $_SERVER['DOCUMENT_ROOT']   = $sitePath;

            return $candidate;
        }

        return parent::frontControllerPath($sitePath, $siteName, $uri);
    }
}
