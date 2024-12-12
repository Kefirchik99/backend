<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;

class PriceResolver
{
    public function resolvePrices(int $productId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT currency, symbol, amount FROM prices WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

