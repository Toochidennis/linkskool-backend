<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProgramCourseCohortService;
use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;

#[Group('/public')]
class ProgramCourseCohortController extends ExploreBaseController
{
    protected ProgramCourseCohortService $cohortService;

    public function __construct()
    {
        parent::__construct();
        $this->cohortService = new ProgramCourseCohortService($this->pdo);
    }

    #[Route('/learn/programs/courses/cohorts', 'POST', ['api', 'auth'])]
    public function create(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'program_id' => 'required|integer',
                'title' => 'required|string',
                'description' => 'nullable|string',
                'benefits' => 'required|string',
                'code' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|string|in:upcoming,ongoing,completed',
                'capacity' => 'nullable|integer',
                'delivery_mode' => 'nullable|string|in:virtual,onsite,hybrid',
                'zoom_link' => 'nullable|string',
                'is_free' => 'required|boolean',
                'trial_type' => 'required_if:is_free,false|in:views,days',
                'trial_value' => 'required_if:is_free,false|integer',
                'cost' => 'required_if:is_free,false|numeric',

                'image' => 'required|array',
                'image.name' => 'required|string',
                'image.tmp_name' => 'required|string',
                'image.error' => 'required|integer',
                'image.size' => 'required|integer|max:2097152' // 2 MB
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

    #[Route('/learn/programs/courses/cohorts/{id}', 'POST', ['api', 'auth'])]
    public function update(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'title' => 'required|string',
                'description' => 'nullable|string',
                'benefits' => 'required|string',
                'code' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|string|in:upcoming,ongoing,completed',
                'capacity' => 'nullable|integer',
                'delivery_mode' => 'nullable|string|in:virtual,onsite,hybrid',
                'zoom_link' => 'nullable|string',
                'is_free' => 'required|boolean',
                'trial_type' => 'required_if:is_free,false|in:views,days',
                'trial_value' => 'required_if:is_free,false|integer',
                'cost' => 'required_if:is_free,false|numeric',

                'image' => 'array',
                'image.name' => 'string',
                'image.tmp_name' => 'string',
                'image.error' => 'integer',
                'image.size' => 'integer|max:2097152' // 2 MB
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

    #[Route('/learn/programs/courses/cohorts/{id}/status', 'PUT', ['api', 'auth'])]
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

    #[Route('/learn/programs/{program_id}/courses/{course_id}/cohorts', 'GET', ['api', 'auth'])]
    public function getAllCohortsByCourseId(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'course_id' => 'required|integer',
                'program_id' => 'required|integer',
            ]
        );

        $cohorts = $this->cohortService
            ->getAllCohortsByCourseId(
                (int)$validatedData['program_id'],
                (int)$validatedData['course_id']
            );

        $this->respond(
            [
                'success' => true,
                'data' => $cohorts
            ],
            HttpStatus::OK
        );
    }

    #[Route('/learn/programs/courses/cohorts/{id}', 'DELETE', ['api', 'auth'])]
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
