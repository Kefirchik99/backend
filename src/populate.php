<?php

namespace Yaro\EcommerceProject;

require_once __DIR__ . '/../bootstrap.php';

use Yaro\EcommerceProject\Utils\JsonLoader;
use Yaro\EcommerceProject\Utils\DatabaseSeeder;

try {
    // Load JSON data
    $dataFile = realpath(__DIR__ . '/../data/data.json');
    if (!$dataFile) {
        throw new \Exception("Data file not found.");
    }
    $data = JsonLoader::load($dataFile);

    // Seed the database
    DatabaseSeeder::seed($data['data']);

    echo "Database populated successfully.\n";
} catch (\Exception $e) {
    die("Error during database seeding: " . $e->getMessage() . "\n");
}
