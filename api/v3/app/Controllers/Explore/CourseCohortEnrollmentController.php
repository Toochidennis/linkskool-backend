<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CourseCohortEnrollmentService;

#[Group('/public/learning/cohorts/{cohort_id}/enrollments')]
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
                'profile_id' => 'required|integer',
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'cohort_name' => 'required|string',
                'program_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'enrollment_type' => 'required|string|in:free,paid,trial',
                'trial_expiry_date' => 'nullable|date',
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
                'profile_id' => 'required|integer',
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
                'profile_id' => 'required|integer',
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

    #[Route('/status', 'PUT', ['api'])]
    public function updateStatus(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'profile_id' => 'required|integer',
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

    #[Route('/payment', 'POST', ['api'])]
    public function verifyPayment(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'profile_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'course_name' => 'nullable|string',
                'cohort_name' => 'nullable|string',
                'amount' => 'required|numeric|min:0.01',
                'reference' => 'required|string',
                'lessons_taken' => 'nullable|integer|min:0',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'enrollment_type' => 'required|string|in:free,paid,trial',
                'trial_expiry_date' => 'nullable|date',
            ],
        );

        $result = $this->enrollmentService->verifyAndRecordPayment($validated);

        if (!$result['success'] && $result['status'] === 0) {
            $this->respondError(
                $result['message'],
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => $result['status'] === 1,
                'message' => $result['message'],
                'data' => [
                    'payment_status' => $result['status'],
                ],
            ],
            HttpStatus::OK
        );
    }

    #[Route('/payment-status', 'GET', ['api'])]
    public function getPaymentStatus(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'profile_id' => 'required|integer',
                'cohort_id' => 'required|integer',
            ],
        );

        $status = $this->enrollmentService->getPaymentStatus($validated);

        $this->respond(
            [
                'status' => true,
                'data' => $status,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/lessons-taken', 'PUT', ['api'])]
    public function updateLessonsTaken(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'profile_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'lessons_taken' => 'required|integer|min:0',
            ],
        );

        $success = $this->enrollmentService->updateLessonCount($validated);

        if (!$success) {
            $this->respondError('Updating lessons taken failed.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'Lessons taken updated successfully.',
            ],
            HttpStatus::OK
        );
    }
}
