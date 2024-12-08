<?php

namespace Yaro\EcommerceProject\Utils;

use Yaro\EcommerceProject\Models\Category;
use Yaro\EcommerceProject\Models\Product;
use Yaro\EcommerceProject\Models\TextAttribute;
use Yaro\EcommerceProject\Models\SwatchAttribute;

class DatabaseSeeder
{
    /**
     * Seed the database with the provided data.
     *
     * @param array $data The data to populate into the database.
     * @return void
     * @throws \Exception If seeding fails.
     */
    public static function seed(array $data): void
    {
        // Insert categories
        foreach ($data['categories'] as $categoryData) {
            $category = new Category($categoryData['name']);
            $category->save();
            echo "Category saved: " . $categoryData['name'] . "\n";
        }

        // Insert products
        foreach ($data['products'] as $productData) {
            $categoryId = Category::find($productData['category'])['id'];
            $product = new Product(
                $productData['name'],
                $productData['description'],
                $productData['brand'],
                $categoryId,
                $productData['inStock']
            );
            $product->save();

            echo "Product saved: " . $productData['name'] . "\n";

            // Insert product attributes
            foreach ($productData['attributes'] as $attributeData) {
                $attributeClass = $attributeData['type'] === 'text' ? TextAttribute::class : SwatchAttribute::class;
                $attribute = new $attributeClass($product->getId(), $attributeData['name']);
                $attribute->save();

                echo "Attribute saved: " . $attributeData['name'] . "\n";

                // Insert attribute items
                foreach ($attributeData['items'] as $item) {
                    $attribute->saveItem($item['displayValue'], $item['value']);
                    echo "Attribute item saved: " . $item['displayValue'] . "\n";
                }
            }

            // Insert product gallery images
            foreach ($productData['gallery'] as $imageUrl) {
                $product->saveGalleryImage($imageUrl);
                echo "Gallery image saved: " . $imageUrl . "\n";
            }

            // Insert product prices
            foreach ($productData['prices'] as $price) {
                $product->savePrice($price['currency']['label'], $price['currency']['symbol'], $price['amount']);
                echo "Price saved: " . $price['amount'] . " " . $price['currency']['label'] . "\n";
            }
        }
    }
}
