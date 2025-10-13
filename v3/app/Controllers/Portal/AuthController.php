<?php

namespace V3\App\Controllers\Portal;

use PDO;
use V3\App\Models\Portal\AuthModel;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Portal\AuthService;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Common\Traits\{AuthenticatesRequests, ValidationTrait};
use V3\App\Common\Utilities\{ResponseHandler, DataExtractor, HttpStatus};

#[Group('/portal')]
class AuthController
{
    use ValidationTrait;
    use AuthenticatesRequests;

    private array $response = ['success' => false, 'message' => ''];
    private AuthModel $authModel;

    public function __construct()
    {
        $this->verifyAPIKey();
    }

    #[Route(path: '/auth/login', method: 'POST', middleware: ['api'])]
    public function handleAuthRequest()
    {
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
            $dbname = getenv('DB_NAME_PREFIX') . $result['database_name'];
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
    }

    /**
     * Delegating user authentication to auth service.
     *
     * @param string        $username
     * @param string        $password
     * @param PDO           $db
     * @param string        $dbname
     */
    public function login(
        string $username,
        string $password,
        PDO $db,
        string $dbname
    ) {
        $authService = new AuthService($db);
        $loginResponse = $authService->login($username, $password);

        $this->response = [
            'success' => true,
            'message' => 'Login successful',
            'response' => $loginResponse + ['_db' => $dbname]
        ];
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function logout()
    {
        echo "Hi";
    }
}
