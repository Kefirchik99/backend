<?php

namespace Yaro\EcommerceProject\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    'mysql:host=mysql-container;dbname=scandiweb;charset=utf8mb4',
                    'root', // MySQL username
                    'root_password', // MySQL password
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                error_log('Connection failed: ' . $e->getMessage());
                die('Connection failed: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    // Optional: Add a method to close the connection if needed
    public static function closeConnection(): void
    {
        self::$instance = null;
    }
}
