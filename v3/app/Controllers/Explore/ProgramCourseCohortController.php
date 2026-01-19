<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProgramCourseCohortService;
use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;

#[Group('/public/programs/{program_id}/courses/{course_id}/cohorts')]
class ProgramCourseCohortController extends ExploreBaseController
{
    protected ProgramCourseCohortService $cohortService;

    public function __construct()
    {
        parent::__construct();
        $this->cohortService = new ProgramCourseCohortService($this->pdo);
    }

    #[Route('', 'POST', ['api', 'auth'])]
    public function create(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'program_id' => 'required|integer',
                'title' => 'required|string',
                'description' => 'string',
                'benefits' => 'required|string',
                'code' => 'string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'status' => 'required|string|in:upcoming,ongoing,completed',
                'capacity' => 'integer',
                'delivery_mode' => 'string',
                'zoom_link' => 'string',
                'is_free' => 'required|boolean',
                'trial_type' => 'required_if:is_free,true|in:views,days',
                'trial_value' => 'required_if:is_free,true|integer',

                'image' => 'required|array',
                'image.name' => 'required|string',
                'image.tmp_name' => 'required|string',
                'image.error' => 'required|integer',
                'image.size' => 'required|integer'
            ]
        );

        $id =  $this->cohortService->addCohortToProgramCourse($validatedData);

        if (!$id) {
            $this->respondError(
                'Failed to create program course cohort.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program course cohort created successfully.',
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
                'id' => 'required|integer',
                'title' => 'required|string',
                'description' => 'string',
                'benefits' => 'required|string',
                'code' => 'string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'status' => 'required|string|in:upcoming,ongoing,completed',
                'capacity' => 'integer',
                'delivery_mode' => 'string',
                'zoom_link' => 'string',
                'is_free' => 'required|boolean',
                'trial_type' => 'required_if:is_free,true|in:views,days',
                'trial_value' => 'required_if:is_free,true|integer',

                'image' => 'array',
                'image.name' => 'string',
                'image.tmp_name' => 'string',
                'image.error' => 'integer',
                'image.size' => 'integer'
            ]
        );

        $updated =  $this->cohortService->updateProgramCourseCohort($validatedData);

        if (!$updated) {
            $this->respondError(
                'Failed to update program course cohort.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program course cohort updated successfully.'
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}/status', 'POST', ['api', 'auth'])]
    public function updateStatus(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'status' => 'required|string|in:upcoming,ongoing,completed'
            ]
        );

        $updated =  $this->cohortService
            ->updateStatus(
                (int)$validatedData['id'],
                $validatedData['status']
            );

        if (!$updated) {
            $this->respondError(
                'Failed to update program course cohort status.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program course cohort status updated successfully.'
            ],
            HttpStatus::OK
        );
    }

    #[Route('', 'GET', ['api', 'auth'])]
    public function getAllCohortsByCourseId(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'course_id' => 'required|integer',
            ]
        );

        $cohorts = $this->cohortService
            ->getAllCohortsByCourseId((int)$validatedData['course_id']);

        $this->respond(
            [
                'success' => true,
                'data' => $cohorts
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}', 'DELETE', ['api', 'auth'])]
    public function delete(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'id' => 'required|integer',
            ]
        );

        $deleted = $this->cohortService->deleteCohort((int)$validatedData['id']);

        if (!$deleted) {
            $this->respondError(
                'Failed to delete program course cohort.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program course cohort deleted successfully.'
            ],
            HttpStatus::OK
        );
    }
}
