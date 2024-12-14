<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;

class AttributeResolver
{
    public function resolveAttributes(int $productId)
    {
        $db = Database::getConnection();

        $textAttributes = $db->prepare("SELECT * FROM text_attributes WHERE product_id = :product_id");
        $textAttributes->execute(['product_id' => $productId]);

        $swatchAttributes = $db->prepare("SELECT * FROM swatch_attributes WHERE product_id = :product_id");
        $swatchAttributes->execute(['product_id' => $productId]);

        $attributes = [];
        foreach ($textAttributes->fetchAll() as $attr) {
            $items = $db->prepare("SELECT value FROM text_attribute_items WHERE attribute_id = :id");
            $items->execute(['id' => $attr['id']]);
            $attributes[] = ['name' => $attr['name'], 'items' => $items->fetchAll(\PDO::FETCH_COLUMN)];
        }
        foreach ($swatchAttributes->fetchAll() as $attr) {
            $items = $db->prepare("SELECT value FROM swatch_attribute_items WHERE attribute_id = :id");
            $items->execute(['id' => $attr['id']]);
            $attributes[] = ['name' => $attr['name'], 'items' => $items->fetchAll(\PDO::FETCH_COLUMN)];
        }

        return $attributes;
    }
}

