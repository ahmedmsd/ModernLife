<?php
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'ModernLife_test';
$sqlFile = 'u499459033_modernlife.sql';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon but be careful with triggers/routines (though probably not here)
    // For simplicity, we'll try to execute the whole thing if it's small enough
    $pdo->exec($sql);
    
    echo "Schema loaded successfully from $sqlFile.\n";
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
