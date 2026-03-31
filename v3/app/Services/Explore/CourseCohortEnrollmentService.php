<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Email\CohortCourseEnrolled;
use V3\App\Events\Email\CohortCoursesEnrolled;
use V3\App\Models\Explore\CourseCohortPayment;
use V3\App\Models\Explore\CourseCohortPaymentItem;
use V3\App\Models\Explore\LearningCourse;
use V3\App\Models\Explore\Program;
use V3\App\Models\Explore\ProgramCohortCourseEnrollment;
use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Services\Paystack\PaystackService;

class CourseCohortEnrollmentService
{
    protected ProgramCohortCourseEnrollment $enrollmentModel;
    private ProgramCourseCohort $programCourseCohort;
    private Program $programModel;
    private LearningCourse $learningCourseModel;
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
        $this->programModel = new Program($pdo);
        $this->learningCourseModel = new LearningCourse($pdo);
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

        if ($enrollmentId && ($data['send_notification'] ?? true)) {
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
        $existingEnrollment = $this->findExistingCourseEnrollment(
            (int) $data['profile_id'],
            (int) $data['program_id'],
            (int) $data['course_id']
        );

        if (
            $existingEnrollment !== null
            && !\in_array($existingEnrollment['enrollment_type'] ?? null, ['trial', 'reserved'], true)
        ) {
            return [
                'status' => 'blocked',
                'message' => $this->buildBlockedCheckoutMessage([
                    $this->buildBlockedCheckoutItem($data, $existingEnrollment),
                ]),
            ];
        }

        $reference = 'MOB-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $amount = $this->computePrice($data['cohort_id']);

        if ($amount <= 0) {
            return [
                'status' => 'failed',
                'message' => 'This course is free and should not be added to payment.',
            ];
        }

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
        $eligibility = $this->categorizeCheckoutItems(
            (int) $data['profile_id'],
            (int) $data['program_id'],
            $data['items']
        );

        if (!empty($eligibility['blocked_items'])) {
            return [
                'status' => 'blocked',
                'message' => $this->buildBlockedCheckoutMessage(
                    $eligibility['blocked_items'],
                    $eligibility['allowed_items']
                ),
                'payment_url' => '',
                'reference' => '',
                'payment_type' => '',
            ];
        }

        $reference = 'COURSE-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));

        $this->pdo->beginTransaction();

        try {
            $total = 0;
            $payableItems = [];

            foreach ($eligibility['allowed_items'] as $item) {
                $price = $this->computePrice((int) $item['cohort_id']);

                if ($price <= 0) {
                    continue;
                }

                $item['amount'] = $price;
                $payableItems[] = $item;
                $total += $price;
            }

            if (empty($payableItems) || $total <= 0) {
                return [
                    'status' => 'failed',
                    'message' => 'The selected courses are free and should not be added to payment.',
                ];
            }

            $paymentId = $this->payment->insert([
                'profile_id' => $data['profile_id'],
                'reference' => $reference,
                'amount' => $total,
                'status' => 'pending',
                'method' => 'online',
                'platform' => 'web'
            ]);

            foreach ($payableItems as $item) {
                $this->paymentItem->insert([
                    'payment_id' => $paymentId,
                    'program_id' => $data['program_id'],
                    'course_id' => $item['course_id'],
                    'cohort_id' => $item['cohort_id'],
                    'amount' => $item['amount']
                ]);
            }

            $paystack = new PaystackService();

            $payment = $paystack->initialize([
                'email' => $data['email'],
                'amount' => $total,
                'reference' => $reference,
                'callback_url' => $data['callback_url'],
                'metadata' => [
                    'payment_id' => $paymentId,
                    'payment_type' => 'course',
                ]
            ]);

            $this->pdo->commit();

            return [
                'status' => 'pending',
                'payment_url' => $payment['authorization_url'],
                'reference' => $reference,
                'message' => 'Payment initialized successfully'
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

        if ((int)$verification['amount_kobo'] !== (int)$payment['amount']) {
            return ['status' => 'failed'];
        }

        $items = $this->paymentItem
            ->where('payment_id', $payment['id'])
            ->get();

        $this->pdo->beginTransaction();

        try {
            $this->payment
                ->where('id', $payment['id'])
                ->update(['status' => 'success']);

            $enrolledItems = [];

            foreach ($items as $item) {
                $enrolled = $this->markEnrollmentAsPaidOrCreate([
                    'profile_id' => (int) $payment['profile_id'],
                    'program_id' => (int) $item['program_id'],
                    'course_id' => (int) $item['course_id'],
                    'cohort_id' => (int) $item['cohort_id'],
                    'payment_reference' => $reference,
                ]);

                if ($enrolled) {
                    $enrolledItems[] = $this->buildEnrollmentNotificationItem($item);
                }
            }

            if (\count($enrolledItems) === 1) {
                $item = $enrolledItems[0];

                EventDispatcher::dispatch(
                    new CohortCourseEnrolled(
                        (int) $payment['profile_id'],
                        (int) $item['program_id'],
                        (int) $item['course_id'],
                        (int) $item['cohort_id'],
                        $item['course_name'],
                        $item['cohort_name']
                    )
                );
            } elseif (\count($enrolledItems) > 1) {
                EventDispatcher::dispatch(
                    new CohortCoursesEnrolled(
                        (int) $payment['profile_id'],
                        $enrolledItems
                    )
                );
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
                $updatePayload = [
                    'payment_status' => $status,
                    'payment_reference' => $data['reference'],
                    'lessons_taken' => (int) ($data['lessons_taken'] ?? null),
                    'trial_expiry_date' => $data['trial_expiry_date'] ?? null,
                ];

                if ($status === 'success' && \in_array($enrollment['enrollment_type'] ?? null, ['trial', 'reserved'], true)) {
                    $updatePayload['enrollment_type'] = 'paid';
                    $updatePayload['status'] = 'active';
                }

                $this->enrollmentModel
                    ->where('profile_id', $data['profile_id'])
                    ->where('cohort_id', $paymentItem['cohort_id'])
                    ->update($updatePayload);
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

    private function categorizeCheckoutItems(int $profileId, int $programId, array $items): array
    {
        $blockedItems = [];
        $allowedItems = [];

        foreach ($items as $item) {
            $existingEnrollment = $this->findExistingCourseEnrollment(
                $profileId,
                $programId,
                (int) $item['course_id']
            );

            if ($existingEnrollment === null) {
                $allowedItems[] = $item;
                continue;
            }

            if (\in_array($existingEnrollment['enrollment_type'] ?? null, ['trial', 'reserved'], true)) {
                $allowedItems[] = $item;
                continue;
            }

            $blockedItems[] = $this->buildBlockedCheckoutItem($item, $existingEnrollment);
        }

        return [
            'allowed_items' => $allowedItems,
            'blocked_items' => $blockedItems,
        ];
    }

    private function buildTransactionPayload(array $data, string $status, string $message): array
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

    private function buildEnrollmentNotificationItem(array $item): array
    {
        $cohort = $this->programCourseCohort
            ->where('id', $item['cohort_id'])
            ->first();

        $programId = (int) ($item['program_id'] ?? $cohort['program_id'] ?? 0);
        $courseId = (int) ($item['course_id'] ?? $cohort['course_id'] ?? 0);

        $program = $programId > 0
            ? $this->programModel->where('id', $programId)->first()
            : [];
        $course = $courseId > 0
            ? $this->learningCourseModel->where('id', $courseId)->first()
            : [];

        return [
            'program_id' => $programId,
            'course_id' => $courseId,
            'cohort_id' => (int) $item['cohort_id'],
            'program_name' => $program['name'] ?? null,
            'course_name' => $course['title'] ?? null,
            'cohort_name' => $item['cohort_name'] ?? ($cohort['title'] ?? null),
        ];
    }

    private function buildBlockedCheckoutItem(array $item, array $existingEnrollment): array
    {
        $notificationItem = $this->buildEnrollmentNotificationItem($item);

        return [
            'program_id' => (int) ($item['program_id'] ?? $notificationItem['program_id'] ?? 0),
            'course_id' => (int) $item['course_id'],
            'cohort_id' => (int) $item['cohort_id'],
            'course_name' => $notificationItem['course_name'] ?? null,
            'cohort_name' => $notificationItem['cohort_name'] ?? null,
            'existing_enrollment_type' => $existingEnrollment['enrollment_type'] ?? null,
            'existing_payment_status' => $existingEnrollment['payment_status'] ?? null,
            'message' => 'User is already enrolled in this course.',
        ];
    }

    private function findExistingCourseEnrollment(int $profileId, int $programId, int $courseId): ?array
    {
        $enrollment = $this->enrollmentModel
            ->where('profile_id', $profileId)
            ->where('program_id', $programId)
            ->where('course_id', $courseId)
            ->first();

        return !empty($enrollment) ? $enrollment : null;
    }

    private function buildBlockedCheckoutMessage(array $blockedItems, array $allowedItems = []): string
    {
        $blockedCourseNames = array_values(array_filter(array_unique(array_map(
            static fn(array $item) => trim((string) ($item['course_name'] ?? '')),
            $blockedItems
        ))));

        if (empty($blockedCourseNames)) {
            return 'You are already enrolled in one or more selected courses. Remove them and try again.';
        }

        $blockedList = $this->joinLabels($blockedCourseNames);

        if (empty($allowedItems)) {
            return "You are already enrolled in {$blockedList}. Remove it and try again.";
        }

        $allowedCourseNames = [];

        foreach ($allowedItems as $item) {
            $course = $this->learningCourseModel
                ->where('id', (int) $item['course_id'])
                ->first();

            $title = trim((string) ($course['title'] ?? ''));

            if ($title !== '') {
                $allowedCourseNames[] = $title;
            }
        }

        $allowedCourseNames = array_values(array_unique($allowedCourseNames));

        if (empty($allowedCourseNames)) {
            return "You are already enrolled in {$blockedList}. Remove it and try again.";
        }

        $allowedList = $this->joinLabels($allowedCourseNames);

        return "You are already enrolled in {$blockedList}. Remove that and enroll in {$allowedList} that is yet to be enrolled.";
    }

    private function joinLabels(array $labels): string
    {
        $labels = array_values(array_filter(array_map(
            static fn(string $label) => trim($label),
            $labels
        )));

        $count = \count($labels);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $labels[0];
        }

        if ($count === 2) {
            return $labels[0] . ' and ' . $labels[1];
        }

        $last = array_pop($labels);

        return implode(', ', $labels) . ', and ' . $last;
    }

    private function markEnrollmentAsPaidOrCreate(array $data): bool
    {
        $existingEnrollment = $this->findExistingCourseEnrollment(
            (int) $data['profile_id'],
            (int) $data['program_id'],
            (int) $data['course_id']
        );

        if ($existingEnrollment !== null) {
            $payload = [
                'payment_status' => 'success',
                'payment_reference' => $data['payment_reference'],
            ];

            if (\in_array($existingEnrollment['enrollment_type'] ?? null, ['trial', 'reserved'], true)) {
                $payload['enrollment_type'] = 'paid';
                $payload['status'] = 'active';
                $payload['trial_expiry_date'] = null;
            }

            $updated = $this->enrollmentModel
                ->where('id', $existingEnrollment['id'])
                ->update($payload);

            return (bool) $updated;
        }

        return $this->enrollUser([
            'profile_id' => $data['profile_id'],
            'program_id' => $data['program_id'],
            'course_id' => $data['course_id'],
            'cohort_id' => $data['cohort_id'],
            'enrollment_type' => 'paid',
            'send_notification' => false,
        ]);
    }

    private function dispatchEnrollmentNotifications(int $profileId, array $items): void
    {
        if (\count($items) === 1) {
            $item = $items[0];

            EventDispatcher::dispatch(
                new CohortCourseEnrolled(
                    $profileId,
                    (int) $item['program_id'],
                    (int) $item['course_id'],
                    (int) $item['cohort_id'],
                    $item['course_name'] ?? null,
                    $item['cohort_name'] ?? null
                )
            );

            return;
        }

        if (\count($items) > 1) {
            EventDispatcher::dispatch(
                new CohortCoursesEnrolled($profileId, $items)
            );
        }
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

    public function checkPaymentStatus(string $reference)
    {
        return $this->payment
            ->where('reference', $reference)
            ->where('status', 'success')
            ->exists();
    }

    private function computePrice(int $cohortId): int
    {
        $row = $this->programCourseCohort
            ->where('id', $cohortId)
            ->first();

        if (empty($row) || !isset($row['cost']) || $row['cost'] === null) {
            return 0;
        }

        $price = (float) $row['cost'];
        $discount = $row['discount'] ?? null;

        if ($discount) {
            $price -= ($price * $discount / 100);
        }

        if ($price <= 0) {
            return 0;
        }

        return (int) round($price * 100);
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
            $data['phone']
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

    public function reserve(array $data): bool
    {
        $user = $this->registerUserIfNotExists($data);

        if (empty($user)) {
            return false;
        }

        $data['profile_id'] = $user['profiles'][0]['id'];
        $reservedItems = [];

        foreach ($data['items'] as $item) {
            $payload = [
                'profile_id' => $data['profile_id'],
                'program_id' => $data['program_id'],
                'course_id' => $item['course_id'],
                'cohort_id' => $item['cohort_id'],
                'enrollment_type' => 'reserved',
                'payment_status' => 'reserved'
            ];

            $exist = $this->isUserEnrolled($payload);
            if ($exist) {
                continue;
            }

            $reserved = $this->enrollmentModel->insert($payload);

            if ($reserved) {
                $reservedItems[] = $this->buildEnrollmentNotificationItem([
                    'program_id' => $data['program_id'],
                    'course_id' => $item['course_id'],
                    'cohort_id' => $item['cohort_id'],
                ]);
            }
        }

        if (!empty($reservedItems)) {
            $this->dispatchEnrollmentNotifications((int) $data['profile_id'], $reservedItems);
        }

        return true;
    }

    public function freeEnroll(array $data): bool
    {
        $user = $this->registerUserIfNotExists($data);

        if (empty($user) || empty($user['profiles'])) {
            return false;
        }

        $profileId = (int) $user['profiles'][0]['id'];
        $freeEnrolledItems = [];

        foreach ($data['items'] as $item) {
            $existingEnrollment = $this->findExistingCourseEnrollment(
                $profileId,
                (int) $data['program_id'],
                (int) $item['course_id']
            );

            if ($existingEnrollment !== null) {
                if (!\in_array($existingEnrollment['enrollment_type'] ?? null, ['trial', 'reserved'], true)) {
                    continue;
                }

                $this->enrollmentModel
                    ->where('id', $existingEnrollment['id'])
                    ->update([
                        'cohort_id' => $item['cohort_id'],
                        'enrollment_type' => 'free',
                        'payment_status' => 'success',
                        'status' => 'active',
                        'trial_expiry_date' => null,
                        'payment_reference' => null,
                    ]);

                $freeEnrolledItems[] = $this->buildEnrollmentNotificationItem([
                    'program_id' => $data['program_id'],
                    'course_id' => $item['course_id'],
                    'cohort_id' => $item['cohort_id'],
                ]);

                continue;
            }

            $enrolled = $this->enrollmentModel->insert([
                'profile_id' => $profileId,
                'program_id' => $data['program_id'],
                'course_id' => $item['course_id'],
                'cohort_id' => $item['cohort_id'],
                'enrollment_type' => 'free',
                'payment_status' => 'success',
                'status' => 'active',
            ]);

            if ($enrolled) {
                $freeEnrolledItems[] = $this->buildEnrollmentNotificationItem([
                    'program_id' => $data['program_id'],
                    'course_id' => $item['course_id'],
                    'cohort_id' => $item['cohort_id'],
                ]);
            }
        }

        if (!empty($freeEnrolledItems)) {
            $this->dispatchEnrollmentNotifications($profileId, $freeEnrolledItems);
        }

        return true;
    }
}
