<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;
use RuntimeException;

class OrderResolver
{
    public function createOrder(int $productId, int $quantity): string
    {
        if ($quantity <= 0) {
            throw new RuntimeException("Quantity must be greater than zero.");
        }

        $db = Database::getConnection();

        // Fetch product details
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new RuntimeException("Product not found.");
        }

        if (!isset($product['price'])) {
            throw new RuntimeException("Price information is missing for the selected product.");
        }

        $total = $product['price'] * $quantity;

        // Insert order into the database
        $stmt = $db->prepare("INSERT INTO orders (product_id, quantity, total) VALUES (:product_id, :quantity, :total)");
        $stmt->execute([
            'product_id' => $productId,
            'quantity' => $quantity,
            'total' => $total,
        ]);

        return "Order created successfully!";
    }
}
