<?php

namespace Yaro\EcommerceProject\GraphQL;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Yaro\EcommerceProject\GraphQL\Resolvers\ProductResolver;
use Yaro\EcommerceProject\GraphQL\Resolvers\CategoryResolver;
use Yaro\EcommerceProject\GraphQL\Resolvers\AttributeResolver;
use Yaro\EcommerceProject\GraphQL\Resolvers\PriceResolver;
use Yaro\EcommerceProject\GraphQL\Resolvers\OrderResolver;
use Throwable;

class GraphQL
{
    public static function handle()
    {
        // --- CORS Headers Start ---
        header("Access-Control-Allow-Origin: http://localhost:5173"); // Replace with your frontend origin
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Max-Age: 86400");

        // Handle preflight requests (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
        // --- CORS Headers End ---

        try {
            // Log request
            file_put_contents('/tmp/graphql.log', "Request received: " . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);

            // Create resolvers
            $categoryResolver = new CategoryResolver();
            $attributeResolver = new AttributeResolver();
            $priceResolver = new PriceResolver();
            $orderResolver = new OrderResolver();
            $productResolver = new ProductResolver($categoryResolver, $attributeResolver, $priceResolver);

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
                    'value' => ['type' => Type::string()],
                    'type' => ['type' => Type::string()],
                    'items' => ['type' => Type::listOf(Type::string())],
                ],
            ]);

            // Define Product type
            // Define Product type
            $ProductType = new ObjectType([
                'name' => 'Product',
                'fields' => [
                    'id' => ['type' => Type::nonNull(Type::string())],
                    'name' => ['type' => Type::nonNull(Type::string())],
                    'description' => ['type' => Type::string()],
                    'brand' => ['type' => Type::string()],
                    'inStock' => ['type' => Type::nonNull(Type::boolean())],
                    'category' => ['type' => Type::string(), 'description' => 'The product category'],
                    'price' => ['type' => Type::float(), 'description' => 'The product price'],
                    'gallery' => [
                        'type' => Type::listOf(Type::string()),
                        'resolve' => fn($product) => $productResolver->resolveGallery($product['id']),
                    ],
                    'attributes' => [
                        'type' => Type::listOf($attributeType),
                        'resolve' => fn($product) => $attributeResolver->resolveAttributes($product['id']),
                    ],
                ],
            ]);



            // Define Query type
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'categories' => [
                        'type' => Type::listOf($categoryType),
                        'resolve' => fn() => $categoryResolver->resolveAll(),
                    ],
                    'products' => [
                        'type' => Type::listOf($ProductType),
                        'args' => [
                            'category' => ['type' => Type::string()],
                        ],
                        'resolve' => fn($root, $args) => $productResolver->resolveAll($args['category'] ?? null),
                    ],
                ],
            ]);

            // Define Mutation type
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createOrder' => [
                        'type' => Type::string(),
                        'args' => [
                            'productId' => ['type' => Type::nonNull(Type::int())],
                            'quantity' => ['type' => Type::nonNull(Type::int())],
                        ],
                        'resolve' => fn($root, $args) => $orderResolver->createOrder($args['productId'], $args['quantity']),
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
                throw new \RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);

            if (!$input || !isset($input['query'])) {
                throw new \RuntimeException('Invalid GraphQL request: Missing query.');
            }

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
