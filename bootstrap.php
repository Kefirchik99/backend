<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Use the correct namespace for Database class
use Yaro\EcommerceProject\Config\Database;

// Initialize database connection
$db = Database::getConnection();

// Adjust namespaces for Category and Product models if necessary.
// For example, if they are located under Yaro\EcommerceProject\Models:
use Yaro\EcommerceProject\Models\Category;
use Yaro\EcommerceProject\Models\Product;

$dataFile = __DIR__ . '/data/data.json';

if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);

    // Populate categories
    foreach ($data['categories'] as $categoryData) {
        $category = new Category($categoryData['name']);
        $category->save();
    }

    // Populate products
    foreach ($data['products'] as $productData) {
        // Assuming Category::find() returns an array with 'id' as a key
        $categoryId = Category::find($productData['category'])['id'];

        $product = new Product(
            $productData['name'],
            $productData['description'],
            $productData['brand'],
            $categoryId,
            $productData['inStock']
        );
        $product->save();
    }
}
