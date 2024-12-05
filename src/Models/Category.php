<?php

namespace App\Models;

class Category extends Model
{
    public function insert(array $data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->execute(['name' => $data['name']]);
    }
}
