<?php
/**
 * Shared handler for admin-uploaded media (hero videos, service banner
 * images). Every other "image" field in this admin (image_feature's
 * media_url) is a plain text path -- this is the one place that accepts an
 * actual binary upload, so the validation lives here once rather than
 * copy-pasted into settings.php and services.php.
 *
 * Files land under assets/{$subdir}/ with a random filename (the original
 * name is never trusted or reused), so the return value is a path already
 * relative to /assets/, ready to hand straight to asset() or store in the
 * DB as-is.
 */

declare(strict_types=1);

/**
 * @param array<string,mixed> $file       one $_FILES[...] entry
 * @param string[]            $allowedExt lowercase extensions without the dot
 * @return array{0: ?string, 1: ?string} [relative asset path, error message] -- exactly one is non-null, or both are null when no file was chosen
 */
function admin_store_upload(array $file, string $subdir, array $allowedExt, string $mimePrefix, int $maxBytes): array
{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($error !== UPLOAD_ERR_OK) {
        return [null, 'Upload failed (error code ' . $error . ').'];
    }
    if (!is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
        return [null, 'Upload failed validation.'];
    }
    if ((int) ($file['size'] ?? 0) > $maxBytes) {
        return [null, 'File is too large (max ' . (int) round($maxBytes / 1048576) . ' MB).'];
    }

    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return [null, 'Unsupported file type: .' . esc($ext)];
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = (string) $finfo->file($file['tmp_name']);
    if (!str_starts_with($mimeType, $mimePrefix)) {
        return [null, 'That file does not look like a valid ' . rtrim($mimePrefix, '/') . ' file.'];
    }

    $dir = dirname(__DIR__, 2) . '/assets/' . trim($subdir, '/');
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return [null, 'Could not create the upload directory.'];
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
        return [null, 'Could not save the uploaded file.'];
    }

    return [trim($subdir, '/') . '/' . $filename, null];
}
