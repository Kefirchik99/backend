<?php
 
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;

// Establish database connection
$pdo = Database::connect();

// Read JSON data
$jsonData = file_get_contents(__DIR__ . '/../data/data.json');
$data = json_decode($jsonData, true);

if (!$data) {
    die("Error decoding JSON data.");
}

// Insert categories
$categoryModel = new Category($pdo);
foreach ($data['categories'] as $category) {
    $categoryModel->insert($category);
}

// Insert attributes
$attributeModel = new Attribute($pdo);
foreach ($data['attributes'] as $attribute) {
    $attributeModel->insert($attribute);
}

// Insert products
$productModel = new Product($pdo);
foreach ($data['products'] as $product) {
    $productModel->insert($product);
}

echo "Data successfully inserted!";
