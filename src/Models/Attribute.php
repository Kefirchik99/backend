<?php

namespace Yaro\EcommerceProject\Models;

abstract class Attribute extends Model
{
    protected static string $table = 'attributes';

    protected string $name;
    protected int $productId;

    public function __construct(string $name, int $productId)
    {
        $this->name = $name;
        $this->productId = $productId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    abstract public function save(): void;

    public function getId(): ?int
    {
        $query = "SELECT id FROM " . static::$table . " WHERE name = :name AND product_id = :productId";
        $params = [
            'name' => $this->name,
            'productId' => $this->productId,
        ];
        return $this->fetchColumn($query, $params);
    }

    public function saveItem(string $displayValue, string $value): void
    {
        $query = "INSERT INTO attribute_items (attribute_id, display_value, value)
                  VALUES (:attribute_id, :display_value, :value)";
        $params = [
            'attribute_id' => $this->getId(),
            'display_value' => $displayValue,
            'value' => $value,
        ];
        $this->executeQuery($query, $params);
    }
}
