<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=ModernLife_test', 'root', 'root');
$stmt = $pdo->query('SELECT migration FROM migrations');
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
