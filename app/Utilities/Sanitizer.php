<?php
namespace V3\App\Utilities;

class Sanitizer{
    // Use this for general text input sanitization
    public static function sanitizeInput($input)
    {
        // Trim, strip tags, and remove special HTML characters
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}