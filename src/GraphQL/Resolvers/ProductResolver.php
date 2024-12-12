<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;

class ProductResolver
{
    private $categoryResolver;
    private $attributeResolver;
    private $priceResolver;

    public function __construct(CategoryResolver $categoryResolver, AttributeResolver $attributeResolver, PriceResolver $priceResolver)
    {
        $this->categoryResolver = $categoryResolver;
        $this->attributeResolver = $attributeResolver;
        $this->priceResolver = $priceResolver;
    }

    public function resolveAll()
    {
        $db = Database::getConnection();
        $products = $db->query("SELECT * FROM products")->fetchAll();

        foreach ($products as &$product) {
            $product['category'] = $this->categoryResolver->resolveById($product['category_id']);
            $product['attributes'] = $this->attributeResolver->resolveAttributes($product['id']);
        }

        return $products;
    }

    public function resolveGallery(int $productId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT image_url FROM gallery WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }
}
