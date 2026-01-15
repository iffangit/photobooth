<?php
// config.php
$host = 'localhost';
$dbname = 'photobooth_db';
$username = 'root'; // Adjust if using MAMP default (root/root)
$password = '';     // Adjust if using MAMP default (root)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
