<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CourseCohortEnrollmentService;

#[Group('/public/learning/cohorts')]
class CourseCohortEnrollmentController extends ExploreBaseController
{
    private CourseCohortEnrollmentService $enrollmentService;

    public function __construct()
    {
        parent::__construct();
        $this->enrollmentService = new CourseCohortEnrollmentService($this->pdo);
    }

    #[Route('/{cohort_id}/enrollments', 'POST', ['api'])]
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
                'enrollment_type' => 'nullable|string|in:free,paid,trial',
            ],
        );

        try {
            $result = $this->enrollmentService->enrollOrResolveNextAction($validated);
        } catch (\InvalidArgumentException $e) {
            $this->respondError(
                $e->getMessage(),
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => $result['message'],
                'data' => $result['data'],
            ],
            ($result['created'] ?? false) ? HttpStatus::CREATED : HttpStatus::OK
        );
    }

    // #[Route('', 'DELETE', ['api'])]
    // public function unEnrollUser(array $vars): void
    // {
    //     $validated = $this->validate(
    //         [...$this->getRequestData(), ...$vars],
    //         [
    //             'profile_id' => 'required|integer',
    //             'cohort_id' => 'required|integer',
    //         ],
    //     );

    //     $success = $this->enrollmentService->unEnrollUser($validated);

    //     if (!$success) {
    //         $this->respondError('Unenrollment failed.', HttpStatus::BAD_REQUEST);
    //     }

    //     $this->respond(
    //         [
    //             'status' => true,
    //             'message' => 'User unenrolled successfully.',
    //         ],
    //         HttpStatus::OK
    //     );
    // }

    #[Route('/{cohort_id}/enrollments/is-enrolled', 'GET', ['api'])]
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

    #[Route('/{cohort_id}/enrollments/status', 'PUT', ['api'])]
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

    #[Route('/{cohort_id}/enrollments/payment', 'POST', ['api'])]
    public function verifyPayment(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'profile_id' => 'required|integer',
                'program_id' => 'nullable|integer',
                'course_id' => 'nullable|integer',
                'cohort_id' => 'nullable|integer',
                'course_name' => 'nullable|string',
                'cohort_name' => 'nullable|string',
                'payment_item' => 'nullable|array',
                'payment_item.program_id' => 'required_without:program_id|integer',
                'payment_item.course_id' => 'required_without:course_id|integer',
                'payment_item.cohort_id' => 'required_without:cohort_id|integer',
                'payment_item.course_name' => 'nullable|string',
                'payment_item.cohort_name' => 'nullable|string',
                'amount' => 'nullable|numeric|min:0.01',
                'reference' => 'required|string',
                'lessons_taken' => 'nullable|integer|min:0',
                'method' => 'nullable|string',
                'platform' => 'nullable|string',
                'first_name' => 'nullable|string',
                'last_name' => 'nullable|string',
                'enrollment_type' => 'required|string|in:free,paid,trial',
            ],
        );

        try {
            $result = $this->enrollmentService->verifyAndRecordPayment($validated);
        } catch (\InvalidArgumentException $e) {
            $this->respondError(
                $e->getMessage(),
                HttpStatus::BAD_REQUEST
            );
        }

        if (!$result['success'] && $result['status'] === 'failed') {
            $this->respondError(
                $result['message'],
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => $result['status'] === 'success',
                'message' => $result['message'],
                'data' => [
                    'payment_status' => $result['status'],
                ],
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{cohort_id}/enrollments/payment-status', 'GET', ['api'])]
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

    #[Route('/{cohort_id}/enrollments/lessons-taken', 'PUT', ['api'])]
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

    #[Route('/{cohort_id}/enrollments/mobile-payment', 'POST', ['api'])]
    public function initiateMobilePayment(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'profile_id' => 'required|integer',
                'email' => 'required|email',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
                'cohort_id' => 'required|integer',
            ]
        );

        $res = $this->enrollmentService->initiateMobilePayment($validated);

        if ($res['status'] === 'blocked') {
            $this->respond(
                [
                    'success' => false,
                    'message' => $res['message'],
                ],
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        if ($res['status'] === 'failed') {
            $this->respondError(
                $res['message'],
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Mobile payment initiated successfully.',
                'data' => $res,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/enrollments/checkout', 'POST', ['api'])]
    public function checkout()
    {
        $validated = $this->validate(
            $this->getRequestData(),
            [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'program_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.course_id' => 'required|integer',
                'items.*.cohort_id' => 'required|integer',
                'callback_url' => 'nullable|string'
            ]
        );

        $res = $this->enrollmentService->initiateWebPayment($validated);

        if ($res['status'] === 'blocked') {
            $this->respond(
                [
                    'success' => false,
                    'message' => $res['message'],
                ],
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        if ($res['status'] === 'failed') {
            $this->respondError(
                $res['message'],
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Payment initiated successfully.',
                'data' => $res,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/enrollments/checkout/offline', 'POST', ['api'])]
    public function offlineCheckout()
    {
        $validated = $this->validate(
            $this->getRequestData(),
            [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'program_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.course_id' => 'required|integer',
                'items.*.cohort_id' => 'required|integer',
                'callback_url' => 'nullable|string'
            ]
        );

        $res = $this->enrollmentService->completeOfflinePayment($validated);

        if ($res['status'] === 'blocked') {
            $this->respond(
                [
                    'success' => false,
                    'message' => $res['message'],
                ],
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        if ($res['status'] === 'failed') {
            $this->respondError(
                $res['message'],
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Offline payment completed successfully.',
                'data' => $res,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/enrollments/checkout/{reference}/status', 'GET', ['api'])]
    public function checkPaymentStatus(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'reference' => 'required|string'
            ]
        );

        $this->respond(
            [
                'success' => true,
                'message' => 'Payment status retrieved successfully.',
                'data' => $this->enrollmentService->checkPaymentStatus($validated['reference'])
            ]
        );
    }

    #[Route('/enrollments/reserve', 'POST', ['api'])]
    public function reserve()
    {
        $validated = $this->validate(
            $this->getRequestData(),
            [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'program_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.course_id' => 'required|integer',
                'items.*.cohort_id' => 'required|integer'
            ]
        );

        $res = $this->enrollmentService->reserve($validated);

        if (!$res) {
            $this->respondError(
                'Reservation failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Reservation completed successfully.',
                'data' => $res,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/enrollments/free', 'POST', ['api'])]
    public function freeEnroll()
    {
        $validated = $this->validate(
            $this->getRequestData(),
            [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'program_id' => 'required|integer',
                'items' => 'required|array|min:1',
                'items.*.course_id' => 'required|integer',
                'items.*.cohort_id' => 'required|integer'
            ]
        );

        $res = $this->enrollmentService->freeEnroll($validated);

        if (!$res) {
            $this->respondError(
                'Free enrollment failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Free enrollment completed successfully.',
                'data' => $res,
            ],
            HttpStatus::OK
        );
    }
}
