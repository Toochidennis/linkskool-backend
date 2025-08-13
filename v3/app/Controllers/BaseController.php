<?php

namespace V3\App\Controllers;

use PDO;
use V3\App\Common\Traits\PermissionTrait;
use V3\App\Database\DatabaseConnector;
use V3\App\Common\Utilities\DataExtractor;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\Sanitizer;
use V3\App\Common\Traits\ValidationTrait;
use V3\App\Common\Traits\AuthenticatesRequests;

abstract class BaseController
{
    use AuthenticatesRequests;
    use PermissionTrait;
    use ValidationTrait;

    /**
     * @var array The extracted POST data (if any).
     */
    protected array $post = [];

    /**
     * @var string The database name extracted from the request.
     */
    // protected string $dbname;

    /**
     * @var PDO The database connection name.
     */
    protected PDO $pdo;

    /**
     * Initializes the controller by verifying API key/JWT and extracting request data.
     *
     * This method:
     * - Calls AuthService::verifyAPIKey() and AuthService::verifyJWT()
     * to ensure that the request is authenticated.
     * - Extracts the POST data (if the request method is POST) or the
     * query parameters (if GET) to obtain the '_db' parameter.
     *
     * @return void
     */
    public function __construct()
    {
        self::verifyAPIKey();
        self::verifyJWT();

        $method = $_SERVER['REQUEST_METHOD'];

        $payload = match ($method) {
            'POST', 'PUT', 'DELETE', 'PATCH' =>
            Sanitizer::sanitizeInput(DataExtractor::extractPostData()),
            'GET' => Sanitizer::sanitizeInput($_GET),
            default => []
        };

        // Extract the database name based on request method.
        if (empty($payload)) {
            $this->respondError(
                'Request payload can not be empty',
                HttpStatus::BAD_REQUEST
            );
        }

        // Validate that _db is provided.
        if (empty($payload['_db'])) {
            $this->respondError(
                '_db field is required.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->post = $payload;
        $dbname = $this->post['_db'];
        $_SESSION['_db'] = $dbname;
        $this->pdo = DatabaseConnector::connect(dbname: $dbname);
    }

    protected function respond(
        array $data = [],
        int $statusCode = HttpStatus::OK
    ): void {
        http_response_code($statusCode);
        ResponseHandler::sendJsonResponse(
            ['statusCode' => $statusCode] + $data
        );
    }

    protected function respondError(
        string $message,
        int $statusCode = HttpStatus::INTERNAL_SERVER_ERROR
    ): void {
        $this->respond(
            ['success' => false, 'message' => $message],
            $statusCode
        );
    }
}
