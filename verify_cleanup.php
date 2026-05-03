<?php
$host = '127.0.0.1';
$db   = 'ModernLife';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE read_at IS NULL AND created_at < '2026-04-01 00:00:00'");
    $count = $stmt->fetchColumn();

    echo "Unread notifications before April: " . $count . "\n";
    
    $stmt2 = $pdo->query("SELECT COUNT(*) FROM notifications WHERE read_at IS NULL");
    $totalUnread = $stmt2->fetchColumn();
    echo "Total unread notifications: " . $totalUnread . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
