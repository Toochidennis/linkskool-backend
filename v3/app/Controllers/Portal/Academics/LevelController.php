<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\LevelService;

class LevelController extends BaseController
{
    private LevelService $levelService;

    public function __construct()
    {
        parent::__construct();
        $this->levelService = new LevelService($this->pdo);
    }

    public function addLevel()
    {
        $data = $this->validateData(data: $this->post, requiredFields: ['level_name', 'school_type', 'rank']);

        try {
            $levelId = $this->levelService->addLevel(data: $data);

            if ($levelId) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Level added successfully.',
                    'level_id' => $levelId
                ], HttpStatus::CREATED);
            }

            return $this->respondError('Failed to add level', HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getLevels()
    {
        try {
            $this->respond([
                'success' => true,
                'response' => $this->levelService->fetchLevels()
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
