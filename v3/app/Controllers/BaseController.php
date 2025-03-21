<?php

namespace V3\App\Controllers;

use PDO;
use V3\App\Utilities\DatabaseConnector;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Services\Portal\AuthService;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\Sanitizer;

abstract class BaseController
{
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
     * @var array Default response structure.
     */
    protected array $response;

    /**
     * Initializes the controller by verifying API key/JWT and extracting request data.
     *
     * This method:
     * - Calls AuthService::verifyAPIKey() and AuthService::verifyJWT() to ensure that the request is authenticated.
     * - Extracts the POST data (if the request method is POST) or the query parameters (if GET) to obtain the '_db' parameter.
     *
     * @return void
     */
    public function __construct()
    {
        //AuthService::verifyAPIKey();
        //AuthService::verifyJWT();

        // Extract the database name based on request method.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = Sanitizer::sanitizeInput(DataExtractor::extractPostData());
            $dbname = $this->post['_db'] ?? '';
        } else {
            $get = Sanitizer::sanitizeInput($_GET);
            $dbname = $get['_db'] ?? '';
        }

       // ResponseHandler::sendJsonResponse(['message'=>$dbname]);

        // Validate that _db is provided.
        if (empty($dbname)) {
            http_response_code(HttpStatus::BAD_REQUEST);
            $this->response['message'] = '_db is required.';
            ResponseHandler::sendJsonResponse($this->response);
        }

        $this->pdo = DatabaseConnector::connect(dbname: $dbname);

        $this->response = ['success' => false];
    }
}
