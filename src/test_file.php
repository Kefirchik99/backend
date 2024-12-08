<?php

require_once __DIR__ . '/../bootstrap.php';

use Yaro\EcommerceProject\Models\Category;
use Yaro\EcommerceProject\Models\Product;

echo "Testing database connection...\n";

// Test: Create a new category
try {
    $category = new Category("Electronics");
    $category->save();
    echo "Category 'Electronics' saved successfully!\n";
} catch (\Exception $e) {
    echo "Error saving category: " . $e->getMessage() . "\n";
}

// Test: Fetch all categories
try {
    $categories = Category::all();
    echo "Fetched categories:\n";
    print_r($categories);
} catch (\Exception $e) {
    echo "Error fetching categories: " . $e->getMessage() . "\n";
}

// Test: Insert a product
try {
    $categoryId = $categories[0]['id'] ?? 1; // Use the first category or fallback to ID 1
    $product = new Product("Smartphone", "Latest model", "BrandX", $categoryId, true);
    $product->save();
    echo "Product 'Smartphone' saved successfully!\n";
} catch (\Exception $e) {
    echo "Error saving product: " . $e->getMessage() . "\n";
}

// Test: Fetch all products
try {
    $products = Product::all();
    echo "Fetched products:\n";
    print_r($products);
} catch (\Exception $e) {
    echo "Error fetching products: " . $e->getMessage() . "\n";
}
