<?php

namespace V3\App\Controllers;

use V3\App\Models\Student;
use V3\App\Utilities\Sanitizer;
use V3\App\Utilities\AuthHelper;
use V3\App\Utilities\Permission;
use V3\App\Services\AuthService;
use V3\App\Models\SchoolSettings;
use V3\App\Utilities\DataExtractor;
use V3\App\Services\StudentService;
use V3\App\Utilities\ResponseHandler;
use V3\App\Models\RegistrationTracker;
use V3\App\Utilities\DatabaseConnector;

/**
 * Class StudentController
 *
 * Handles student-related operations.
 */

class StudentController
{
    private array $post;
    private Student $student;
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private StudentService $studentService;
    private array $response = ['success' => false, 'message' => ''];


    public function __construct()
    {
        AuthService::verifyAPIKey();
        AuthService::verifyJWT();

        $this->init();
    }

    private function init()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = DataExtractor::extractPostData();
            $dbname = $this->post['_db'] ?? '';
        } else {
            $dbname = $_GET['_db'] ?? '';
        }

        if (!empty($dbname)) {
            $db = DatabaseConnector::connect(dbname: $dbname);
            $this->student = new Student(pdo: $db);
            $this->schoolSettings = new SchoolSettings($db);
            $this->regTracker = new RegistrationTracker($db);
        } else {
            $this->response['message'] = '_db is required.';

            ResponseHandler::sendJsonResponse($this->response);
        }

        // Instantiate the service with the necessary models
        $this->studentService = new StudentService(
            $this->student,
            $this->schoolSettings,
            $this->regTracker
        );
    }

    /**
     * Adds a new student.
     */
    public function addStudent()
    {
        try {
            $data = $this->validateAndGetData();
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse($this->response);
        }

        try {
            $studentId = $this->student->insertStudent($data);
            if ($studentId) {
                $success = $this->studentService->generateRegistrationNumber($studentId);
                if ($success) {
                    $this->response = [
                        'success' => true,
                        'message' => 'Student added successfully.',
                        'data' => $studentId
                    ];
                } else {
                    throw new \Exception('Failed to generate registration number.');
                }
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAllStudents() {}

    public function getStudentById(array $vars)
    {
        echo print_r($vars);
    }

    public function deleteStudent($id) {}

    /**
     * Validates POST data and returns sanitized data or false on error.
     *
     * @return array|false
     * @throws \InvalidArgumentException
     */
    private function validateAndGetData()
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
            if (!isset($this->post[$field]) || empty($this->post[$field])) {
                $errors[] = $errorMessage;
            }
        }

        // Sanitize and set each input
        $surname = Sanitizer::sanitizeInput($this->post['surname']);
        $data =  [
            "surname" => $surname,
            "first_name" => Sanitizer::sanitizeInput($this->post['first_name']),
            "middle" => Sanitizer::sanitizeInput($this->post['middle'] ?? ''),
            "sex" => (int) Sanitizer::sanitizeInput($this->post['sex']),
            //"birthdate" => Sanitizer::sanitizeInput($this->post['birthdate']),
            // "email" => filter_var(Sanitizer::sanitizeInput($this->post['email'])),
            // "guardian_name" => Sanitizer::sanitizeInput($this->post['guardian_name']),
            // "guardian_email" => filter_var(Sanitizer::sanitizeInput($this->post['guardian_email']), FILTER_VALIDATE_EMAIL), // Validate email format
            // "guardian_address" => Sanitizer::sanitizeInput($this->post['guardian_address']),
            // "guardian_phone_no" => Sanitizer::sanitizeInput($this->post['guardian_phone_no']),
            // "state_origin" => Sanitizer::sanitizeInput($this->post['state_origin']),
            "student_level" => Sanitizer::sanitizeInput($this->post['student_level']),
            "student_class" => Sanitizer::sanitizeInput($this->post['student_class']),
            "password" => $this->studentService->generatePassword($surname)
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
