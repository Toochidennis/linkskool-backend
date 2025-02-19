<?php

namespace V3\App\Controllers\Portal;

use V3\App\Models\Portal\Level;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Utilities\DatabaseConnector;
use V3\App\Services\Portal\AuthService;
use V3\App\Services\Portal\LevelService;

class LevelController{
    private array $response = ['success' => false, 'message' => ''];
    private array $post;
    private Level $level;
    private LevelService $levelService;

    public function __construct(){
        $this->initialize();
    }

    private function initialize()
    {
        AuthService::verifyAPIKey();
        AuthService::verifyJWT();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = DataExtractor::extractPostData();
            $dbname = $this->post['_db'] ?? '';
        } else {
            $dbname = $_GET['_db'] ?? '';
        }

        if (!empty($dbname)) {
            $db = DatabaseConnector::connect(dbname: $dbname);
            $this->level = new Level(pdo: $db);
        } else {
            $this->response['message'] = '_db is required.';
            http_response_code(401);
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        $this->levelService = new LevelService();
    }

    
    public function addLevel()
    {
        try {
            $data = $this->levelService->validateAndGetData(post: $this->post);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        try {
            $success = $this->level->insertLevel(data: $data);
            $this->response = [
                'success' => true,
                'message' => 'level added successfully.',
                'level_id' => $success
            ];
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateLevel() {}

    public function getLevels()
    {
        try {
            $result = $this->level->getLevels();

            if ($result) {
                $levels  = array_map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'level_name' => $row['level_name'],
                        'school_type' => $row['school_type'],
                        'rank' => $row['rank']
                    ];
                }, $result);

                $this->response = ['success' => true, 'levels' => $levels];
            }else{
                $this->response = ['success' => true, 'levels' => []];
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getLevelById() {}

    public function deleteLevel() {}
}