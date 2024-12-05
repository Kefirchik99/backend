<?php

namespace App\Models;

class Product extends Model
{
    public function insert(array $data)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, sku, price, category_id) VALUES (:name, :sku, :price, :category_id)"
        );
        $stmt->execute([
            'name' => $data['name'],
            'sku' => $data['sku'],
            'price' => $data['price'],
            'category_id' => $data['category_id']
        ]);
    }
}
