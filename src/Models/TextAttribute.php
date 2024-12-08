<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;
use PDOException;

class TextAttribute extends Attribute
{
    /**
     * Save the text attribute to the database.
     *
     * @return void
     */
    public function save(): void
    {
        try {
            $db = Database::getConnection();
            $query = "INSERT INTO " . static::$table . " (name, product_id, type) 
                      VALUES (:name, :product_id, 'text')";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'name' => $this->name,
                'product_id' => $this->productId,
            ]);
        } catch (PDOException $e) {
            die("Error saving text attribute: " . $e->getMessage());
        }
    }
}
