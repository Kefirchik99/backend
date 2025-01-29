<?php
require '/var/www/html/vendor/autoload.php'; // Correct the path to the vendor directory
use Yaro\EcommerceProject\Config\Database;
use Psr\Log\LoggerInterface;

function populateGalleryFromJson(string $jsonFilePath, LoggerInterface $logger): void
{
    // Get database connection
    $db = Database::getConnection();
    // Read JSON file
    $jsonData = json_decode(file_get_contents($jsonFilePath), true);
    // Validate JSON
    if (!is_array($jsonData)) {
        $logger->error("Invalid JSON file.");
        return;
    }
    // Populate gallery
    foreach ($jsonData as $product) {
        if (!empty($product['gallery'])) {
            foreach ($product['gallery'] as $imageUrl) {
                try {
                    $stmt = $db->prepare("
                        INSERT IGNORE INTO gallery (product_id, image_url) 
                        VALUES (:product_id, :image_url)
                    ");
                    $stmt->execute([
                        'product_id' => $product['id'],
                        'image_url' => $imageUrl,
                    ]);
                    $logger->info("Inserted gallery image for product ID {$product['id']}: $imageUrl");
                } catch (\PDOException $e) {
                    $logger->error("Error inserting gallery for product_id {$product['id']}: " . $e->getMessage());
                }
            }
        }
    }
    $logger->info("Gallery table populated successfully.");
}

// Fetch the global logger instance
$logger = $GLOBALS['logger'] ?? null;

if ($logger) {
    // Run the script
    populateGalleryFromJson('/var/www/html/backend/data/data.json', $logger);
} else {
    echo "Logger not initialized.";
}
