<?php

namespace V3\App\Services\Portal;

use V3\App\Utilities\Sanitizer;

class CourseService{
    
    public function __construct(){}

    public function validateAndGetData(array $post){
        // Required fields with custom error messages
        $requiredFields = [
            'course_name' => 'Course name is required.',
            'course_code' => 'Course code is required.'
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
            'course_name'=> Sanitizer::sanitizeInput($post['course_name']),
            'course_code'=> Sanitizer::sanitizeInput($post['course_code'])
        ];
    }
}