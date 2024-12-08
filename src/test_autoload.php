<?php

require_once __DIR__ . '/../../vendor/autoload.php';

echo "Testing autoload...\n";

// Check if autoload file exists
$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "Autoload file exists at: $autoloadPath\n";
} else {
    echo "Autoload file NOT found at: $autoloadPath\n";
    exit(1);
}

// Check if Database.php exists
$databasePath = __DIR__ . '/Config/Database.php';
if (file_exists($databasePath)) {
    echo "Database.php found at: $databasePath\n";
} else {
    echo "Database.php NOT found at: $databasePath\n";
    exit(1);
}

// Test if the Database class is resolvable
use Yaro\EcommerceProject\Config\Database;

if (class_exists(Database::class)) {
    echo "Autoloader is working! Database class found.\n";
} else {
    echo "Autoloader is NOT working! Database class not found.\n";
    echo "Possible issues:\n";
    echo "- Incorrect namespace in Database.php\n";
    echo "- Incorrect directory structure for PSR-4\n";
    echo "- Autoload not regenerated. Run 'composer dump-autoload'.\n";
}
