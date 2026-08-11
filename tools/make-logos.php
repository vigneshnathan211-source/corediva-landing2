<?php
/**
 * Dev helper: generate web-ready logo assets from the master Corediva logo.
 *
 *   php tools/make-logos.php <path-to-master.png>
 *
 * Produces:
 *   assets/imgs/corediva-logo.png        resized for the light header
 *   assets/imgs/corediva-logo-white.png  all-white, for the dark footer
 *
 * The master file lives outside the repo (Docs/ is gitignored); only the
 * generated assets are committed.
 */

declare(strict_types=1);

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD extension is required.\n");
    exit(1);
}

$src = $argv[1] ?? null;
if (!$src || !is_file($src)) {
    fwrite(STDERR, "Usage: php tools/make-logos.php <path-to-master.png>\n");
    exit(1);
}

$outDir      = __DIR__ . '/../assets/imgs';
$targetWidth = 440;

$master = imagecreatefrompng($src);
if (!$master) {
    fwrite(STDERR, "Could not read {$src}\n");
    exit(1);
}

$sw = imagesx($master);
$sh = imagesy($master);
$th = (int) round($sh * ($targetWidth / $sw));

/** Resize preserving alpha. */
function resized(GdImage $src, int $w, int $h): GdImage
{
    $dst = imagecreatetruecolor($w, $h);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
    return $dst;
}

// ---- Colour version ----
$colour = resized($master, $targetWidth, $th);
imagepng($colour, $outDir . '/corediva-logo.png', 9);
echo "wrote corediva-logo.png ({$targetWidth}x{$th})\n";

// ---- White version: recolour every visible pixel to white, keep alpha ----
$white = resized($master, $targetWidth, $th);
imagealphablending($white, false);
imagesavealpha($white, true);

for ($y = 0; $y < $th; $y++) {
    for ($x = 0; $x < $targetWidth; $x++) {
        $rgba  = imagecolorat($white, $x, $y);
        $alpha = ($rgba >> 24) & 0x7F;
        if ($alpha === 127) {
            continue; // fully transparent -- leave it
        }
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;

        // Near-white pixels in the master are background, not ink. Treat them
        // as transparent so the mark sits cleanly on the dark footer.
        if ($r > 240 && $g > 240 && $b > 240) {
            imagesetpixel($white, $x, $y, imagecolorallocatealpha($white, 0, 0, 0, 127));
            continue;
        }

        imagesetpixel($white, $x, $y, imagecolorallocatealpha($white, 255, 255, 255, $alpha));
    }
}

imagepng($white, $outDir . '/corediva-logo-white.png', 9);
echo "wrote corediva-logo-white.png ({$targetWidth}x{$th})\n";
