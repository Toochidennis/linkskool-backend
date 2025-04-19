<?php

namespace V3\App\Traits;

use InvalidArgumentException;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;

trait ValidationTrait
{
    /**
     * Validates data based on a list of fields.
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
     * @throws InvalidArgumentException if any required field is missing or empty.
     */
    private function validate(array $data, array $requiredFields = []): array
    {
        $errors = [];
        foreach ($requiredFields as $fieldPath) {
            $value = $this->getNestedValue($data, explode('.', $fieldPath));

            if ($value === null || empty($value)) {
                $errors[] = "$fieldPath is required.";
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        unset($data['_db']);
        return $data;
    }

    /**
     * Retrieves a value from a multi-dimensional array using a path of keys.
     *
     * This method supports deep access into nested arrays using a sequence of keys.
     * It's typically used for validating nested input structures (e.g., user.profile.name).
     *
     * Example usage:
     * ```php
     * $data = [
     *     'user' => [
     *         'profile' => [
     *             'name' => 'ToochiDennis'
     *         ]
     *     ]
     * ];
     *
     * $value = $this->getNestedValue($data, ['user', 'profile', 'name']); // Returns 'ToochiDennis'
     * ```
     *
     * @param array $data The input array to traverse.
     * @param array $path An ordered list of keys representing the path to the target value.
     *
     * @return mixed|null Returns the value if found, or null if any part of the path doesn't exist.
     */
    private function getNestedValue(array $data, array $path)
    {
        foreach ($path as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

    /**
     * Validates the provided data. If validation fails, sends a JSON error response.
     *
     * @param array $data           The data to validate.
     * @param array $requiredFields An array of required field names.
     *
     * @return array|null Returns the sanitized data if validation passes; otherwise,
     *  sends an error response and returns null.
     */
    public function validateData(array $data, array $requiredFields = []): array
    {
        try {
            return $this->validate($data, $requiredFields);
        } catch (InvalidArgumentException $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            http_response_code(HttpStatus::BAD_REQUEST);
            ResponseHandler::sendJsonResponse(response: $response);
        }
    }
}
