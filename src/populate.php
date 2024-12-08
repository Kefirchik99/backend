<?php

namespace Yaro\EcommerceProject;

require_once __DIR__ . '/../bootstrap.php';

use Yaro\EcommerceProject\Utils\JsonLoader;
use Yaro\EcommerceProject\Utils\DatabaseSeeder;

try {
    // Load JSON data
    $data = JsonLoader::load(__DIR__ . '/../data/data.json');

    // Seed the database
    DatabaseSeeder::seed($data);

    echo "Database populated successfully.\n";
} catch (\Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
