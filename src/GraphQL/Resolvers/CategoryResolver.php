<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;
use Psr\Log\LoggerInterface;

class CategoryResolver
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function resolveAll()
    {
        $db = Database::getConnection();
        try {
            $result = $db->query("SELECT * FROM categories")->fetchAll();
            $this->logger->info("Categories fetched: " . print_r($result, true));
            return $result;
        } catch (\PDOException $e) {
            $this->logger->error("Error fetching categories: " . $e->getMessage());
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    public function resolveById(int $id)
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            if (!$result) {
                throw new \RuntimeException("Category not found");
            }
            $this->logger->info("Category fetched for ID $id: " . print_r($result, true));
            return $result;
        } catch (\PDOException $e) {
            $this->logger->error("Error fetching category with ID $id: " . $e->getMessage());
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }
}
