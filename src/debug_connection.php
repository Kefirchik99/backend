<?php
$dsn = 'mysql:host=mysql-container;dbname=scandiweb;charset=utf8mb4';
$username = 'root';
$password = 'root_password';

try {
    $pdo = new PDO($dsn, $username, $password);
    echo "Connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
