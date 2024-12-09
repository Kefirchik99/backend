<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Yaro\EcommerceProject\Config\Database;
use Yaro\EcommerceProject\Utils\DatabaseSeeder;
use Yaro\EcommerceProject\Utils\JsonLoader;

try {
    // Initialize database connection
    $db = Database::getConnection();

    // Load data from JSON file
    $dataFile = realpath(__DIR__ . '/../data/data.json');
    if (!$dataFile || !file_exists($dataFile)) {
        throw new Exception("File not found or inaccessible: " . ($dataFile ?? 'Invalid path'));
    }

    $data = JsonLoader::load($dataFile);

    // Seed the database
    DatabaseSeeder::seed($data);

    echo "Database populated successfully.\n";
} catch (Exception $e) {
    echo "Error during database seeding: " . $e->getMessage() . "\n";
}
