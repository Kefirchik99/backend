<?php

namespace Yaro\EcommerceProject\Utils;

use Yaro\EcommerceProject\Config\Database;

class DatabaseSeeder
{
    public static function seed(array $data): void
    {
        $db = Database::getConnection();
        echo "Starting database seeding...\n";

        // Handle categories
        if (!empty($data['categories'])) {
            echo "Processing categories...\n";
            foreach ($data['categories'] as $category) {
                try {
                    if (empty($category['name'])) {
                        echo "Skipping category: Missing name.\n";
                        continue;
                    }

                    $stmt = $db->prepare("INSERT IGNORE INTO categories (name) VALUES (:name)");
                    $stmt->execute(['name' => $category['name']]);
                    echo "Inserted category: {$category['name']}\n";
                } catch (\PDOException $e) {
                    echo "Error inserting category: {$e->getMessage()}\n";
                }
            }
        } else {
            echo "No categories found in data.\n";
        }

        // Handle products
        if (!empty($data['products'])) {
            echo "Processing products...\n";
            foreach ($data['products'] as $product) {
                try {
                    if (empty($product['id']) || empty($product['name']) || empty($product['category'])) {
                        echo "Skipping product: Missing ID, name, or category.\n";
                        continue;
                    }

                    // Fetch category_id dynamically
                    $stmtCategory = $db->prepare("SELECT id FROM categories WHERE name = :name");
                    $stmtCategory->execute(['name' => $product['category']]);
                    $categoryId = $stmtCategory->fetchColumn();

                    if (!$categoryId) {
                        echo "Error: Category '{$product['category']}' not found for product ID: {$product['id']}\n";
                        continue;
                    }

                    // Insert product with category_id
                    $stmt = $db->prepare("
                    INSERT INTO products (id, name, description, brand, category_id, category, price, in_stock)
                    VALUES (:id, :name, :description, :brand, :category_id, :category, :price, :in_stock)
                    ON DUPLICATE KEY UPDATE
                        name = VALUES(name),
                        description = VALUES(description),
                        brand = VALUES(brand),
                        category_id = VALUES(category_id),
                        category = VALUES(category),
                        price = VALUES(price),
                        in_stock = VALUES(in_stock)
                ");
                    $stmt->execute([
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'description' => $product['description'] ?? '',
                        'brand' => $product['brand'] ?? null,
                        'category_id' => $categoryId,
                        'category' => $product['category'],
                        'price' => $product['prices'][0]['amount'] ?? null,
                        'in_stock' => $product['in_stock'] ?? 1, // Default to 1 if not provided
                    ]);


                    echo "Inserted product: {$product['name']} (ID: {$product['id']}, Category ID: {$categoryId})\n";

                    // Insert gallery
                    if (!empty($product['gallery'])) {
                        foreach ($product['gallery'] as $imageUrl) {
                            if (empty($imageUrl)) continue;
                            $galleryStmt = $db->prepare("
                                INSERT IGNORE INTO gallery (product_id, image_url)
                                VALUES (:product_id, :image_url)
                            ");
                            $galleryStmt->execute([
                                'product_id' => $product['id'],
                                'image_url' => $imageUrl,
                            ]);
                            echo "Inserted gallery image for product ID: {$product['id']}\n";
                        }
                    }

                    // Insert attributes
                    if (!empty($product['attributes'])) {
                        foreach ($product['attributes'] as $attribute) {
                            if (empty($attribute['name']) || empty($attribute['items'])) continue;

                            foreach ($attribute['items'] as $item) {
                                if (empty($item['value'])) continue;
                                $attributeStmt = $db->prepare("
                                    INSERT IGNORE INTO attributes (product_id, name, value, type)
                                    VALUES (:product_id, :name, :value, :type)
                                ");
                                $attributeStmt->execute([
                                    'product_id' => $product['id'],
                                    'name' => $attribute['name'],
                                    'value' => $item['value'],
                                    'type' => $attribute['type'] ?? null,
                                ]);
                                echo "Inserted attribute: {$attribute['name']} -> {$item['value']} for product ID: {$product['id']}\n";
                            }
                        }
                    } else {
                        echo "No attributes found for product ID: {$product['id']}\n";
                    }
                } catch (\PDOException $e) {
                    echo "Error inserting product ID {$product['id']}: {$e->getMessage()}\n";
                }
            }
        } else {
            echo "No products found in data.\n";
        }

        echo "Database seeding completed successfully.\n";
    }
}
