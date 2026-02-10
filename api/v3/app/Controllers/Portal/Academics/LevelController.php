<?php

namespace V3\App\Controllers\Portal\Academics;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Academics\LevelService;

#[Group('/portal')]
class LevelController extends BaseController
{
    private LevelService $levelService;

    public function __construct()
    {
        parent::__construct();
        $this->levelService = new LevelService($this->pdo);
    }

    #[Route('/levels', 'POST', ['auth', 'role:admin'])]
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
    }

    #[Route('/levels/{id:\d+}', 'PUT', ['auth', 'role:admin'])]
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
    }

    #[Route('/levels', 'GET', ['auth', 'role:admin'])]
    public function getLevels()
    {
        $this->respond([
            'success' => true,
            'data' => $this->levelService->fetchLevels()
        ]);
    }

    #[Route('/levels/{id:\d+}', 'DELETE', ['auth', 'role:admin'])]
    public function deleteLevel(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer|filled'
            ]
        );

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
    }
}
