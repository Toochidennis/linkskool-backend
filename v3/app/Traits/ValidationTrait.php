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
     * @throws \InvalidArgumentException if any required field is missing or empty.
     */
    private function validate(array $data, array $requiredFields = []): array
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

    /**
     * Validates the provided data. If validation fails, sends a JSON error response.
     *
     * @param array $data           The data to validate.
     * @param array $requiredFields An array of required field names.
     *
     * @return array|null Returns the sanitized data if validation passes; otherwise, sends an error response and returns null.
     */
    public function validateData(array $data, array $requiredFields = [])
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
