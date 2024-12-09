<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;

abstract class Model
{
    protected static string $table;

    public static function find(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
