<?php
namespace V3\App\Utilities;

class EnvLoader{
    
    public static function load($filePath = __DIR__ . '/../../config/.env')
    {
        if (!file_exists($filePath)) throw new \InvalidArgumentException("File not found!");

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue; // Ignore comments
            [$name, $value] = explode('=', $line, 2);
            putenv("$name=$value"); // Set each variable in the environment
        }
    }
}