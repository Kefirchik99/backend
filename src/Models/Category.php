<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;
use PDOException;

class Category extends Model
{
    protected static string $table = 'categories';

    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function save(): void
    {
        try {
            $db = Database::getConnection();
            $query = "INSERT INTO " . static::$table . " (name) VALUES (:name)";
            $stmt = $db->prepare($query);
            $stmt->execute(['name' => $this->name]);
        } catch (PDOException $e) {
            die("Error saving category: " . $e->getMessage());
        }
    }

    public function getId(): ?int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM " . static::$table . " WHERE name = :name");
        $stmt->execute(['name' => $this->name]);

        $id = $stmt->fetchColumn();

        return $id !== false ? (int)$id : null;
    }

    public static function findByName(string $name): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE name = :name");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
