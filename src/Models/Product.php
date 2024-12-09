<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;
use PDOException;

class Product extends Model
{
    protected static string $table = 'products';

    private string $name;
    private string $description;
    private string $brand;
    private int $categoryId;
    private bool $inStock;

    public function __construct(string $name, string $description, string $brand, int $categoryId, bool $inStock)
    {
        $this->name = $name;
        $this->description = $description;
        $this->brand = $brand;
        $this->categoryId = $categoryId;
        $this->inStock = $inStock;
    }

    public function save(): void
    {
        $db = Database::getConnection();
        $query = "INSERT INTO " . static::$table . " (name, description, brand, category_id, in_stock)
                  VALUES (:name, :description, :brand, :category_id, :in_stock)
                  ON DUPLICATE KEY UPDATE description = :description, brand = :brand, in_stock = :in_stock";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'category_id' => $this->categoryId,
            'in_stock' => $this->inStock ? 1 : 0,
        ]);
    }

    public function getId(): ?int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM " . static::$table . " WHERE name = :name AND category_id = :category_id");
        $stmt->execute([
            'name' => $this->name,
            'category_id' => $this->categoryId,
        ]);

        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

    public function saveGalleryImage(string $imageUrl): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO gallery (product_id, image_url) VALUES (:product_id, :image_url)");
        $stmt->execute(['product_id' => $this->getId(), 'image_url' => $imageUrl]);
    }

    public function savePrice(string $currency, string $symbol, float $amount): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO prices (product_id, currency, symbol, amount)
                              VALUES (:product_id, :currency, :symbol, :amount)");
        $stmt->execute([
            'product_id' => $this->getId(),
            'currency' => $currency,
            'symbol' => $symbol,
            'amount' => $amount,
        ]);
    }
}
