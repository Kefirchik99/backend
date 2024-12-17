<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;

class ProductResolver
{
    private $attributeResolver;
    private $categoryResolver;
    private $priceResolver;

    public function __construct(CategoryResolver $categoryResolver, AttributeResolver $attributeResolver, PriceResolver $priceResolver)
    {
        $this->categoryResolver = $categoryResolver;
        $this->attributeResolver = $attributeResolver;
        $this->priceResolver = $priceResolver;
    }

    public function resolveAll($category = null)
    {
        $db = Database::getConnection();
        file_put_contents('/tmp/graphql.log', "Starting resolveAll...\n", FILE_APPEND);

        try {
            $params = [];
            $query = "SELECT id, name, description, brand, in_stock, category, price FROM products";

            if ($category  && $category !== '1') {
                $query .= " WHERE category = :category";
                $params['category'] = $category;
            }

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Debugging logs
            file_put_contents('/tmp/graphql.log', "Products fetched: " . print_r($products, true), FILE_APPEND);

            foreach ($products as &$product) {
                $product['inStock'] = (bool) $product['in_stock'];
                unset($product['in_stock']);

                // Debugging gallery and attributes resolution
                file_put_contents('/tmp/graphql.log', "Resolving gallery for product ID: {$product['id']}\n", FILE_APPEND);
                $product['gallery'] = $this->resolveGallery($product['id']);

                file_put_contents('/tmp/graphql.log', "Resolving attributes for product ID: {$product['id']}\n", FILE_APPEND);
                $product['attributes'] = $this->resolveAttributes($product['id']);
            }

            return $products;
        } catch (\PDOException $e) {
            file_put_contents('/tmp/graphql.log', "Database Error: " . $e->getMessage() . "\n", FILE_APPEND);
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }


    public function resolveGallery(string $productId): array
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare("SELECT image_url FROM gallery WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $productId]);
            return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        } catch (\PDOException $e) {
            file_put_contents('/tmp/graphql.log', "Gallery Error for $productId: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    public function resolveAttributes(string $productId): array
    {
        return $this->attributeResolver->resolveAttributes($productId);
    }
}
