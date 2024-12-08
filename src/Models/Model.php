<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;
use PDO;

abstract class Model
{
    protected static string $table;

    /**
     * Fetch all records from the table.
     *
     * @return array
     */
    public static function all(): array
    {
        $db = Database::getConnection();
        $query = "SELECT * FROM " . static::$table;
        $stmt = $db->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a single record by ID.
     *
     * @param int $id
     * @return array|null
     */
    public static function find(int $id): ?array
    {
        $db = Database::getConnection();
        $query = "SELECT * FROM " . static::$table . " WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Save the current instance to the database.
     * Subclasses must implement this method.
     */
    abstract public function save(): void;
}
