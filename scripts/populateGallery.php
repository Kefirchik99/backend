<?php
require '/var/www/html/vendor/autoload.php'; // Correct the path to the vendor directory

use Yaro\EcommerceProject\Config\Database;

function populateGalleryFromJson(string $jsonFilePath): void
{
    // Get database connection
    $db = Database::getConnection();

    // Read JSON file
    $jsonData = json_decode(file_get_contents($jsonFilePath), true);

    // Validate JSON
    if (!is_array($jsonData)) {
        echo "Invalid JSON file.\n";
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
                } catch (\PDOException $e) {
                    echo "Error inserting gallery for product_id {$product['id']}: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    echo "Gallery table populated successfully.\n";
}

// Run the script
populateGalleryFromJson('/var/www/html/backend/data/data.json');  
