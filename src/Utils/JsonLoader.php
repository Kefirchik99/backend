<?php

namespace Yaro\EcommerceProject\Utils;

class JsonLoader
{
    public static function load(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("JSON file not found at: $filePath");
        }

        $jsonData = file_get_contents($filePath);
        $data = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON data: " . json_last_error_msg());
        }

        return $data;
    }
}
