<?php

namespace V3\App\Services\Portal;

use V3\App\Utilities\Sanitizer;

class LevelService
{
    public function __construct() {}

    public function validateAndGetData(array $post)
    {
        // Required fields with custom error messages
        $requiredFields = [
            'level_name' => 'Level name is required.',
            'school_type' => 'School type is required.',
            'rank' => 'Rank is required.'
        ];

        // Loop through required fields and check if they are empty
        $errors = [];
        foreach ($requiredFields as $field => $errorMessage) {
            if (!isset($post[$field]) || empty($post[$field])) {
                $errors[] = $errorMessage;
            }
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
        }

        return [
            'level_name' => Sanitizer::sanitizeInput($post['level_name']),
            'school_type' => Sanitizer::sanitizeInput($post['school_type']),
            'rank' => Sanitizer::sanitizeInput($post['rank'])
        ];
    }
}
