<?php
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'ModernLife_test';

try {
    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbName' created successfully or already exists.\n";
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
