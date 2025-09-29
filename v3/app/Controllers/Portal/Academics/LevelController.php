<?php

namespace V3\App\Controllers\Portal\Academics;

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
        $data = $this->validate(
            data: $this->post,
            rules: [
                'level_name' => 'required|string|filled',
                'school_type' => 'required|string|in:primary,secondary,nursery',
                'result_template' => 'sometimes|string',
                'rank' => 'sometimes|integer'
            ]
        );

        try {
            $levelId = $this->levelService->addLevel(data: $data);

            if ($levelId) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Level added successfully.',
                    'level_id' => $levelId
                ], HttpStatus::CREATED);
            }

            return $this->respondError(
                'Failed to add level',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateLevel(array $vars)
    {
        $data = $this->validate(
            data: array_merge($this->post, $vars),
            rules: [
                'id' => 'required|integer|filled',
                'level_name' => 'required|string|filled',
                'school_type' => 'required|string|in:primary,secondary,nursery',
                'result_template' => 'sometimes|string',
                'rank' => 'sometimes|integer'
            ]
        );

        try {
            $updated = $this->levelService->updateLevel($data);

            if ($updated) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Level updated successfully.'
                ], HttpStatus::OK);
            }

            return $this->respondError(
                'Failed to update level',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getLevels()
    {
        try {
            $this->respond([
                'success' => true,
                'data' => $this->levelService->fetchLevels()
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function deleteLevel(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer|filled'
            ]
        );

        try {
            $deleted = $this->levelService->deleteLevel($data['id']);

            if ($deleted) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Level deleted successfully.'
                ], HttpStatus::OK);
            }

            return $this->respondError(
                'Failed to delete level',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
