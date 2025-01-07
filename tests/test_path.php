<?php

$path = __DIR__ . '/../Config/Database.php';
if (file_exists($path)) {
    echo "Database.php found at: $path\n";
} else {
    echo "Database.php NOT found at: $path\n";
}
