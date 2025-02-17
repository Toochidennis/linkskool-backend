<?php

namespace V3\App\Controllers;

use V3\App\Utilities\Sanitizer;
use V3\App\Utilities\AuthHelper;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\QueryExecutor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Utilities\DatabaseConnector;


class AuthController
{
    private $this->response = ['success' => false, 'message' => '', 'data' => ''];

    public function __construct()
    {
        AuthHelper::verifyAPIKey();
    }

    public function handleAuthRequest()
    {
        try {
            $post = DataExtractor::extractPostData();
            
            if(!isset($post['username'], $post['password'], $post['token'])){
                $this->response['message'] = 'Invalid JSON payload. Ensure that all fields are provided.';
                ResponseHandler::sendJsonResponse($this->response);
            }

            $username = Sanitizer::sanitizeInput($post['username']);
            $password = Sanitizer::sanitizeInput($post['password']);
            $token = Sanitizer::sanitizeInput($post['token']);

            if (empty($username) || empty($password) || empty($token)) {
                http_response_code(400);
                $this->response['message'] = 'Invalid input. All fields are required.';
                ResponseHandler::sendJsonResponse($this->response);
            }

            $pdo = DatabaseConnector::connect();

            if (!$pdo) {
                http_response_code(500);
                $this->response['message'] = 'Unable to connect to the database.';
                ResponseHandler::sendJsonResponse($this->response);
            }

            $queryExecutor = new QueryExecutor($pdo);

            // Fetch school data by token
            $result = $queryExecutor->findBy(
                table: 'school_data',
                conditions: ['token' => $token],
                limit: 1
            );

            if (!empty($result)) {
                $dbName = $result['database_name'];
                $schoolName = $result['school_name'];

                $schoolDb = DatabaseConnector::connect(dbname: $dbName);

                if (!$schoolDb) {
                    http_response_code(500);
                    $this->response['message'] = 'Unable to connect to the school database.';
                    ResponseHandler::sendJsonResponse($this->response);
                }

                $this->login(username: $username, password: $password, schoolName: $schoolName, db: $schoolDb);
            } else {
                http_response_code(404);
                $this->response['message'] = 'School not found';
                ResponseHandler::sendJsonResponse($this->response);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            $this->response['message'] =  $e->getMessage();
            ResponseHandler::sendJsonResponse($this->response);
        }
    }


    // Login: Verify user credentials and generate JWT
    public function login($username, $password, $schoolName, $db)
    {
        try {
            $queryExecutor = new QueryExecutor($db);
            $user = $queryExecutor->findBy(table: 'staff_record', conditions: ['staff_no' => $username], limit: 1);

            if (!$user) {
                $user = $queryExecutor->queryWithJoins(
                    table: 'students_record',
                    joins: [
                        [
                            'table' => 'class_table',
                            'condition' => 'students_record.student_class = class_table.id',
                            'type' => 'INNER'
                        ]
                    ],
                    conditions: ['registration_no' => $username],
                    limit: 1
                );

                if (!$user) {
                    http_response_code(404);
                    $this->response['message'] = 'User not found.';
                    ResponseHandler::sendJsonResponse($this->response);
                }

                $passwordHash = password_hash($user['password'], PASSWORD_DEFAULT);

                if (!password_verify($password, $passwordHash)) {
                    http_response_code(401);
                    $this->response['message'] = 'Invalid password.';
                    ResponseHandler::sendJsonResponse($this->response);
                }

                $token = AuthHelper::generateJWT(userId: $user['id'], name: $user['surname'], role: 'student');
                $this->response = ['success' => true, 'message' => 'Login successful', 'data' => ['token' => $token]];
                ResponseHandler::sendJsonResponse($this->response);
            }

            $passwordHash = password_hash($user['password'], PASSWORD_DEFAULT);

            if (!password_verify($password, $passwordHash)) {
                http_response_code(401);
                $this->response['message'] = 'Invalid password.';
                ResponseHandler::sendJsonResponse($this->response);
            }

            switch ($user['access_level']) {
                case 2:
                    $token = AuthHelper::generateJWT(userId: $user['id'], name: $user['surname'], role: 'admin');
                    $this->response = ['success' => true, 'message' => 'Login successful', 'data' => ['token' => $token]];
                    ResponseHandler::sendJsonResponse($this->response);
                default:
                    http_response_code(403);
                    $this->response['message'] = 'Forbidden';
                    ResponseHandler::sendJsonResponse($this->response);
            }
        } catch (\Exception $e) {
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse($this->response);
        }
    }

    public function logout()
    {
        echo "Hi";
    }
}
