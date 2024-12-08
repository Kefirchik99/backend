<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;
use PDOException;

class Category extends Model
{
    protected static string $table = 'categories';

    private string $name;

    /**
     * Category constructor.
     *
     * @param string $name The name of the category.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Save the category to the database.
     *
     * @return void
     */
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

    /**
     * Retrieve the ID of the category.
     *
     * @return int|null The ID of the category, or null if not found.
     */
    public function getId(): ?int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM " . static::$table . " WHERE name = :name");
        $stmt->execute(['name' => $this->name]);

        $id = $stmt->fetchColumn();

        return $id !== false ? (int)$id : null;
    }
}
