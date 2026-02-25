<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Email\CohortCourseEnrolled;
use V3\App\Models\Explore\ProgramCohortCourseEnrollment;
use V3\App\Models\Portal\Payments\Transaction;

class CourseCohortEnrollmentService
{
    protected ProgramCohortCourseEnrollment $enrollmentModel;
    private Transaction $transactionModel;
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->enrollmentModel = new ProgramCohortCourseEnrollment($pdo);
        $this->transactionModel = new Transaction($pdo);
    }

    public function enrollUser(array $data): bool
    {
        $hasEnrolled = $this->isUserEnrolled($data);

        if ($hasEnrolled) {
            return true;
        }

        $payload = [
            'profile_id' => $data['profile_id'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'] ?? null,
            'cohort_name' => $data['cohort_name'],
            'cohort_id' => $data['cohort_id'],
            'program_id' => $data['program_id'],
            'enrollment_type' => $data['enrollment_type'],
            'trial_expiry_date' => $data['trial_expiry_date'] ?? null,
        ];

        $enrollmentId = $this->enrollmentModel->insert($payload);

        if ($enrollmentId) {
            EventDispatcher::dispatch(
                new CohortCourseEnrolled(
                    (int) $data['profile_id'],
                    (int) $data['program_id'],
                    (int) $data['course_id'],
                    (int) $data['cohort_id'],
                    $data['course_name'] ?? null,
                    $data['cohort_name'] ?? null
                )
            );
        }

        return (bool) $enrollmentId;
    }

    public function unEnrollUser(array $data): bool
    {
        return $this->enrollmentModel
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->delete();
    }

    public function isUserEnrolled(array $data): bool
    {
        return $this->enrollmentModel
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->exists();
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

    public function updateLessonCount(array $data)
    {
        return $this->enrollmentModel
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->update(['lessons_taken' => $data['lessons_taken']]);
    }

    public function verifyAndRecordPayment(array $data): array
    {
        $paystackService = new \V3\App\Services\Paystack\PaystackService();
        $verification = $paystackService->verify($data['reference']);
        $status = 0;
        $message = 'Cohort payment verification failed.';

        if (!$verification['success']) {
            $message = 'Payment verification failed: ' . $verification['message'];
        } else {
            $expectedAmount = (float) $data['amount'];
            $verifiedAmount = (float) $verification['amount'];

            if (abs($verifiedAmount - $expectedAmount) > 0.01) {
                $message = 'Payment amount mismatch';
            } elseif (\in_array($verification['status'], ['failed', 'abandoned'], true)) {
                $message = 'Payment was not successful: ' . $verification['status'];
            } elseif ($verification['status'] === 'pending') {
                $status = 2;
                $message = 'Payment is still pending';
            } else {
                $status = 1;
                $message = 'Payment verified successfully';
            }
        }

        $paymentStatus = $this->mapPaymentStatus($status);
        $payload = $this->buildTransactionPayload($data, $status, $message);

        $this->pdo->beginTransaction();

        try {
            $existing = $this->transactionModel
                ->where('ref', $data['reference'])
                ->first();

            if (!empty($existing)) {
                $this->transactionModel
                    ->where('ref', $data['reference'])
                    ->update($payload);
            } else {
                $this->transactionModel->insert($payload);
            }

            $enrollment = $this->enrollmentModel
                ->where('profile_id', $data['profile_id'])
                ->where('cohort_id', $data['cohort_id'])
                ->first();

            if (!empty($enrollment)) {
                $this->enrollmentModel
                    ->where('profile_id', $data['profile_id'])
                    ->where('cohort_id', $data['cohort_id'])
                    ->update([
                        'payment_status' => $paymentStatus,
                        'payment_reference' => $data['reference'],
                        'lessons_taken' => (int) ($data['lessons_taken'] ?? null),
                        'trial_expiry_date' => $data['trial_expiry_date'] ?? null,
                    ]);
            } else {
                $enrollmentInserted = $this->enrollmentModel->insert([
                    'profile_id' => $data['profile_id'],
                    'program_id' => $data['program_id'],
                    'course_id' => $data['course_id'],
                    'course_name' => $data['course_name'] ?? null,
                    'cohort_id' => $data['cohort_id'],
                    'cohort_name' => $data['cohort_name'] ?? null,
                    'enrollment_type' => $data['enrollment_type'] ?? 'paid',
                    'status' => $data['status'] ?? 'active',
                    'payment_status' => $paymentStatus,
                    'payment_reference' => $data['reference'],
                    'lessons_taken' => $data['lessons_taken'] ?? null,
                    'trial_expiry_date' => $data['trial_expiry_date'] ?? null,
                ]);

                if ($enrollmentInserted && $status === 1) {
                    EventDispatcher::dispatch(
                        new CohortCourseEnrolled(
                            (int) $data['profile_id'],
                            (int) $data['program_id'],
                            (int) $data['course_id'],
                            (int) $data['cohort_id'],
                            $data['course_name'] ?? null,
                            $data['cohort_name'] ?? null
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
            'success' => $status === 1,
            'status' => $status,
            'message' => $message,
        ];
    }

    private function buildTransactionPayload(array $data, int $status, string $message): array
    {
        $metadata = [
            'program_id' => $data['program_id'],
            'course_id' => $data['course_id'],
            'cohort_id' => $data['cohort_id'],
            'lessons_taken' => $data['lessons_taken'] ?? null,
            'enrollment_type' => $data['enrollment_type'],
        ];

        return [
            'trans_type' => 'receipts',
            'memo' => $message,
            'ref' => $data['reference'],
            'cid' => $data['profile_id'],
            'cref' => $data['profile_id'],
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'quantity' => 1,
            'it_id' => $data['course_id'],
            'it_type' => 'course_cohort',
            'description' => json_encode($metadata),
            'amount' => (float) $data['amount'],
            'date' => date('Y-m-d'),
            'account' => 1980,
            'account_name' => 'Income',
            'approved' => 1,
            'status' => $status,
            'sub' => 0,
        ];
    }

    private function mapPaymentStatus(int $status): string
    {
        return match ($status) {
            1 => 'paid',
            2 => 'pending',
            default => 'failed',
        };
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
            'is_paid' => $status === 'paid',
        ];
    }
}
