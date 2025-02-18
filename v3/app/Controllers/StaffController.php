<?php

namespace V3\App\Controllers;

use V3\App\Models\Staff;

use V3\App\Utilities\Sanitizer;
use V3\App\Utilities\AuthHelper;
use V3\App\Utilities\Permission;
use V3\App\Services\AuthService;
use V3\App\Services\StaffService;
use V3\App\Models\SchoolSettings;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Models\RegistrationTracker;
use V3\App\Utilities\DatabaseConnector;

/**
 * Class StaffController
 *
 * Handles staff-related operations.
 */

class StaffController
{
    private array $post;
    private Staff $staff;
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private StaffService $staffService;
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
            $this->staff = new Staff(pdo: $db);
            $this->schoolSettings = new SchoolSettings($db);
            $this->regTracker = new RegistrationTracker($db);
        } else {
            $this->response['message'] = '_db is required.';

            ResponseHandler::sendJsonResponse($this->response);
        }

        // Instantiate the service with the necessary models
        $this->staffService = new staffService(
            $this->staff,
            $this->schoolSettings,
            $this->regTracker
        );
    }

    /**
     * Adds a new staff.
     */
    public function addStaff()
    {
        try {
            $data = $this->validateAndGetData();
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse($this->response);
        }

        try {
            $staffId = $this->staff->insertStaff($data);
            if ($staffId) {
                $success = $this->staffService->generateRegistrationNumber($staffId);
                if ($success) {
                    $this->response = [
                        'success' => true,
                        'message' => 'Staff added successfully.',
                        'staff_id' => $staffId
                    ];
                } else {
                    throw new \Exception('Failed to generate staff number.');
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

    public function getStaff()
    {
        try {
            $results = $this->staff->getStaff(columns: [
                'id',
                'picture_ref',
                'surname',
                'first_name',
                'middle',
                'staff_no'
            ]);

            if ($results) {
                $staffDetails = array_map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'profile_url' => $row['picture_ref'],
                        'surname' => $row['surname'],
                        'first_name' => $row['first_name'],
                        'middle' => $row['middle'],
                        'staff_no' => $row['staff_no'],
                    ];
                }, $results);

                $this->response = ['success' => true, 'staff_record' => $staffDetails];
            }
        } catch (\PDOException $e) {
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getStaffById(array $params) {}

    public function updateStaff() {}

    public function deleteStaff($params) {}

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
            "access_level" => (int) Sanitizer::sanitizeInput($this->post['access_level']),
            "password" => $this->staffService->generatePassword($surname)
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
