<?php

namespace V3\App\Traits;

use InvalidArgumentException;
use V3\App\Utilities\Sanitizer;

trait ValidationTrait
{
    /**
     * Validates and sanitizes data based on a list of fields.
     *
     * The $fields parameter is an associative array where keys are field names and
     * values are booleans indicating whether the field is required (true) or optional (false).
     *
     * Example:
     * [
     *    'class_name'  => true,
     *    'level'       => true,
     *    'form_teacher'=> false
     * ]
     *
     * @param array $data   The input data to validate.
     * @param array $fields Associative array defining required and optional fields.
     *
     * @return array The sanitized data.
     * @throws \InvalidArgumentException if any required field is missing or empty.
     */
    public function validateData(array $data, array $requiredFields = []): array
    {
        $errors = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "$field is required.";
            }
        }
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        unset($data['_db']);
        return $data;
    }
}
