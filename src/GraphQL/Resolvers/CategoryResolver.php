<?php

namespace Yaro\EcommerceProject\GraphQL\Resolvers;

use Yaro\EcommerceProject\Config\Database;

class CategoryResolver
{
    public function resolveAll()
    {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM categories")->fetchAll();
    }

    public function resolveById(int $id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
