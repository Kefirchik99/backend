<?php

namespace Yaro\EcommerceProject\Utils;

class JsonLoader
{
    /**
     * Load and decode JSON data from a file.
     *
     * @param string $filePath The path to the JSON file.
     * @return array The decoded JSON data as an associative array.
     * @throws \Exception If the file is missing or invalid.
     */
    public static function load(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        $data = json_decode(file_get_contents($filePath), true);
        if (!$data) {
            throw new \Exception("Failed to parse JSON file: $filePath");
        }

        return $data;
    }
}
