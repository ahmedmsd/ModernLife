<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=ModernLife_test', 'root', 'root');
$stmt = $pdo->query('SELECT * FROM department_categories');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
