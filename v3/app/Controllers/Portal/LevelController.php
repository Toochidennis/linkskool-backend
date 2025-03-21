<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Level;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;

class LevelController extends BaseController
{
    use ValidationTrait;
    private Level $level;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->level = new Level(pdo: $this->pdo);
    }

    public function addLevel()
    {
        $requiredFields = ['level_name', 'school_type', 'rank'];
        $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);

        try {
            $levelId = $this->level->insert(data: $data);

            $this->response = $levelId ?
                [
                    'success' => true,
                    'message' => 'Level added successfully.'
                ] :
                [
                    'success' => false,
                    'message' => 'Failed to add level.'
                ];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateLevel() {}

    public function getLevels()
    {
        try {
            $result = $this->level->get();

            $levels  = array_map(fn($row) => [
                'id' => $row['id'],
                'level_name' => $row['level_name'],
                'school_type' => $row['school_type'],
                'rank' => $row['rank']
            ], $result);

            $this->response = ['success' => true, 'levels' => $levels];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getLevelById() {}

    public function deleteLevel() {}
}
