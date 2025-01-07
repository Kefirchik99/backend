<?php

require_once '/var/www/html/vendor/autoload.php';

use Yaro\EcommerceProject\Config\Database;

echo "Testing database connection...\n";

try {
    $db = Database::getConnection();
    echo "Database connection successful!\n";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
