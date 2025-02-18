<?php

namespace V3\App\Services\Portal;

use V3\App\Utilities\Sanitizer;
use V3\App\Models\Portal\Student;
use V3\App\Models\Portal\SchoolSettings;
use V3\App\Models\Portal\RegistrationTracker;

class StudentService
{
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private Student $student;

    /**
     * StudentRegistrationService constructor.
     *
     * @param Student         $student
     * @param SchoolSettings  $schoolSettings
     * @param RegistrationTracker $regTracker
     */
    public function __construct(Student $student, SchoolSettings $schoolSettings, RegistrationTracker $regTracker)
    {
        $this->student = $student;
        $this->schoolSettings = $schoolSettings;
        $this->regTracker = $regTracker;
    }

    /**
     * Generate and update the registration number for a given student.
     *
     * @param int $studentId
     * @return bool
     */
    public function generateRegistrationNumber(int $studentId): bool
    {
        $prefixResult = $this->schoolSettings->getStudentPrefix();
        $regResult = $this->regTracker->getStudentLastRegNumber();
        $newNumber = '001';

        if ($prefixResult) {
            $studentPrefix = $prefixResult['student_prefix'];

            if ($regResult) {
                $lastNumber = (int)$regResult['student_reg_number'];
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            $studentRegNumber = "$studentPrefix$studentId$newNumber";
        } else {
            $studentRegNumber = "$studentId$newNumber";
        }

        // Update the student's registration number
        $updateStudentStmt = $this->student->updateStudent(
            data: ['registration_no' => $studentRegNumber],
            conditions: ['id' => $studentId]
        );

        // Update (or insert) the last used registration number in the tracker
        $regStmt = $regResult ?
            $this->regTracker->updateRegNumber(
                data: ['student_reg_number' => $newNumber],
                conditions: ['id' => $regResult['id']]
            ) : $this->regTracker->insertRegNumber(data: ['student_reg_number' => $newNumber]);

        return $updateStudentStmt && $regStmt;
    }

    /**
     * Generates a hashed password using the student's surname as a seed.
     *
     * @param string $surname
     * @return string
     * @throws \Exception
     */
    public function generatePassword(string $surname): string
    {
        return substr($surname, 0, 4) . rand(10000, 90000);
    }

    
    /**
     * Validates POST data and returns sanitized data or false on error.
     *
     * @return array|false
     * @throws \InvalidArgumentException
     */
    public function validateAndGetData(array $post)
    {
        // Define an array for required fields with custom error messages
        $requiredFields = [
            'surname' => 'Surname is required.',
            'first_name' => 'First name is required.',
            'sex' => 'Gender is required.',
            'student_class' => 'Class is required.',
            'student_level' => 'Level is required.',
        ];

        $errors = [];

        // Loop through required fields and check if they are empty
        $errors = [];
        foreach ($requiredFields as $field => $errorMessage) {
            if (!isset($post[$field]) || empty($post[$field])) {
                $errors[] = $errorMessage;
            }
        }

        // Sanitize and set each input
        $surname = Sanitizer::sanitizeInput($post['surname']);
        $data =  [
            "surname" => $surname,
            "first_name" => Sanitizer::sanitizeInput($post['first_name']),
            "middle" => Sanitizer::sanitizeInput($post['middle'] ?? ''),
            "sex" => (int) Sanitizer::sanitizeInput($post['sex']),
            //"birthdate" => Sanitizer::sanitizeInput($post['birthdate']),
            // "email" => filter_var(Sanitizer::sanitizeInput($post['email'])),
            // "guardian_name" => Sanitizer::sanitizeInput($post['guardian_name']),
            // "guardian_email" => filter_var(Sanitizer::sanitizeInput($post['guardian_email']), FILTER_VALIDATE_EMAIL), // Validate email format
            // "guardian_address" => Sanitizer::sanitizeInput($post['guardian_address']),
            // "guardian_phone_no" => Sanitizer::sanitizeInput($post['guardian_phone_no']),
            // "state_origin" => Sanitizer::sanitizeInput($post['state_origin']),
            "student_level" => Sanitizer::sanitizeInput($post['student_level']),
            "student_class" => Sanitizer::sanitizeInput($post['student_class']),
            "password" => $this->generatePassword($surname)
        ];

        // Check for invalid email
        // if (!$data["guardian_email"]) {
        //     $errors[] = 'Invalid email address';
        // }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
        }

        return $data;
    }
}
