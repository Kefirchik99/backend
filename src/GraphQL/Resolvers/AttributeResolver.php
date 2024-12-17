<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;

class AttributeResolver
{
    public function resolveAttributes(string $productId)
    {
        try {
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
                return []; // Return an empty array instead of null
            }

            return $attributes;
        } catch (\PDOException $e) {
            error_log("Database error in resolveAttributes for product ID {$productId}: " . $e->getMessage());
            return [];
        }
    }
}
