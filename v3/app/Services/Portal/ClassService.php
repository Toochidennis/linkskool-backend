<?php

namespace V3\App\Services\Portal;

use V3\App\Utilities\Sanitizer;

class ClassService{
    
    public function __construct() {}

    public function validateAndGetData(array $post)
    {
        // Required fields with custom error messages
        $requiredFields = [
            'class_name' => 'Class name is required.',
            'level' => 'Level is required.',
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
            'class_name' => Sanitizer::sanitizeInput($post['class_name']),
            'level' => Sanitizer::sanitizeInput($post['level']),
            'form_teacher' => Sanitizer::sanitizeInput($post['form_teacher'] ?? '')
        ];
    }
}