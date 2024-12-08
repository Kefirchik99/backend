<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;
use PDOException;

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

    abstract public function save(): void;

    public function getId(): ?int
    {
        return $this->fetchColumn("
            SELECT id FROM " . static::$table . " WHERE name = :name AND product_id = :productId
        ", [
            'name' => $this->name,
            'productId' => $this->productId,
        ]);
    }

    public function saveItem(string $displayValue, string $value): void
    {
        $this->executeQuery("
            INSERT INTO attribute_items (attribute_id, display_value, value)
            VALUES (:attribute_id, :display_value, :value)
        ", [
            'attribute_id' => $this->getId(),
            'display_value' => $displayValue,
            'value' => $value,
        ]);
    }
}
