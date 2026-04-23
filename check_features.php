<?php
require 'config/database.php';
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->query('SELECT plan_type, features FROM plans ORDER BY id');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['plan_type'] . ': ' . $row['features'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>