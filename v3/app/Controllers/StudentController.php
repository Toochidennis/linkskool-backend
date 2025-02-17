<?php

namespace V3\App\Controllers;

use Exception;
use V3\App\Models\StudentModel;
use V3\App\Utilities\Sanitizer;
use V3\App\Utilities\AuthHelper;
use V3\App\Utilities\Permission;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Models\SchoolSettingsModel;
use V3\App\Utilities\DatabaseConnector;


class StudentController
{
    private $post;
    private $studentModel;
    private $settingsModel;
    private $response = ['success' => false, 'message' => ''];


    public function __construct()
    {
        AuthHelper::verifyAPIKey();
        AuthHelper::verifyJWT();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = DataExtractor::extractPostData();
            $dbname = $this->post['_db'] ?? '';
        } else {
            $dbname = $_GET['_db'] ?? '';
        }

        if (!empty($dbname)) {
            $db = DatabaseConnector::connect(dbname: $dbname);
            $this->studentModel = new StudentModel(pdo: $db);
            $this->settingsModel = new SchoolSettingsModel($db);
        } else {
            $this->response['message'] = '_db is required.';

            ResponseHandler::sendJsonResponse($this->response);
        }
    }

    public function addStudent()
    {
        //$this->generateRegNumber();

        $data = $this->validateAndGetData();

        if ($data) {
            try {
                $result = $this->studentModel->insertStudent($data);
                $this->response = ['success' => true, 'message' => 'Successful', 'data' => $result];
            } catch (\PDOException $e) {
                http_response_code(500);
                $this->response['message'] = $e->getMessage();
            }
        } else {
            http_response_code(400);
            $this->response['message'] = $GLOBALS['statusMessage'];
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAllStudents() {}

    public function getStudentById(array $vars)
    {
        echo print_r($vars);
    }

    public function deleteStudent($id) {}

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
        foreach ($requiredFields as $field => $errorMessage) {
            if (!isset($this->post[$field])) {
                throw new \InvalidArgumentException('Invalid field specified');
            } else if (empty($this->post[$field])) {
                $errors[] = $errorMessage;
            }
        }

        // Sanitize and set each input
        $data =  [
            "surname" => Sanitizer::sanitizeInput($this->post['surname']),
            "first_name" => Sanitizer::sanitizeInput($this->post['first_name']),
            "middle" => Sanitizer::sanitizeInput($this->post['middle'] ?? ''),
            "sex" => (int) Sanitizer::sanitizeInput($this->post['sex']),
            //"birthdate" => Sanitizer::sanitizeInput($this->post['birthdate']),
            "registration_no" => Sanitizer::sanitizeInput($this->post['registration_no'] ?? ''),
            // "email" => filter_var(Sanitizer::sanitizeInput($this->post['email'])),
            // "guardian_name" => Sanitizer::sanitizeInput($this->post['guardian_name']),
            // "guardian_email" => filter_var(Sanitizer::sanitizeInput($this->post['guardian_email']), FILTER_VALIDATE_EMAIL), // Validate email format
            // "guardian_address" => Sanitizer::sanitizeInput($this->post['guardian_address']),
            // "guardian_phone_no" => Sanitizer::sanitizeInput($this->post['guardian_phone_no']),
            // "state_origin" => Sanitizer::sanitizeInput($this->post['state_origin']),
            "student_level" => Sanitizer::sanitizeInput($this->post['student_level']),
            "student_class" => Sanitizer::sanitizeInput($this->post['student_class'])
        ];

        // Check for invalid email
        // if (!$data["guardian_email"]) {
        //     $errors[] = 'Invalid email address';
        // }

        if (!empty($errors)) {
            $GLOBALS['statusMessage'] = implode(', ', $errors);
            return false;
        }
        return $data;
    }

    private function generateRegNumber()
    {
        try {
            $result = $this->settingsModel->getStudentPrefix();

        } catch (Exception $e) {
        }
    }


    private function generatePassword(string $surname) {}
}
