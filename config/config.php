<?php
session_start();

$host = 'localhost';
$dbname = 'sample_php_pdo';
$username = 'root';
$password = '';

define('BASE_URL', 'http://localhost/app');


try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>