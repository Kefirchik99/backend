<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    public static function connect()
    {
        $dsn = 'mysql:host=mysql-container;dbname=scandiweb;charset=utf8mb4';
        $username = 'user';
        $password = 'user_password';

        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Error connecting to the database: " . $e->getMessage());
        }
    }
}
