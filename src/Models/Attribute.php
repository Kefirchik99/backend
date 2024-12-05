<?php

namespace App\Models;

class Attribute extends Model
{
    public function insert(array $data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO attributes (name) VALUES (:name)");
        $stmt->execute(['name' => $data['name']]);
    }
}

