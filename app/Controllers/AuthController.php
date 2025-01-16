<?php

namespace V3\App\Controllers;

require_once '../config/config.php';

use V3\App\Utilities\Sanitizer;
use V3\App\Utilities\AuthHelper;
use V3\App\Utilities\QueryExecutor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Utilities\DatabaseConnector;


class AuthController
{
    private $response = ['success' => false, 'message' => '', 'data' => ''];

    public function handleAuthRequest()
    {
        try {
            AuthHelper::verifyAPIKey();

            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                    if (stripos($contentType, 'application/json') !== false) {
                        // JSON Request
                        $post = json_decode(file_get_contents('php://input'), true);
                        if (json_last_error() !== JSON_ERROR_NONE  || !isset($post['username'], $post['password'], $post['token'])) {
                            http_response_code(400);
                            $response['message'] = 'Invalid JSON payload. Ensure that all fields are provided.';
                            ResponseHandler::sendJsonResponse($response);
                        }
                    } elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
                        // Form-urlencoded Request
                        $post = $_POST;
                    } else {
                        http_response_code(400);
                        $response['message'] = 'Unsupported Content-Type.';
                        ResponseHandler::sendJsonResponse($response);
                    }

                    $username = Sanitizer::sanitizeInput($post['username']);
                    $password = Sanitizer::sanitizeInput($post['password']);
                    $token = Sanitizer::sanitizeInput($post['token']);

                    if (empty($username) || empty($password) || empty($token)) {
                        http_response_code(400);
                        $response['message'] = 'Invalid input. All fields are required.';
                        ResponseHandler::sendJsonResponse($response);
                    }

                    $pdo = DatabaseConnector::connect();

                    if (!$pdo) {
                        http_response_code(500);
                        $response['message'] = 'Unable to connect to the database.';
                        ResponseHandler::sendJsonResponse($response);
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
                            $response['message'] = 'Unable to connect to the school database.';
                            ResponseHandler::sendJsonResponse($response);
                        }

                        $this->login(username: $username, password: $password, schoolName: $schoolName, db: $schoolDb);
                    } else {
                        http_response_code(404);
                        $response['message'] = 'School not found';
                        ResponseHandler::sendJsonResponse($response);
                    }
                    break;
                default:
                    http_response_code(405);
                    $response['message'] = 'Method not allowed';
                    ResponseHandler::sendJsonResponse($response);
                    break;
            }
        } catch (\Exception $e) {
            http_response_code(500);
            $response['message'] =  $e->getMessage();
            ResponseHandler::sendJsonResponse($response);
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
}
