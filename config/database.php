<?php

$localConfig = __DIR__ . '/database.local.php';

if (!file_exists($localConfig)) {
    throw new RuntimeException(
        'Database configuration file is missing.'
    );
}

$config = require $localConfig;

$host = $config['host'];
$port = $config['port'];
$dbname = $config['database'];
$username = $config['username'];
$password = $config['password'];

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());

    throw new RuntimeException(
        'Unable to connect to the database.'
    );
}

return $pdo;
