<?php

require_once __DIR__ . '/../bootstrap.php';

// Example: Handle a simple request (GraphQL or API endpoint)
header('Content-Type: application/json');

// Simulated routing for a GraphQL API
if ($_SERVER['REQUEST_URI'] === '/graphql') {
    echo json_encode(['message' => 'GraphQL endpoint not yet implemented.']);
} else {
    echo json_encode(['message' => 'Welcome to the eCommerce API!']);
}
