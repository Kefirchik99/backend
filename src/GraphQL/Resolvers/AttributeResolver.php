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
            SELECT id, name, value, type
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

    public function resolveItemsForAttribute(array $attributeRow): array
    {
        // 'type' will be text or swatch; if you have separate tables, do an if/else.
        // For demonstration, let's assume standard text attributes go to attribute_items.
        try {
            if (!isset($attributeRow['id'])) {
                return [];
            }
            $attributeId = $attributeRow['id'];

            $db = Database::getConnection();

            if ($attributeRow['type'] === 'swatch') {
                // If you keep separate tables for swatches
                $stmt = $db->prepare("
                    SELECT id, display_value, value
                    FROM swatch_attribute_items
                    WHERE attribute_id = :attribute_id
                ");
            } else {
                // For normal text attributes
                $stmt = $db->prepare("
                    SELECT id, display_value, value
                    FROM attribute_items
                    WHERE attribute_id = :attribute_id
                ");
            }

            $stmt->execute(['attribute_id' => $attributeId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Return them as an array of shape: { id, displayValue, value }
            return array_map(function ($row) {
                return [
                    'id'            => $row['id'],
                    'displayValue'  => $row['display_value'],
                    'value'         => $row['value'],
                ];
            }, $rows);
        } catch (\PDOException $e) {
            error_log("Database error in resolveItemsForAttribute: " . $e->getMessage());
            return [];
        }
    }
}
