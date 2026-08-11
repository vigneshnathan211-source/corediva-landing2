<?php
/**
 * Database connection. Included by every page.
 *
 * Exposes db() returning a shared PDO handle. Real prepared statements are
 * forced on (emulation off) so bound params are never interpolated by PDO
 * into the SQL string.
 */

declare(strict_types=1);

if (!isset($GLOBALS['corediva_config'])) {
    $GLOBALS['corediva_config'] = require __DIR__ . '/config.php';
}

$__cfg = $GLOBALS['corediva_config'];

date_default_timezone_set($__cfg['app']['timezone'] ?? 'UTC');

if (!empty($__cfg['app']['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

/**
 * Shared PDO handle.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = $GLOBALS['corediva_config']['db'];

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $cfg['host'],
        (int) $cfg['port'],
        $cfg['name'],
        $cfg['charset']
    );

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
    } catch (PDOException $e) {
        // Never echo the exception: it contains the DSN, user and sometimes host.
        error_log('DB connection failed: ' . $e->getMessage());
        http_response_code(503);
        exit('Service temporarily unavailable.');
    }

    return $pdo;
}

/**
 * Fetch all rows for a query.
 */
function db_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Fetch a single row, or null.
 */
function db_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/**
 * Fetch a single scalar value, or null.
 */
function db_value(string $sql, array $params = [])
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $val = $stmt->fetchColumn();
    return $val === false ? null : $val;
}

/**
 * Run an INSERT/UPDATE/DELETE, returning affected row count.
 */
function db_exec(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}
