<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProgramService;

#[Group('/public/programs',)]
class ProgramController extends ExploreBaseController
{
    private ProgramService $programService;

    public function __construct()
    {
        parent::__construct();
        $this->programService = new ProgramService($this->pdo);
    }

    #[Route('', 'POST', ['api', 'auth'])]
    public function create()
    {
        $validatedData = $this->validate(
            $this->getRequestData(),
            [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'author_name' => 'required|string|max:255',
                'author_id' => 'required|integer',
                'shortname' => 'required|string|max:100',
                'status' => 'required|string|in:draft,published,archived',
                'sponsor' => 'nullable|string|max:255',

                'image' => 'required|array',
                'image.name' => 'required|string',
                'image.tmp_name' => 'required|string',
                'image.error' => 'required|integer',
                'image.size' => 'required|integer'
            ]
        );

        $id =  $this->programService->createProgram($validatedData);

        if (!$id) {
            $this->respondError(
                'Failed to create program.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program created successfully.',
                'id' => $id
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{id}', 'POST', ['api', 'auth'])]
    public function update(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'updated_by' => 'required|integer',
                'shortname' => 'required|string|max:100',
                'status' => 'required|string|in:draft,published,archived',
                'sponsor' => 'nullable|string|max:255',

                'image' => 'nullable|array',
                'image.name' => 'required_with:image|string',
                'image.tmp_name' => 'required_with:image|string',
                'image.error' => 'required_with:image|integer',
                'image.size' => 'required_with:image|integer',
                'old_image_url' => 'nullable|string'
            ]
        );

        $updated = $this->programService->updateProgram($validatedData);

        if (!$updated) {
            $this->respondError(
                'Failed to update program.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program updated successfully.'
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}/status', 'PUT', ['api', 'auth'])]
    public function updateStatus(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'status' => 'required|string|in:draft,published,archived'
            ]
        );

        $updated = $this->programService->updateStatus(
            $validatedData['id'],
            $validatedData['status']
        );

        if (!$updated) {
            $this->respondError(
                'Failed to update program status.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program status updated successfully.'
            ],
            HttpStatus::OK
        );
    }

    #[Route('', 'GET', ['api', 'auth'])]
    public function getAllPrograms()
    {
        $programs = $this->programService->getAllPrograms();

        if (empty($programs)) {
            $this->respond(
                [
                    'status' => true,
                    'message' => 'No programs yet',
                    'data' => []
                ]
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'Programs fetched successfully',
                'data' => $programs
            ]
        );
    }

    #[Route('/{id}', 'DELETE', ['api', 'auth'])]
    public function deleteProgram(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'id' => 'required|integer',
            ]
        );

        $deleted = $this->programService->deleteProgram((int)$validatedData['id']);

        if (!$deleted) {
            $this->respondError(
                'Failed to delete program.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program deleted successfully.',
            ],
            HttpStatus::OK
        );
    }
}
