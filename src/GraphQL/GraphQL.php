<?php

namespace Yaro\EcommerceProject\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Yaro\EcommerceProject\Config\Database;
use Throwable;

class GraphQL
{
    public static function handle()
    {
        try {
            // Log request
            file_put_contents('/tmp/graphql.log', "Request received: " . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);

            // Define Category type
            $categoryType = new ObjectType([
                'name' => 'Category',
                'fields' => [
                    'id' => ['type' => Type::nonNull(Type::int())],
                    'name' => ['type' => Type::nonNull(Type::string())],
                ],
            ]);

            // Define Attribute type
            $attributeType = new ObjectType([
                'name' => 'Attribute',
                'fields' => [
                    'name' => ['type' => Type::nonNull(Type::string())],
                    'items' => ['type' => Type::listOf(Type::string())],
                ],
            ]);

            // Define Product type
            $productType = new ObjectType([
                'name' => 'Product',
                'fields' => [
                    'id' => ['type' => Type::nonNull(Type::int())],
                    'name' => ['type' => Type::nonNull(Type::string())],
                    'description' => ['type' => Type::string()],
                    'brand' => ['type' => Type::string()],
                    'price' => ['type' => Type::float()],
                    'category' => [
                        'type' => $categoryType,
                        'resolve' => function ($product) {
                            $db = Database::getConnection();
                            $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
                            $stmt->execute(['id' => $product['category_id']]);
                            return $stmt->fetch();
                        },
                    ],
                    'attributes' => [
                        'type' => Type::listOf($attributeType),
                        'resolve' => function ($product) {
                            $db = Database::getConnection();
                            $textAttributes = $db->prepare("SELECT * FROM text_attributes WHERE product_id = :product_id");
                            $textAttributes->execute(['product_id' => $product['id']]);
                            $swatchAttributes = $db->prepare("SELECT * FROM swatch_attributes WHERE product_id = :product_id");
                            $swatchAttributes->execute(['product_id' => $product['id']]);

                            $attributes = [];
                            foreach ($textAttributes->fetchAll() as $attr) {
                                $items = $db->prepare("SELECT value FROM text_attribute_items WHERE attribute_id = :id");
                                $items->execute(['id' => $attr['id']]);
                                $attributes[] = ['name' => $attr['name'], 'items' => $items->fetchAll(\PDO::FETCH_COLUMN)];
                            }
                            foreach ($swatchAttributes->fetchAll() as $attr) {
                                $items = $db->prepare("SELECT value FROM swatch_attribute_items WHERE attribute_id = :id");
                                $items->execute(['id' => $attr['id']]);
                                $attributes[] = ['name' => $attr['name'], 'items' => $items->fetchAll(\PDO::FETCH_COLUMN)];
                            }

                            return $attributes;
                        },
                    ],
                ],
            ]);

            // Define Query type
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'categories' => [
                        'type' => Type::listOf($categoryType),
                        'resolve' => function () {
                            $db = Database::getConnection();
                            return $db->query("SELECT * FROM categories")->fetchAll();
                        },
                    ],
                    'products' => [
                        'type' => Type::listOf($productType),
                        'resolve' => function () {
                            $db = Database::getConnection();
                            return $db->query("SELECT * FROM products")->fetchAll();
                        },
                    ],
                ],
            ]);

            // Define Mutation type for orders
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createOrder' => [
                        'type' => Type::string(),
                        'args' => [
                            'productId' => ['type' => Type::nonNull(Type::int())],
                            'quantity' => ['type' => Type::nonNull(Type::int())],
                        ],
                        'resolve' => function ($root, $args) {
                            $db = Database::getConnection();
                            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
                            $stmt->execute(['id' => $args['productId']]);
                            $product = $stmt->fetch();

                            if (!$product) {
                                throw new RuntimeException("Product not found");
                            }

                            if (!isset($product['price'])) {
                                throw new RuntimeException("Price information is missing for the selected product.");
                            }

                            $total = $product['price'] * $args['quantity'];
                            $stmt = $db->prepare("INSERT INTO orders (product_id, quantity, total) VALUES (:product_id, :quantity, :total)");
                            $stmt->execute([
                                'product_id' => $args['productId'],
                                'quantity' => $args['quantity'],
                                'total' => $total,
                            ]);

                            return "Order created successfully!";
                        },
                    ],
                ],
            ]);

            // Define schema
            $schema = new Schema(
                (new SchemaConfig())
                    ->setQuery($queryType)
                    ->setMutation($mutationType)
            );

            // Process request
            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);
            $query = $input['query'];
            $variableValues = $input['variables'] ?? null;

            $result = GraphQLBase::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray();

        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
            file_put_contents('/tmp/graphql.log', "Error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($output);
    }
}
