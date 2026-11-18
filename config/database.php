<?php

declare(strict_types=1);

$localConfig = __DIR__ . '/database.local.php';

if (!file_exists($localConfig)) {
    throw new RuntimeException(
        'Database configuration file is missing.'
    );
}

$config = require $localConfig;

if (!is_array($config)) {
    throw new RuntimeException(
        'Invalid database configuration file.'
    );
}

// Validate required configuration values.
$requiredKeys = [
    'host',
    'port',
    'database',
    'username',
    'password',
];

foreach ($requiredKeys as $key) {
    if (!array_key_exists($key, $config)) {
        throw new RuntimeException(
            "Missing database configuration: {$key}"
        );
    }
}

$host = $config['host'];
$port = (int) $config['port'];
$dbname = $config['database'];
$username = $config['username'];
$password = $config['password'];

if (
    !is_string($host) ||
    !is_string($dbname) ||
    !is_string($username) ||
    !is_string($password) ||
    $port < 1 ||
    $port > 65535
) {
    throw new RuntimeException(
        'Invalid database configuration.'
    );
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    error_log(
        'Database connection failed: ' . $e->getMessage()
    );

    throw new RuntimeException(
        'Unable to connect to the database.'
    );
}

return $pdo;