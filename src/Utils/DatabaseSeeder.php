<?php

namespace Yaro\EcommerceProject\Utils;

use Yaro\EcommerceProject\Models\Category;
use Yaro\EcommerceProject\Models\Product;
use Yaro\EcommerceProject\Models\TextAttribute;
use Yaro\EcommerceProject\Models\SwatchAttribute;

class DatabaseSeeder
{
    public static function seed(array $data): void
    {
        if (!isset($data['categories']) || !isset($data['products'])) {
            throw new \Exception("Invalid JSON structure. Expected 'categories' and 'products' keys.");
        }

        // Insert categories
        foreach ($data['categories'] as $categoryData) {
            if (Category::findByName($categoryData['name'])) {
                echo "Category already exists: " . $categoryData['name'] . "\n";
                continue;
            }
            $category = new Category($categoryData['name']);
            $category->save();
            echo "Category saved: " . $categoryData['name'] . "\n";
        }

        // Insert products
        foreach ($data['products'] as $productData) {
            $category = Category::findByName($productData['category']);
            if (!$category) {
                throw new \Exception("Category not found: " . $productData['category']);
            }
            $categoryId = $category['id'];

            $product = new Product(
                $productData['name'],
                $productData['description'] ?? '',
                $productData['brand'] ?? '',
                $categoryId,
                $productData['inStock']
            );
            $product->save();
            echo "Product saved: " . $productData['name'] . "\n";

            // Insert attributes
            foreach ($productData['attributes'] as $attributeData) {
                if ($attributeData['type'] === 'text') {
                    self::saveTextAttribute($product, $attributeData);
                } elseif ($attributeData['type'] === 'swatch') {
                    self::saveSwatchAttribute($product, $attributeData);
                }
            }

            // Insert gallery
            foreach ($productData['gallery'] as $imageUrl) {
                $product->saveGalleryImage($imageUrl);
                echo "Gallery image saved: " . $imageUrl . "\n";
            }

            // Insert prices
            foreach ($productData['prices'] as $price) {
                $product->savePrice(
                    $price['currency']['label'],
                    $price['currency']['symbol'],
                    $price['amount']
                );
                echo "Price saved: " . $price['amount'] . " " . $price['currency']['label'] . "\n";
            }
        }
    }

    private static function saveTextAttribute(Product $product, array $attributeData): void
    {
        $attribute = new TextAttribute($attributeData['name'], $product->getId());
        $attribute->save();
        echo "Text attribute saved: " . $attributeData['name'] . "\n";

        foreach ($attributeData['items'] as $item) {
            $attribute->saveItem($item['displayValue'], $item['value']);
            echo "Text attribute item saved: " . $item['displayValue'] . "\n";
        }
    }

    private static function saveSwatchAttribute(Product $product, array $attributeData): void
    {
        $attribute = new SwatchAttribute($attributeData['name'], $product->getId());
        $attribute->save();
        echo "Swatch attribute saved: " . $attributeData['name'] . "\n";

        foreach ($attributeData['items'] as $item) {
            $attribute->saveItem($item['displayValue'], $item['value']);
            echo "Swatch attribute item saved: " . $item['displayValue'] . "\n";
        }
    }
}
