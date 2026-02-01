<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=ModernLife_test', 'root', 'root');
$table = $argv[1] ?? 'projects';
$stmt = $pdo->query("DESCRIBE $table");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
