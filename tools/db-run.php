<?php
/**
 * Dev helper: execute a .sql file against the configured database.
 * Usage:  php tools/db-run.php database/schema.sql
 *
 * Not deployed to production -- this is a local convenience because the
 * mysql CLI client isn't installed on the dev machine.
 */

declare(strict_types=1);

$config = require __DIR__ . '/../includes/config.php';

$file = $argv[1] ?? null;
if (!$file) {
    fwrite(STDERR, "Usage: php tools/db-run.php <path-to.sql>\n");
    exit(1);
}

$path = $file[0] === '/' || preg_match('/^[A-Za-z]:/', $file)
    ? $file
    : __DIR__ . '/../' . $file;

if (!is_file($path)) {
    fwrite(STDERR, "Not found: {$path}\n");
    exit(1);
}

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $config['db']['host'],
    $config['db']['port'],
    $config['db']['name'],
    $config['db']['charset']
);

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $sql = file_get_contents($path);
    $pdo->exec($sql);
    echo "OK: executed " . basename($path) . "\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo count($tables) . " tables now present\n";
} catch (Throwable $e) {
    fwrite(STDERR, "FAILED: " . $e->getMessage() . "\n");
    exit(1);
}
