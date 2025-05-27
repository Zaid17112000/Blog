<?php
require_once __DIR__ . '/bootstrap.php'; // Load environment first

$dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}";
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO(
        $dsn,
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        $options
    );
    
    // Connection successful (debug only)
    // error_log("Database connection established");

} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database maintenance in progress. Please try again later.");
}