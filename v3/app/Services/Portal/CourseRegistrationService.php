<?php

namespace V3\App\Services\Portal;

use V3\App\Utilities\Sanitizer;

class CourseRegistrationService
{

    /**
     * Validates the provided POST data and returns sanitized data along with the type.
     *
     * Expects either a 'class_id' or a 'student_id' and a 'course_id' to be set.
     * If a 'class_id' is provided, it treats the registration as for a class.
     * If a 'student_id' is provided, it treats the registration as for a single student.
     *
     * @param array $post The POST data to validate.
     * @return array|false Returns an associative array with 'type' and 'data' keys on success, or false on failure.
     * @throws \InvalidArgumentException if required fields are missing.
     */
    public function validateAndGetData(array $post)
    {
        if (!isset($post['class']) && !isset($post['students'])) {
            throw new \InvalidArgumentException('Either class or students must be provided.');
        }

        if (!isset($post['courses'])) {
            throw new \InvalidArgumentException('courses is required.');
        }
        if (!isset($post['year'])) {
            throw new \InvalidArgumentException('year is required.');
        }
        if (!isset($post['term'])) {
            throw new \InvalidArgumentException('term is required.');
        }

        $sanitizedData = Sanitizer::sanitizeInput($post);

        // Determine the type based on provided keys.
        if (isset($sanitizedData['class']) && !empty($sanitizedData['class'])) {
            return [
                'type' => 'class',
                'data' => $sanitizedData
            ];
        } else if (isset($sanitizedData['students']) && !empty($sanitizedData['students'])) {
            return [
                'type' => 'single',
                'data' => $sanitizedData
            ];
        }

        // If neither condition met, return false (this line might never be reached due to exceptions above).
        return false;
    }
}
