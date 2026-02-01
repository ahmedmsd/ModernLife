<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=ModernLife_test', 'root', 'root');
$stmt = $pdo->query('SELECT name FROM roles');
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
