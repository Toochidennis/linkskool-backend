<?php

namespace V3\App\Controllers;

use V3\App\Database\DatabaseConnector;
use V3\App\Common\Traits\ValidationTrait;
use V3\App\Common\Utilities\{DataExtractor, ResponseHandler, HttpStatus, Sanitizer};

abstract class BaseController
{
    use ValidationTrait;

    /**
     * @var array The extracted POST data (if any).
     */
    protected array $post = [];

    /**
     * @var \PDO The active PDO connection instance.
     */
    protected \PDO $pdo;

    /**
     * Constructor to initialize common properties and handle request data
     *
     */
    public function __construct()
    {
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
