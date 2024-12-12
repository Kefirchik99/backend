<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;

class ElectronicsProduct extends Product
{
    protected static string $table = 'electronics_products';

    private array $galleryImages = [];
    private array $prices = [];

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

    public function addGalleryImage(string $imageUrl): void
    {
        $this->galleryImages[] = $imageUrl;
    }

    public function saveGallery(): void
    {
        $db = Database::getConnection();
        foreach ($this->galleryImages as $imageUrl) {
            $stmt = $db->prepare("INSERT INTO gallery (product_id, image_url) VALUES (:product_id, :image_url)");
            $stmt->execute([
                'product_id' => $this->getId(),
                'image_url' => $imageUrl,
            ]);
        }
    }

    public function addPrice(string $currency, string $symbol, float $amount): void
    {
        $this->prices[] = [
            'currency' => $currency,
            'symbol' => $symbol,
            'amount' => $amount,
        ];
    }

    public function savePrices(): void
    {
        $db = Database::getConnection();
        foreach ($this->prices as $price) {
            $stmt = $db->prepare("INSERT INTO prices (product_id, currency, symbol, amount)
                                  VALUES (:product_id, :currency, :symbol, :amount)");
            $stmt->execute([
                'product_id' => $this->getId(),
                'currency' => $price['currency'],
                'symbol' => $price['symbol'],
                'amount' => $price['amount'],
            ]);
        }
    }

    public function format(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'category' => 'Electronics',
            'inStock' => $this->inStock,
        ];
    }
}
