<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;

abstract class Model
{
    protected static string $table;
    protected function getConnection(): \PDO
    {
        return Database::getConnection();
    }
    protected function executeQuery(string $query, array $params = []): bool
    {
        try {
            $db = $this->getConnection();
            $stmt = $db->prepare($query);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }
    protected function fetchColumn(string $query, array $params = []): ?int
    {
        try {
            $db = $this->getConnection();
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $id = $stmt->fetchColumn();
            return $id !== false ? (int)$id : null;
        } catch (\PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }
    protected static function findByField(string $field, string $value): ?array
    {
        try {
            $db = self::getConnection();
            $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE {$field} = :value");
            $stmt->execute(['value' => $value]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }
}
class Category extends Model
{
    protected static string $table = 'categories';
    private string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function save(): void
    {
        $query = "INSERT INTO " . static::$table . " (name) VALUES (:name)";
        $params = ['name' => $this->name];
        $this->executeQuery($query, $params);
    }
    public function getId(): ?int
    {
        $query = "SELECT id FROM " . static::$table . " WHERE name = :name";
        $params = ['name' => $this->name];
        return $this->fetchColumn($query, $params);
    }
    public static function findByName(string $name): ?array
    {
        return self::findByField('name', $name);
    }
}
