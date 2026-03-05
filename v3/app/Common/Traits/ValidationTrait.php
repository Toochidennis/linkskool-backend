<?php

namespace V3\App\Common\Traits;

use Illuminate\Validation\Factory;
use Illuminate\Translation\Translator;
use V3\App\Common\Utilities\HttpStatus;
use Illuminate\Translation\ArrayLoader;
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
            $loader = new ArrayLoader();
            $loader->addMessages('en', 'validation', [
                'required' => 'The :attribute field is required.',
                'min' => 'The :attribute must be at least :min.',
                'integer' => 'The :attribute must be an integer.',
                'string' => 'The :attribute must be a string.',
                'filled' => 'The :attribute field must not be empty.',
                'array' => 'The :attribute must be an array.',
                'date' => 'The :attribute is not a valid date.',
                'numeric' => 'The :attribute must be a number.',
                'in' => 'The selected :attribute is invalid.',
                'required_if' => 'The :attribute field is required when :other is :value.',
                'digits' => 'The :attribute field must be exactly :digits digits.',
                'email' => 'The :attribute must be a valid email address.',
                'max' => 'The :attribute may not be greater than :max.',
                'unique' => 'The :attribute has already been taken.',
                'required_without' => 'The :attribute field is required when :values is not present.',
                'required_with' => 'The :attribute field is required when :values is present.',
                'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
            ]);
            $translator = new Translator($loader, 'en');
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
            return $validator->validated();
        }
    }
}
