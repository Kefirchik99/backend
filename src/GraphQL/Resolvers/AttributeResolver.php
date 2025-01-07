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
                SELECT id, name, type 
                FROM attributes 
                WHERE product_id = :product_id
            ");
            $stmt->execute(['product_id' => $productId]);
            $attributes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($attributes as &$attr) {
                $attr['items'] = $this->resolveItemsForAttribute($attr['id']);
            }

            return $attributes ?: [];
        } catch (\PDOException $e) {
            error_log("Database error in resolveAttributes for product ID {$productId}: " . $e->getMessage());
            return [];
        }
    }

    public function resolveItemsForAttribute(int $attributeId)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT id, display_value AS displayValue, value
                FROM attribute_items
                WHERE attribute_id = :attribute_id
            ");
            $stmt->execute(['attribute_id' => $attributeId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Database error in resolveItemsForAttribute for attribute ID {$attributeId}: " . $e->getMessage());
            return [];
        }
    }
}
