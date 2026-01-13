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

    public function create()
    {
        $validatedData = $this->validate(
            $this->getRequestData(),
            [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'created_by' => 'required|integer',
                'shortname' => 'required|string|max:100',
                'status' => 'required|string|in:draft,published,archived',
                'is_free' => 'required|boolean',
                'trial_type' => 'required|string|in:days,watches',
                'trial_value' => 'required|integer|min:0',
                'age_groups' => 'required|array',
                'age_groups.*' => 'string',
                'cost' => 'nullable|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'sponsor' => 'nullable|string|max:255',

                'banner_image' => 'required|array',
                'banner_image.name' => 'required|string',
                'banner_image.tmp_name' => 'required|string',
                'banner_image.error' => 'required|integer',
                'banner_image.size' => 'required|integer'
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
                'is_free' => 'required|boolean',
                'trial_type' => 'required|string|in:days,watches',
                'trial_value' => 'required|integer|min:0',
                'age_groups' => 'required|array',
                'age_groups.*' => 'string',
                'cost' => 'nullable|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'sponsor' => 'nullable|string|max:255',

                'banner_image' => 'nullable|array',
                'banner_image.name' => 'required_with:banner_image|string',
                'banner_image.tmp_name' => 'required_with:banner_image|string',
                'banner_image.error' => 'required_with:banner_image|integer',
                'banner_image.size' => 'required_with:banner_image|integer',

                'old_banner_image' => 'nullable|string'
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
}
