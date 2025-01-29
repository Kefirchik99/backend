<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;
use Psr\Log\LoggerInterface;
use RuntimeException;

class OrderResolver
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    // CHANGED HERE: productId is now a string, not int
    public function createOrder(string $productId, int $quantity): string
    {
        if ($quantity <= 0) {
            throw new RuntimeException("Quantity must be greater than zero.");
        }

        $db = Database::getConnection();

        // Use productId as a string in your query
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch();

        if (!$product) {
            $this->logger->error("Product not found for ID {$productId}");
            throw new RuntimeException("Product not found.");
        }

        if (!isset($product['price'])) {
            $this->logger->error("Price information is missing for product ID {$productId}");
            throw new RuntimeException("Price information is missing for the selected product.");
        }

        $total = $product['price'] * $quantity;

        // Insert order into the database
        $stmt = $db->prepare("
            INSERT INTO orders (product_id, quantity, total)
            VALUES (:product_id, :quantity, :total)
        ");
        $stmt->execute([
            'product_id' => $productId, // store string-based product ID
            'quantity'   => $quantity,
            'total'      => $total,
        ]);

        $this->logger->info(
            "Order created successfully for product ID {$productId} with quantity {$quantity} and total {$total}"
        );

        return "Order created successfully!";
    }
}
