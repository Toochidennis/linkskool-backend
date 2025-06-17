<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Level;

class LevelController extends BaseController
{
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
        $data = $this->validateData(data: $this->post, requiredFields: ['level_name', 'school_type', 'rank']);

        try {
            $levelId = $this->level->insert(data: $data);

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
            $result = $this->level->get();
            $this->respond(['success' => true, 'response' => $result]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
