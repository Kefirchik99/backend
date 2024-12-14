<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;

class AttributeResolver
{
    public function resolveAttributes(int $productId)
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT name, value, type 
            FROM attributes 
            WHERE product_id = :product_id
        ");
        $stmt->execute(['product_id' => $productId]);
        $attributes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$attributes) {
            error_log("No attributes found for product ID: {$productId}");
        }

        return $attributes;
    }
}
