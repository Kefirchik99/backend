<?php

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;
use PDOException;

class SwatchAttribute extends Attribute
{
    /**
     * Save the swatch attribute to the database.
     *
     * @return void
     */
    public function save(): void
    {
        try {
            $db = Database::getConnection();
            $query = "INSERT INTO " . static::$table . " (name, product_id, type) 
                      VALUES (:name, :product_id, 'swatch')";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'name' => $this->name,
                'product_id' => $this->productId,
            ]);
        } catch (PDOException $e) {
            die("Error saving swatch attribute: " . $e->getMessage());
        }
    }
}
