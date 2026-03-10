<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Email\CohortCourseEnrolled;
use V3\App\Models\Explore\CourseCohortPayment;
use V3\App\Models\Explore\CourseCohortPaymentItem;
use V3\App\Models\Explore\ProgramCohortCourseEnrollment;
use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Services\Paystack\PaystackService;

class CourseCohortEnrollmentService
{
    protected ProgramCohortCourseEnrollment $enrollmentModel;
    private ProgramCourseCohort $programCourseCohort;
    private CourseCohortPayment $payment;
    private CourseCohortPaymentItem $paymentItem;
    private AuthBootstrapService $authBootstrapService;
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->enrollmentModel = new ProgramCohortCourseEnrollment($pdo);
        $this->payment = new CourseCohortPayment($pdo);
        $this->paymentItem = new CourseCohortPaymentItem($pdo);
        $this->programCourseCohort = new ProgramCourseCohort($pdo);
        $this->authBootstrapService = new AuthBootstrapService($pdo);
    }

    public function enrollUser(array $data): bool
    {
        if ($this->isUserEnrolled($data)) {
            return true;
        }

        $payload = [
            'profile_id' => $data['profile_id'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'] ?? null,
            'cohort_name' => $data['cohort_name'] ?? null,
            'cohort_id' => $data['cohort_id'],
            'program_id' => $data['program_id'],
            'enrollment_type' => $data['enrollment_type'],
        ];

        $enrollmentId = $this->enrollmentModel->insert($payload);

        if ($enrollmentId) {
            EventDispatcher::dispatch(
                new CohortCourseEnrolled(
                    (int)$data['profile_id'],
                    (int)$data['program_id'],
                    (int)$data['course_id'],
                    (int)$data['cohort_id'],
                    $data['course_name'] ?? null,
                    $data['cohort_name'] ?? null
                )
            );
        }

        return (bool)$enrollmentId;
    }

    public function isUserEnrolled(array $data): bool
    {
        return $this->enrollmentModel
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->exists();
    }

    public function initiateMobilePayment(array $data): array
    {
        $reference = 'MOB-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $amount = $this->computePrice($data['cohort_id']);

        $paymentId = $this->payment->insert([
            'profile_id' => $data['profile_id'],
            'reference' => $reference,
            'amount' => $amount,
            'status' => 'pending',
            'method' => 'paystack',
            'platform' => 'mobile'
        ]);

        $this->paymentItem->insert([
            'payment_id' => $paymentId,
            'program_id' => $data['program_id'],
            'course_id' => $data['course_id'],
            'cohort_id' => $data['cohort_id'],
            'amount' => $amount
        ]);

        $paystack = new PaystackService();

        $payment = $paystack->initialize([
            'email' => $data['email'],
            'amount' => $amount,
            'reference' => $reference,
            'metadata' => [
                'payment_id' => $paymentId,
                'payment_type' => 'course',
            ]
        ]);

        return [
            'status' => 'pending',
            'payment_url' => $payment['authorization_url'],
            'reference' => $reference
        ];
    }

    public function initiateWebPayment(array $data): array
    {
        $user = $this->registerUserIfNotExists($data);

        if (empty($user) || empty($user['profiles'])) {
            return [
                'status' => 'failed',
                'message' => 'User registration failed'
            ];
        }

        $data['profile_id'] = $user['profiles'][0]['id'];
        $reference = 'WEB-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));

        $this->pdo->beginTransaction();

        try {
            $total = 0;

            foreach ($data['courses'] as $course) {
                $total += $this->computePrice($course['cohort_id']);
            }

            $paymentId = $this->payment->insert([
                'profile_id' => $data['profile_id'],
                'reference' => $reference,
                'amount' => $total,
                'status' => 'pending',
                'method' => 'online',
                'platform' => 'web'
            ]);

            foreach ($data['courses'] as $course) {
                $price = $this->computePrice($course['cohort_id']);

                $this->paymentItem->insert([
                    'payment_id' => $paymentId,
                    'program_id' => $course['program_id'],
                    'course_id' => $course['course_id'],
                    'cohort_id' => $course['cohort_id'],
                    'amount' => $price
                ]);
            }

            $paystack = new PaystackService();

            $payment = $paystack->initialize([
                'email' => $data['email'],
                'amount' => $total,
                'reference' => $reference,
                'metadata' => [
                    'payment_id' => $paymentId,
                    'payment_type' => 'course',
                ]
            ]);

            $this->pdo->commit();

            return [
                'status' => 'pending',
                'payment_url' => $payment['authorization_url'],
                'reference' => $reference
            ];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();

            return [
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }

    public function handlePaymentWebhook(string $reference): array
    {
        $paystack = new PaystackService();

        $verification = $paystack->verify($reference);

        if (!$verification['success'] || $verification['status'] !== 'success') {
            return [
                'status' => 'failed'
            ];
        }

        $payment = $this->payment
            ->where('reference', $reference)
            ->first();

        if (!$payment) {
            return ['status' => 'failed'];
        }

        if ($payment['status'] === 'success') {
            return ['status' => 'success'];
        }

        $items = $this->paymentItem
            ->where('payment_id', $payment['id'])
            ->get();

        $this->pdo->beginTransaction();

        try {
            $this->payment
                ->where('id', $payment['id'])
                ->update(['status' => 'success']);

            foreach ($items as $item) {
                $this->enrollUser([
                    'profile_id' => $payment['profile_id'],
                    'program_id' => $item['program_id'],
                    'course_id' => $item['course_id'],
                    'cohort_id' => $item['cohort_id'],
                    'enrollment_type' => 'paid'
                ]);
            }

            $this->pdo->commit();

            return ['status' => 'success'];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();

            return [
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }

    public function verifyAndRecordPayment(array $data): array
    {
        $paymentItem = $this->resolvePaymentItem($data);
        $existingPayment = $this->payment
            ->where('reference', $data['reference'])
            ->first();

        $paystackService = new PaystackService();
        $status = 'failed';
        $message = 'Payment verification failed.';

        try {
            $verification = $paystackService->verify($data['reference']);
        } catch (\Throwable $e) {
            $verification = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        if (!$verification['success']) {
            $message = 'Payment verification failed: ' . $verification['message'];
        } else {
            $expectedAmount = $this->resolveExpectedAmount($data, $paymentItem, $existingPayment);
            $verifiedAmount = (int) ($verification['amount_kobo'] ?? 0);

            if ($verifiedAmount !== $expectedAmount) {
                $message = 'Payment amount mismatch';
            } elseif (\in_array($verification['status'], ['failed', 'abandoned'], true)) {
                $message = 'Payment was not successful: ' . $verification['status'];
            } elseif ($verification['status'] === 'pending') {
                $status = 'pending';
                $message = 'Payment is still pending';
            } else {
                $status = 'success';
                $message = 'Payment verified successfully';
            }
        }

        $payload = $this->buildTransactionPayload($data, $status, $message);

        $this->pdo->beginTransaction();

        try {
            if (!empty($existingPayment)) {
                $this->payment
                    ->where('reference', $data['reference'])
                    ->update($payload);
                $paymentId = (int) $existingPayment['id'];
            } else {
                $paymentId = (int) $this->payment->insert($payload);
            }

            $enrollment = $this->enrollmentModel
                ->where('profile_id', $data['profile_id'])
                ->where('cohort_id', $paymentItem['cohort_id'])
                ->first();

            $existingPaymentItem = $this->paymentItem
                ->where('payment_id', $paymentId)
                ->where('program_id', $paymentItem['program_id'])
                ->where('course_id', $paymentItem['course_id'])
                ->where('cohort_id', $paymentItem['cohort_id'])
                ->first();

            $paymentItemPayload = [
                'payment_id' => $paymentId,
                'program_id' => $paymentItem['program_id'],
                'course_id' => $paymentItem['course_id'],
                'cohort_id' => $paymentItem['cohort_id'],
                'amount' => $payload['amount'],
            ];

            if (!empty($existingPaymentItem)) {
                $this->paymentItem
                    ->where('id', $existingPaymentItem['id'])
                    ->update($paymentItemPayload);
            } else {
                $this->paymentItem->insert($paymentItemPayload);
            }

            if (!empty($enrollment)) {
                $this->enrollmentModel
                    ->where('profile_id', $data['profile_id'])
                    ->where('cohort_id', $paymentItem['cohort_id'])
                    ->update([
                        'payment_status' => $status,
                        'payment_reference' => $data['reference'],
                        'lessons_taken' => (int) ($data['lessons_taken'] ?? null),
                        'trial_expiry_date' => $data['trial_expiry_date'] ?? null,
                    ]);
            } else {
                $enrollmentInserted = $this->enrollmentModel->insert([
                    'profile_id' => $data['profile_id'],
                    'program_id' => $paymentItem['program_id'],
                    'course_id' => $paymentItem['course_id'],
                    'course_name' => $paymentItem['course_name'] ?? null,
                    'cohort_id' => $paymentItem['cohort_id'],
                    'cohort_name' => $paymentItem['cohort_name'] ?? null,
                    'enrollment_type' => $data['enrollment_type'] ?? 'paid',
                    'status' => 'active',
                    'payment_status' => $status,
                    'payment_reference' => $data['reference'],
                    'lessons_taken' => $data['lessons_taken'] ?? null,
                    'trial_expiry_date' => $data['trial_expiry_date'] ?? null,
                ]);

                if ($enrollmentInserted && $status === 'success') {
                    EventDispatcher::dispatch(
                        new CohortCourseEnrolled(
                            (int) $data['profile_id'],
                            (int) $paymentItem['program_id'],
                            (int) $paymentItem['course_id'],
                            (int) $paymentItem['cohort_id'],
                            $paymentItem['course_name'] ?? null,
                            $paymentItem['cohort_name'] ?? null
                        )
                    );
                }
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return [
                'success' => false,
                'status' => $status,
                'message' => 'Failed to record payment: ' . $e->getMessage(),
            ];
        }

        return [
            'success' => $status === 'success',
            'status' => $status,
            'message' => $message,
        ];
    }

    private function buildTransactionPayload(array $data, int $status, string $message): array
    {
        return [
            'reference' => $data['reference'],
            'profile_id' => $data['profile_id'],
            'amount' => $this->resolveExpectedAmount($data, $this->resolvePaymentItem($data)),
            'status' => $status,
            'method' => $data['method'] ?? 'online',
            'platform' => $data['platform'] ?? 'mobile',
            'message' => $message,
        ];
    }

    private function resolvePaymentItem(array $data): array
    {
        return [
            'program_id' => $data['program_id'],
            'course_id' => $data['course_id'],
            'cohort_id' => $data['cohort_id'],
            'course_name' => $data['course_name'],
            'cohort_name' => $data['cohort_name'],
        ];
    }

    private function resolveExpectedAmount(
        array $data,
        array $paymentItem,
        ?array $existingPayment = null
    ): int {
        if (!empty($existingPayment['amount'])) {
            return (int) $existingPayment['amount'];
        }

        if (!empty($paymentItem['cohort_id'])) {
            return $this->computePrice((int) $paymentItem['cohort_id']);
        }

        $amount = $data['amount'] ?? 0;

        if (\is_string($amount) && str_contains($amount, '.')) {
            return (int) round(((float) $amount) * 100);
        }

        if (\is_float($amount)) {
            return (int) round($amount * 100);
        }

        return (int) $amount;
    }

    public function getPaymentStatus(array $data): array
    {
        $enrollment = $this->enrollmentModel
            ->select(['payment_status'])
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->first();

        $status = $enrollment['payment_status'] ?? null;

        return [
            'exists' => !empty($enrollment),
            'payment_status' => $status,
            'is_paid' => $status === 'success',
        ];
    }

    private function computePrice(int $cohortId): int
    {
        $row = $this->programCourseCohort
            ->where('id', $cohortId)
            ->first();

        $price = (int)$row['cost'];
        $discount = $row['discount'] ?? null;

        if ($discount) {
            $price -= ($price * $discount / 100);
        }

        return $price * 100;
    }

    public function updateLessonCount(array $data)
    {
        return $this->enrollmentModel
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->update(['lessons_taken' => $data['lessons_taken']]);
    }

    private function registerUserIfNotExists(array $data)
    {
        return $this->authBootstrapService->bootstrap(
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'birth_date' => $data['birth_date'] ?? null
            ],
        );
    }

    public function updateStatus(array $filters): bool
    {
        $payload = [
            'status' => $filters['status'],
        ];

        return $this->enrollmentModel
            ->where('profile_id', $filters['profile_id'])
            ->where('cohort_id', $filters['cohort_id'])
            ->update($payload);
    }
}
