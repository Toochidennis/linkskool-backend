<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Traits\ValidationTrait;
use V3\App\Common\Utilities\DataExtractor;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Common\Utilities\Sanitizer;
use V3\App\Database\DatabaseConnector;

abstract class ExploreBaseController
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

        $this->post = $payload;
        $this->pdo = DatabaseConnector::connect();
    }

    protected function getRequestData(): array
    {
        return $this->post;
    }

    protected function respond(
        array $data = [],
        int $statusCode = HttpStatus::OK
    ): void {
        http_response_code($statusCode);
        ResponseHandler::sendJsonResponse(
            ['statusCode' => $statusCode, ...$data]
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
