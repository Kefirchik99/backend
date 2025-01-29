<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;
use Psr\Log\LoggerInterface;

class ProductResolver
{
    private $categoryResolver;
    private $attributeResolver;
    private $priceResolver;
    private $logger;

    public function __construct(
        CategoryResolver $categoryResolver,
        AttributeResolver $attributeResolver,
        PriceResolver $priceResolver,
        LoggerInterface $logger
    ) {
        $this->categoryResolver = $categoryResolver;
        $this->attributeResolver = $attributeResolver;
        $this->priceResolver = $priceResolver;
        $this->logger = $logger;
    }

    public function resolveAll($category = null)
    {
        $db = Database::getConnection();
        $this->logger->info("Starting resolveAll...");
        try {
            $params = [];
            $query = "SELECT id, name, description, brand, in_stock, category, price FROM products";
            if ($category && strtolower($category) !== 'all') {
                $query .= " WHERE category = :category";
                $params['category'] = $category;
            }
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->logger->info("Products fetched: " . print_r($products, true));
            foreach ($products as &$product) {
                $product['inStock'] = (bool) $product['in_stock'];
                unset($product['in_stock']);
                $product['gallery'] = $this->resolveGallery($product['id']);
                if (empty($product['gallery'])) {
                    $product['gallery'] = ['https://via.placeholder.com/300'];
                }
                $product['attributes'] = $this->resolveAttributes($product['id']);
            }
            return $products;
        } catch (\PDOException $e) {
            $this->logger->error("Database Error: " . $e->getMessage());
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    public function resolveSingleProduct($id)
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare("SELECT id, name, description, brand, in_stock, category, price FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$product) {
                throw new \RuntimeException("Product not found");
            }
            $product['inStock'] = (bool) $product['in_stock'];
            unset($product['in_stock']);
            $product['gallery'] = $this->resolveGallery($id);
            if (empty($product['gallery'])) {
                $product['gallery'] = ['https://via.placeholder.com/300'];
            }
            $product['attributes'] = $this->resolveAttributes($id);
            return $product;
        } catch (\PDOException $e) {
            $this->logger->error("Error fetching product: " . $e->getMessage());
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
            $this->logger->error("Gallery Error for $productId: " . $e->getMessage());
            return [];
        }
    }

    public function resolveAttributes(string $productId): array
    {
        $attributes = $this->attributeResolver->resolveAttributes($productId);
        foreach ($attributes as &$attribute) {
            $attribute['items'] = $this->resolveAttributeItems($productId, $attribute['id']);
        }
        return $attributes;
    }

    public function resolveAttributeItems(string $productId, int $attributeId): array
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare("
                SELECT id, display_value AS displayValue, value 
                FROM attribute_items 
                WHERE attribute_id = :attribute_id
            ");
            $stmt->execute(['attribute_id' => $attributeId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            $this->logger->error("Error fetching attribute items for product ID $productId, attribute ID $attributeId: " . $e->getMessage());
            return [];
        }
    }
}
