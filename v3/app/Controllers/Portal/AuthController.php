<?php

namespace V3\App\Controllers\Portal;

use Exception;
use PDO;
use V3\App\Utilities\HttpStatus;
use V3\App\Models\Portal\AuthModel;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Services\Portal\AuthService;
use V3\App\Utilities\DatabaseConnector;

class AuthController
{
    private array $response = ['success' => false, 'message' => ''];
    private AuthModel $authModel;
    use ValidationTrait;

    public function __construct()
    {
        AuthService::verifyAPIKey();
    }

    public function handleAuthRequest()
    {
        try {
            $post = DataExtractor::extractPostData();

            $requiredFields = ['username', 'password', 'school_code'];
            $data = $this->validateData($post, $requiredFields);

            $pdo = DatabaseConnector::connect();
            $this->authModel = new AuthModel($pdo);

            // Fetch school data by token
            $result = $this->authModel
                ->where('token', '=', $data['school_code'])
                ->first();

            if (!empty($result)) {
                $dbname = $result['database_name'];
                $schoolDb = DatabaseConnector::connect(dbname: $dbname);

                $this->login(
                    username: $data['username'],
                    password: $data['password'],
                    db: $schoolDb,
                    dbname: $dbname
                );
            } else {
                http_response_code(HttpStatus::NOT_FOUND);
                $this->response['message'] = 'School not found';
                ResponseHandler::sendJsonResponse($this->response);
            }
        } catch (Exception $e) {
            $this->response['message'] =  $e->getMessage();
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            ResponseHandler::sendJsonResponse($this->response);
        }
    }

    /**
     * Delegating user authentication to auth service.
     *
     * @param string $username
     * @param string $password
     * @param PDO $db
     * @param string dbname
     */
    public function login(
        string $username,
        string $password,
        PDO $db,
        string $dbname
    ) {
        try {
            $authService = new AuthService($db);
            $loginResponse = $authService->login($username, $password);

            $this->response = [
                'success' => true,
                'message' => 'Login successful',
                'response'   => $loginResponse + ['_db' => $dbname]
            ];
            ResponseHandler::sendJsonResponse($this->response);
        } catch (Exception $e) {
            http_response_code(HttpStatus::UNAUTHORIZED);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse($this->response);
        }
    }


    public function logout()
    {
        echo "Hi";
    }
}
