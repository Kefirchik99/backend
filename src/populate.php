<?php

namespace Yaro\EcommerceProject;

require_once __DIR__ . '/../bootstrap.php';

use Yaro\EcommerceProject\Utils\JsonLoader;
use Yaro\EcommerceProject\Utils\DatabaseSeeder;

try {
    $dataFile = realpath(__DIR__ . '/../data/data.json');
    if (!$dataFile) {
        die("Resolved data file path is invalid. Path attempted: " . __DIR__ . '/../data/data.json' . "\n");
    }
    echo "Resolved data file path: $dataFile\n";

    // Load JSON data
    $data = JsonLoader::load($dataFile);

    if (!isset($data['data']) || !is_array($data['data'])) {
        throw new \Exception("Invalid JSON structure. 'data' key missing or not an array.");
    }

    DatabaseSeeder::seed($data['data']);

    echo "Database populated successfully.\n";
} catch (\Exception $e) {
    die("Error during database seeding: " . $e->getMessage() . "\n");
}
