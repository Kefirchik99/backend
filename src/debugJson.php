<?php
$dataFile = realpath(__DIR__ . '/../data/data.json');

if (!$dataFile || !file_exists($dataFile)) {
    die("JSON file not found or inaccessible: $dataFile\n");
}

$data = json_decode(file_get_contents($dataFile), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Invalid JSON: " . json_last_error_msg() . "\n");
}

echo "Inspecting JSON file for missing keys...\n";
foreach ($data['data'] as $index => $product) {
    $missingKeys = [];
    if (!isset($product['id'])) {
        $missingKeys[] = 'id';
    }
    if (!isset($product['name'])) {
        $missingKeys[] = 'name';
    }

    if (!empty($missingKeys)) {
        echo "Missing keys in product at index $index:\n";
        echo "Missing: " . implode(', ', $missingKeys) . "\n";
        print_r($product);
        echo "\n";
    }
}

echo "JSON inspection completed.\n";
