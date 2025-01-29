<?php

declare(strict_types=1);

namespace Yaro\EcommerceProject;

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Yaro\EcommerceProject\Config\Database;

try {
    // Initialize DB connection once
    $db = Database::getConnection();

    // Initialize Monolog
    $logger = new Logger('my_logger');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/logs/my_app.log', Level::Debug));

    // Make the logger accessible globally
    $GLOBALS['logger'] = $logger;

    // If there's any other minimal config you want, do it here
} catch (\Exception $e) {
    // If you absolutely need to handle an error, just log it
    $logger = new Logger('my_logger');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/logs/my_app.log', Level::Debug));
    $logger->error("Bootstrap error: " . $e->getMessage());
    // No echo. Let the request fail gracefully or continue
}
