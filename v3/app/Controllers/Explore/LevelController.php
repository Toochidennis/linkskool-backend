<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\LevelService;

#[Group('/public/levels')]
class LevelController extends ExploreBaseController
{
    private LevelService $levelService;

    public function __construct()
    {
        parent::__construct();
        $this->levelService = new LevelService($this->pdo);
    }

    #[Route('', 'POST', ['api', 'auth'])]
    public function addLevel()
    {
        $validated =  $this->validate(
            $this->getRequestData(),
            [
                'name' => 'required|string|max:100',
                'rank' => 'required|integer|min:1',
            ]
        );

        $levelId = $this->levelService->addLevel($validated);

        if (!$levelId) {
            $this->respondError('Failed to add level.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Level added successfully.',
                'level_id' => $levelId
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('', 'PUT', ['api', 'auth'])]
    public function updateLevel(array $vars)
    {
        $validated =  $this->validate(
            $this->getRequestData(),
            [
                'id' => 'required|integer|min:1',
                'name' => 'sometimes|required|string|max:100',
                'rank' => 'sometimes|required|integer|min:1',
            ]
        );

        $updated = $this->levelService->updateLevel($validated);

        if (!$updated) {
            $this->respondError('Failed to update level.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Level updated successfully.'
            ]
        );
    }

    #[Route('', 'GET', ['api'])]
    public function getLevels()
    {
        $levels = $this->levelService->getLevels();

        $this->respond(
            [
                'success' => true,
                'data' => $levels
            ]
        );
    }

    #[Route('/{id}', 'DELETE', ['api', 'auth'])]
    public function deleteLevel(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'id' => 'required|integer|min:1',
            ]
        );

        $deleted = $this->levelService->deleteLevel($validated['id']);

        if (!$deleted) {
            $this->respondError('Failed to delete level.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Level deleted successfully.'
            ]
        );
    }
}
