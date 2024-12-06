<?php

namespace App\Models;

use App\Config\Database;


require_once __DIR__ . '/../config/Database.php';

// Connect to the database
$pdo = Database::connect();

// Read the JSON file
$jsonFilePath = __DIR__ . '/../data/data.json';
$jsonData = file_get_contents($jsonFilePath);
$data = json_decode($jsonData, true);

if (!$data) {
    die("Failed to parse JSON file.\n");
}

// Insert categories
foreach ($data['categories'] as $category) {
    $stmt = $pdo->prepare("INSERT INTO categories (id, name) VALUES (:id, :name)");
    $stmt->execute([
        'id' => $category['id'],
        'name' => $category['name']
    ]);
}
echo "Categories inserted successfully.\n";

// Insert attributes
foreach ($data['attributes'] as $attribute) {
    $stmt = $pdo->prepare("INSERT INTO attributes (id, name) VALUES (:id, :name)");
    $stmt->execute([
        'id' => $attribute['id'],
        'name' => $attribute['name']
    ]);
}
echo "Attributes inserted successfully.\n";

// Insert products and their attributes
foreach ($data['products'] as $product) {
    $stmt = $pdo->prepare("
        INSERT INTO products (id, name, sku, price, category_id)
        VALUES (:id, :name, :sku, :price, :category_id)
    ");
    $stmt->execute([
        'id' => $product['id'],
        'name' => $product['name'],
        'sku' => $product['sku'],
        'price' => $product['price'],
        'category_id' => $product['category_id']
    ]);

    // Insert product attributes
    foreach ($product['attributes'] as $attribute) {
        $stmt = $pdo->prepare("
            INSERT INTO product_attributes (product_id, attribute_id, value)
            VALUES (:product_id, :attribute_id, :value)
        ");
        $stmt->execute([
            'product_id' => $product['id'],
            'attribute_id' => $attribute['attribute_id'],
            'value' => $attribute['value']
        ]);
    }
}
echo "Products and product attributes inserted successfully.\n";
