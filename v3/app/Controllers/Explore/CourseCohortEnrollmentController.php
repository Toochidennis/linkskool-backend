<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CourseCohortEnrollmentService;

#[Group('/public/cohorts/{cohortId}/enrollments')]
class CourseCohortEnrollmentController extends ExploreBaseController
{
    private CourseCohortEnrollmentService $enrollmentService;

    public function __construct()
    {
        parent::__construct();
        $this->enrollmentService = new CourseCohortEnrollmentService($this->pdo);
    }

    #[Route('', 'POST', ['api'])]
    public function enrollUser(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'user_id' => 'required|integer',
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'cohort_name' => 'required|string',
                'program_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'enrollment_type' => 'required|string|in:free,paid,trial',
            ],
        );

        $enrollmentId = $this->enrollmentService->enrollUser($validated);

        if (!$enrollmentId) {
            $this->respondError(
                'Enrollment failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'User enrolled successfully.',
                'data' => $enrollmentId
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('', 'DELETE', ['api'])]
    public function unEnrollUser(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'user_id' => 'required|integer',
                'cohort_id' => 'required|integer',
            ],
        );

        $success = $this->enrollmentService->unEnrollUser($validated);

        if (!$success) {
            $this->respondError('Unenrollment failed.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'User unenrolled successfully.',
            ],
            HttpStatus::OK
        );
    }

    #[Route('/is-enrolled', 'GET', ['api'])]
    public function isUserEnrolled(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'user_id' => 'required|integer',
                'cohort_id' => 'required|integer',
            ],
        );

        $isEnrolled = $this->enrollmentService->isUserEnrolled($validated);

        $this->respond(
            [
                'status' => true,
                'data' => ['is_enrolled' => $isEnrolled],
            ],
            HttpStatus::OK
        );
    }

    public function updateStatus(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'user_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'status' => 'required|string|in:active,completed,dropped',
            ],
        );

        $success = $this->enrollmentService->updateStatus($validated);

        if (!$success) {
            $this->respondError('Status update failed.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'Enrollment status updated successfully.',
            ],
            HttpStatus::OK
        );
    }
}
