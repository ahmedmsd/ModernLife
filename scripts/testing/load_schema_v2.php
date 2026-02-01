<?php
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'ModernLife_test';
// Use the larger file if it exists, otherwise the small one
$sqlFile = 'u499459033_modernlife.sql'; 

echo "Loading $sqlFile into $dbName...\n";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);

    $sql = file_get_contents($sqlFile);
    
    // Remove comments
    $sql = preg_replace('/--.*?\n/', "\n", $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    $queries = explode(";\n", $sql);
    
    $pdo->beginTransaction();
    foreach ($queries as $query) {
        $query = trim($query);
        if ($query) {
            try {
                $pdo->exec($query);
            } catch (Exception $e) {
                echo "Warning: Failed to execute query: " . substr($query, 0, 50) . "... Error: " . $e->getMessage() . "\n";
            }
        }
    }
    $pdo->commit();
    
    echo "Done.\n";
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
