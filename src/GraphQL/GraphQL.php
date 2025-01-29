<?php

namespace Yaro\EcommerceProject\GraphQL;

use Psr\Log\LoggerInterface;
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
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function handle($vars)
    {
        $logger = $GLOBALS['logger'] ?? null;
        if ($logger) {
            $graphqlInstance = new self($logger);
            return $graphqlInstance->execute();
        } else {
            throw new \RuntimeException("Logger not initialized.");
        }
    }

    private function execute()
    {
        // CORS Headers
        header("Access-Control-Allow-Origin: http://localhost:5173");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Max-Age: 86400");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        try {
            $this->logger->info("Request received: " . date('Y-m-d H:i:s'));

            // Instantiate resolvers
            $categoryResolver   = new CategoryResolver($this->logger);
            $attributeResolver  = new AttributeResolver($this->logger);
            $priceResolver      = new PriceResolver();
            $orderResolver      = new OrderResolver($this->logger);
            $productResolver    = new ProductResolver(
                $categoryResolver,
                $attributeResolver,
                $priceResolver,
                $this->logger
            );

            // 1) AttributeItem Type
            $attributeItemType = new ObjectType([
                'name' => 'AttributeItem',
                'fields' => [
                    'id'           => ['type' => Type::nonNull(Type::string())],
                    'displayValue' => ['type' => Type::nonNull(Type::string())],
                    'value'        => ['type' => Type::nonNull(Type::string())],
                ],
            ]);

            // 2) Attribute Type
            $attributeType = new ObjectType([
                'name' => 'Attribute',
                'fields' => function () use ($attributeItemType, $attributeResolver) {
                    return [
                        'id'   => ['type' => Type::nonNull(Type::int())],
                        'name' => ['type' => Type::nonNull(Type::string())],
                        'type' => ['type' => Type::string()],
                        'items' => [
                            'type' => Type::listOf($attributeItemType),
                            'resolve' => function ($attribute) use ($attributeResolver) {
                                return $attributeResolver->resolveItemsForAttribute($attribute['id']);
                            },
                        ],
                    ];
                },
            ]);

            // 3) Category Type
            $categoryType = new ObjectType([
                'name' => 'Category',
                'fields' => [
                    'id'   => ['type' => Type::nonNull(Type::int())],
                    'name' => ['type' => Type::nonNull(Type::string())],
                ],
            ]);

            // 4) Product Type
            $ProductType = new ObjectType([
                'name' => 'Product',
                'fields' => [
                    'id'          => ['type' => Type::nonNull(Type::string())],
                    'name'        => ['type' => Type::nonNull(Type::string())],
                    'description' => ['type' => Type::string()],
                    'brand'       => ['type' => Type::string()],
                    'inStock'     => ['type' => Type::nonNull(Type::boolean())],
                    'category'    => ['type' => Type::string()],
                    'price'       => ['type' => Type::float()],
                    'gallery'     => [
                        'type' => Type::listOf(Type::string()),
                        'resolve' => function ($product) use ($productResolver) {
                            $gallery = $productResolver->resolveGallery($product['id']);
                            return !empty($gallery) ? $gallery : ['https://via.placeholder.com/300'];
                        },
                    ],
                    'attributes'  => [
                        'type' => Type::listOf($attributeType),
                        'resolve' => function ($product) use ($attributeResolver) {
                            return $attributeResolver->resolveAttributes($product['id']);
                        },
                    ],
                ],
            ]);

            // 5) Query Type
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'categories' => [
                        'type' => Type::listOf($categoryType),
                        'resolve' => function () use ($categoryResolver) {
                            return $categoryResolver->resolveAll();
                        },
                    ],
                    'products' => [
                        'type' => Type::listOf($ProductType),
                        'args' => [
                            'category' => ['type' => Type::string()],
                        ],
                        'resolve' => function ($_, $args) use ($productResolver) {
                            return $productResolver->resolveAll($args['category'] ?? null);
                        },
                    ],
                    'product' => [
                        'type' => $ProductType,
                        'args' => [
                            'id' => ['type' => Type::nonNull(Type::id())],
                        ],
                        'resolve' => function ($_, $args) use ($productResolver) {
                            return $productResolver->resolveSingleProduct($args['id']);
                        },
                    ],
                ],
            ]);

            // 6) Mutation Type
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createOrder' => [
                        'type' => Type::string(),
                        'args' => [
                            // CHANGED: Expect a string/ID instead of an int
                            'productId' => ['type' => Type::nonNull(Type::id())],
                            'quantity'  => ['type' => Type::nonNull(Type::int())],
                        ],
                        'resolve' => function ($_, $args) use ($orderResolver) {
                            // If your orderResolver expects a string, make sure it handles it properly
                            return $orderResolver->createOrder($args['productId'], $args['quantity']);
                        },
                    ],
                ],
            ]);

            // 7) Final Schema
            $schema = new Schema(
                (new SchemaConfig())
                    ->setQuery($queryType)
                    ->setMutation($mutationType)
            );

            // 8) Process incoming GraphQL request
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
            $this->logger->error("Error: " . $e->getMessage());
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($output);
    }
}
