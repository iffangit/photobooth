<?php
// config.php
require_once __DIR__ . '/env_loader.php';

// Ensure consistent session handling across all directories
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/');
}

// Load .env from root directory
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    EnvLoader::load($envPath);
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'photobooth_db';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Emulate prepared statements for security (good practice)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Hide error details in production
    error_log("Database Connection Failed: " . $e->getMessage());
    die("System Error: Unable to connect to database. Please check logs.");
}
?>