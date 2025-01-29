<?php

declare(strict_types=1);

namespace Yaro\EcommerceProject\Utils;

use Yaro\EcommerceProject\Config\Database;
use Psr\Log\LoggerInterface;

class DatabaseSeeder
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function seed(array $data): void
    {
        $db = Database::getConnection();
        $this->logger->info("Starting database seeding...");

        // Handle categories
        if (!empty($data['categories'])) {
            $this->logger->info("Processing categories...");
            foreach ($data['categories'] as $category) {
                try {
                    if (empty($category['name'])) {
                        $this->logger->warning("Skipping category: Missing name.");
                        continue;
                    }
                    $stmt = $db->prepare("INSERT IGNORE INTO categories (name) VALUES (:name)");
                    $stmt->execute(['name' => $category['name']]);
                    $this->logger->info("Inserted category: {$category['name']}");
                } catch (\PDOException $e) {
                    $this->logger->error("Error inserting category: {$e->getMessage()}");
                }
            }
        } else {
            $this->logger->info("No categories found in data.");
        }

        // Handle products
        if (!empty($data['products'])) {
            $this->logger->info("Processing products...");
            foreach ($data['products'] as $product) {
                try {
                    if (empty($product['id']) || empty($product['name']) || empty($product['category'])) {
                        $this->logger->warning("Skipping product: Missing ID, name, or category.");
                        continue;
                    }
                    // Fetch category_id dynamically
                    $stmtCategory = $db->prepare("SELECT id FROM categories WHERE name = :name");
                    $stmtCategory->execute(['name' => $product['category']]);
                    $categoryId = $stmtCategory->fetchColumn();
                    if (!$categoryId) {
                        $this->logger->error("Error: Category '{$product['category']}' not found for product ID: {$product['id']}");
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
                        'in_stock' => isset($product['inStock']) ? (int) $product['inStock'] : 1,
                    ]);
                    $this->logger->info("Inserted product: {$product['name']} (ID: {$product['id']}, Category ID: {$categoryId})");

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
                            $this->logger->info("Inserted gallery image for product ID: {$product['id']}");
                        }
                    }

                    // Insert attributes with attribute items
                    if (!empty($product['attributes'])) {
                        foreach ($product['attributes'] as $attribute) {
                            if (empty($attribute['name']) || empty($attribute['items'])) continue;
                            // Insert top-level attribute
                            $attributeStmt = $db->prepare("
                                INSERT IGNORE INTO attributes (product_id, name, type)
                                VALUES (:product_id, :name, :type)
                            ");
                            $attributeStmt->execute([
                                'product_id' => $product['id'],
                                'name' => $attribute['name'],
                                'type' => $attribute['type'] ?? null,
                            ]);
                            $attributeId = $db->lastInsertId();
                            if (!$attributeId) {
                                // Fetch existing attribute ID if not inserted
                                $fetchStmt = $db->prepare("
                                    SELECT id FROM attributes WHERE product_id = :product_id AND name = :name
                                ");
                                $fetchStmt->execute([
                                    'product_id' => $product['id'],
                                    'name' => $attribute['name'],
                                ]);
                                $attributeId = $fetchStmt->fetchColumn();
                            }
                            if (!$attributeId) {
                                $this->logger->error("Error: Unable to find attribute ID for '{$attribute['name']}' on product ID: {$product['id']}");
                                continue;
                            }
                            // Insert attribute items
                            $itemStmt = $db->prepare("
                                INSERT INTO attribute_items (attribute_id, display_value, value)
                                VALUES (:attribute_id, :display_value, :value)
                            ");
                            foreach ($attribute['items'] as $item) {
                                if (empty($item['value'])) continue;
                                $itemStmt->execute([
                                    'attribute_id' => $attributeId,
                                    'display_value' => $item['displayValue'] ?? $item['value'],
                                    'value' => $item['value'],
                                ]);
                                $this->logger->info("Inserted attribute item: {$attribute['name']} -> {$item['value']} for product ID: {$product['id']}");
                            }
                        }
                    } else {
                        $this->logger->info("No attributes found for product ID: {$product['id']}");
                    }
                } catch (\PDOException $e) {
                    $this->logger->error("Error inserting product ID {$product['id']}: {$e->getMessage()}");
                }
            }
        } else {
            $this->logger->info("No products found in data.");
        }
        $this->logger->info("Database seeding completed successfully.");
    }
}
