<?php

namespace V3\App\Common\Traits;

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use InvalidArgumentException;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;

/**
 * Trait ValidationTrait
 *
 * Provides reusable data validation logic for nested arrays with optional support
 * for wildcard keys (e.g., `students.*.name`) and null/empty value rejection.
 */
trait ValidationTrait
{
    private ?Factory $validationFactory = null;

    private function getValidationFactory(): Factory
    {
        if ($this->validationFactory === null) {
            $translator = new Translator(new ArrayLoader(), 'en');
            $this->validationFactory = new Factory($translator);
        }
        return $this->validationFactory;
    }

    public function validate(array $data, $rules)
    {
        $validator = $this->getValidationFactory()->make($data, $rules);

        if ($validator->fails()) {
            http_response_code(HttpStatus::BAD_REQUEST);
            ResponseHandler::sendJsonResponse(
                [
                    'statusCode' => HttpStatus::BAD_REQUEST,
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()->all()
                ]
            );
        } else {
            var_dump($validator->validated());
            return $validator->validated();
        }
    }

    /**
     * Validates the structure and presence of required fields in input data.
     *
     * @param array $data The input data to validate (e.g., from a request body).
     * @param array $requiredFields A list of dot-notated field paths that must exist and not be empty.
     *
     * @return array The validated and cleaned data.
     * @throws InvalidArgumentException If any required field is missing or empty.
     */
    private function validate2(array $data, array $requiredFields = []): array
    {
        $errors = [];

        foreach ($requiredFields as $fieldPath) {
            $segments = explode('.', $fieldPath);
            $this->checkField($data, $segments, $fieldPath, $errors);
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        unset($data['_db']);
        return $data;
    }

    /**
     * Recursively checks if a specific field path exists and is not null/empty.
     *
     * Supports nested arrays and wildcard indexing (e.g., `items.*.price`).
     *
     * @param mixed  $data      The data being traversed (array or nested arrays).
     * @param array  $segments  The exploded parts of the field path (e.g., ['students', '*', 'name']).
     * @param string $fullPath  The original dot-notated field name (used for error messaging).
     * @param array  &$errors   A reference to the errors array to collect validation issues.
     */
    private function checkField($data, array $segments, string $fullPath, array &$errors)
    {
        $current = $data;

        foreach ($segments as $i => $segment) {
            if ($segment === '*') {
                if (!is_array($current)) {
                    $errors[] = "$fullPath is required.";
                    return;
                }

                foreach ($current as $item) {
                    $this->checkField($item, array_slice($segments, $i + 1), $fullPath, $errors);
                }
                return; // handled all items
            }

            if (!is_array($current) || !array_key_exists($segment, $current)) {
                $errors[] = "$fullPath is required.";
                return;
            }

            $current = $current[$segment];
        }

        // Final value check: reject null, empty string, and empty array
        if ($current === null || $current === '' || (is_array($current) && empty($current))) {
            $errors[] = "$fullPath cannot be null or empty.";
        }
    }

    /**
     * Validates the provided data and automatically returns a JSON error response on failure.
     *
     * Useful for controller-level data validation to avoid boilerplate try-catch code.
     * If validation fails, it will send an HTTP 400 response with details and halt execution.
     *
     * @return array Returns the validated data if successful.
     *               Otherwise, sends a JSON error response and halts further execution.
     */
    public function validateData(array $data, array $requiredFields = []): array
    {
        try {
            return $this->validate2($data, $requiredFields);
        } catch (InvalidArgumentException $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            http_response_code(HttpStatus::BAD_REQUEST);
            ResponseHandler::sendJsonResponse(response: $response);
        }
    }
}
