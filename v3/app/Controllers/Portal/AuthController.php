<?php

namespace V3\App\Controllers\Portal;

use V3\App\Models\Portal\Staff;
use V3\App\Utilities\Sanitizer;
use V3\App\Models\Portal\Student;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\QueryExecutor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Services\Portal\AuthService;
use V3\App\Utilities\DatabaseConnector;


class AuthController
{
    private array $response = ['success' => false, 'message' => ''];
    private Staff $staff;
    private Student $student;


    public function __construct()
    {
        AuthService::verifyAPIKey();
    }

    public function handleAuthRequest()
    {
        try {
            $post = DataExtractor::extractPostData();
            
            if(!isset($post['username'], $post['password'], $post['token'])){
                $this->response['message'] = 'Invalid JSON payload. Ensure that all fields are provided.';
                http_response_code(400);
                ResponseHandler::sendJsonResponse($this->response);
            }

            $username = Sanitizer::sanitizeInput($post['username']);
            $password = Sanitizer::sanitizeInput($post['password']);
            $token = Sanitizer::sanitizeInput($post['token']);

            if (empty($username) || empty($password) || empty($token)) {
                $this->response['message'] = 'Invalid input. All fields are required.';
                http_response_code(400);
                ResponseHandler::sendJsonResponse($this->response);
            }

            $pdo = DatabaseConnector::connect();
            $queryExecutor = new QueryExecutor($pdo);

            // Fetch school data by token
            $result = $queryExecutor->findBy(
                table: 'school_data',
                conditions: ['token' => $token],
                limit: 1
            );

            if (!empty($result)) {
                $dbname = $result['database_name'];
                //$schoolName = $result['school_name'];
                $schoolDb = DatabaseConnector::connect(dbname: $dbname);

                $this->login(username: $username, password: $password, db: $schoolDb);
            } else {
                http_response_code(404);
                $this->response['message'] = 'School not found';
                ResponseHandler::sendJsonResponse($this->response);
            }
        } catch (\Exception $e) {
            $this->response['message'] =  $e->getMessage();
            http_response_code(500);
            ResponseHandler::sendJsonResponse($this->response);
        }
    }


    /**
     * Delegating user authentication to auth service.
     *
     * @param string $username
     * @param string $password
     * @param \PDO   $db
     */
    public function login(string $username, string $password, \PDO $db)
    {
        try {
            $authService = new AuthService($db);
            $result = $authService->login($username, $password);

            $this->response = [
                'success' => true,
                'message' => 'Login successful',
                'token'   => $result['token']
            ];
            ResponseHandler::sendJsonResponse($this->response);
        } catch (\Exception $e) {
            http_response_code(401);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse($this->response);
        }
    }

    public function logout()
    {
        echo "Hi";
    }
}

// $student = $queryExecutor->queryWithJoins(
//     table: 'students_record',
//     joins: [
//         [
//             'table' => 'class_table',
//             'condition' => 'students_record.student_class = class_table.id',
//             'type' => 'INNER'
//         ]
//     ],
//     conditions: ['registration_no' => $username],
//     limit: 1
// );