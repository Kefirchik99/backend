<?php

declare(strict_types=1);

namespace Yaro\EcommerceProject\Models;

use Yaro\EcommerceProject\Config\Database;
use Psr\Log\LoggerInterface;

abstract class Model
{
    protected static string $table;
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function find(int $id, LoggerInterface $logger): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $logger->info("Record found in table " . static::$table . " with ID {$id}");
            } else {
                $logger->info("No record found in table " . static::$table . " with ID {$id}");
            }
            return $result ?: null;
        } catch (\PDOException $e) {
            $logger->error("Database error finding record in table " . static::$table . ": " . $e->getMessage());
            return null;
        }
    }
}
