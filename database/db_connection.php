<?php
// Load the app config
$config = require __DIR__ . '/../config/app.php';

// Pull DB credentials from config (avoid common variable names that can collide in includes)
$dbHost = $config['db_host'];
$dbName = $config['db_name'];
$dbUser = $config['db_username'];
$dbPass = $config['db_password'];

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Make $pdo globally accessible
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

} catch (PDOException $e) {
    if ($config['debug']) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection error.");
    }
}
