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
    private const PAYMENT_EXPIRY_MINUTES = 10;

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

        if (($data['enrollment_type'] ?? null) === 'trial') {
            $payload = [
                ...$payload,
                ...$this->buildTrialEnrollmentPayload($data),
            ];
        }

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

    public function enrollOrResolveNextAction(array $data): array
    {
        $cohort = $this->getEnrollmentCohort($data);

        if (empty($cohort)) {
            throw new \InvalidArgumentException('Invalid cohort selected for enrollment.');
        }

        $existingEnrollment = $this->enrollmentModel
            ->where('profile_id', (int) $data['profile_id'])
            ->where('cohort_id', (int) $data['cohort_id'])
            ->first();

        if (!empty($existingEnrollment)) {
            return $this->buildEnrollmentDecision(
                $data,
                $cohort,
                $existingEnrollment,
                false,
                'Enrollment status resolved.'
            );
        }

        $requestedType = $data['enrollment_type'] ?? null;

        if ((bool) $cohort['is_free']) {
            $enrollmentId = $this->createEnrollment([
                ...$data,
                'enrollment_type' => 'free',
            ]);

            return $this->buildEnrollmentDecision(
                $data,
                $cohort,
                [
                    'id' => $enrollmentId,
                    'enrollment_type' => 'free',
                    'payment_status' => 'success',
                    'trial_expiry_date' => null,
                ],
                true,
                'User enrolled successfully.'
            );
        }

        if ($requestedType === 'trial') {
            $trialPayload = $this->buildTrialEnrollmentPayloadFromCohort($cohort);
            $enrollmentId = $this->createEnrollment([
                ...$data,
                'enrollment_type' => 'trial',
                ...$trialPayload,
            ]);

            return $this->buildEnrollmentDecision(
                $data,
                $cohort,
                [
                    'id' => $enrollmentId,
                    'enrollment_type' => 'trial',
                    'payment_status' => null,
                    'trial_expiry_date' => $trialPayload['trial_expiry_date'],
                ],
                true,
                'Trial enrollment started successfully.'
            );
        }

        return [
            'created' => false,
            'message' => 'Payment required.',
            'data' => $this->buildEnrollmentActionData($data, 'payment'),
        ];
    }

    private function createEnrollment(array $data): int
    {
        $payload = [
            'profile_id' => $data['profile_id'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'] ?? null,
            'cohort_name' => $data['cohort_name'] ?? null,
            'cohort_id' => $data['cohort_id'],
            'program_id' => $data['program_id'],
            'enrollment_type' => $data['enrollment_type'],
        ];

        foreach (['trial_expiry_date', 'lessons_taken', 'payment_status', 'payment_reference', 'status'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        $enrollmentId = $this->enrollmentModel->insert($payload);

        if (!$enrollmentId) {
            throw new \InvalidArgumentException('Enrollment failed.');
        }

        if ($data['send_notification'] ?? true) {
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

        return (int) $enrollmentId;
    }

    private function getEnrollmentCohort(array $data): array
    {
        return $this->programCourseCohort
            ->select([
                'id',
                'program_id',
                'course_id',
                'is_free',
                'status',
                'trial_type',
                'trial_value',
                'cost',
                'discount',
            ])
            ->where('id', (int) $data['cohort_id'])
            ->where('program_id', (int) $data['program_id'])
            ->where('course_id', (int) $data['course_id'])
            ->whereRaw('status IN (?, ?)', ['ongoing', 'upcoming'])
            ->first();
    }

    private function buildEnrollmentDecision(
        array $data,
        array $cohort,
        array $enrollment,
        bool $created,
        string $message
    ): array {
        return [
            'created' => $created,
            'message' => $message,
            'data' => $this->buildEnrollmentActionData(
                $data,
                $this->resolveEnrollmentNextAction($cohort, $enrollment)
            ),
        ];
    }

    private function buildEnrollmentActionData(
        array $data,
        string $nextAction
    ): array {
        return [
            'nextAction' => $nextAction,
            'payment_endpoint' => $nextAction === 'payment'
                ? '/public/learning/cohorts/' . (int) $data['cohort_id'] . '/enrollments/mobile-payment'
                : null,
        ];
    }

    private function resolveEnrollmentNextAction(array $cohort, array $enrollment): string
    {
        $isUpcoming = ($cohort['status'] ?? null) === 'upcoming';
        $isReserved = ($enrollment['enrollment_type'] ?? null) === 'reserved'
            || ($enrollment['payment_status'] ?? null) === 'reserved';
        $isPaidAccess = \in_array($enrollment['enrollment_type'] ?? null, ['paid', 'free'], true)
            || ($enrollment['payment_status'] ?? null) === 'success';
        $isTrialAccess = $this->hasActiveTrialAccess($cohort, $enrollment);

        if ($isReserved || (($enrollment['enrollment_type'] ?? null) === 'trial' && !$isTrialAccess)) {
            return 'payment';
        }

        if ($isUpcoming && ($isPaidAccess || $isTrialAccess)) {
            return 'waiting';
        }

        if ($isPaidAccess || $isTrialAccess) {
            return 'content';
        }

        return 'payment';
    }

    private function hasActiveTrialAccess(array $cohort, array $enrollment): bool
    {
        if (($enrollment['enrollment_type'] ?? null) !== 'trial') {
            return false;
        }

        $trialType = $cohort['trial_type'] ?? null;
        $trialValue = (int) ($cohort['trial_value'] ?? 0);

        if ($trialType === 'days') {
            return $trialValue > 0 && !$this->isTrialExpired($enrollment['trial_expiry_date'] ?? null);
        }

        if ($trialType === 'views') {
            return $trialValue > 0 && (int) ($enrollment['lessons_taken'] ?? 0) < $trialValue;
        }

        return false;
    }

    private function buildTrialEnrollmentPayload(array $data): array
    {
        $cohort = $this->programCourseCohort
            ->select(['id', 'program_id', 'course_id', 'trial_type', 'trial_value', 'status'])
            ->where('id', (int) $data['cohort_id'])
            ->where('program_id', (int) $data['program_id'])
            ->where('course_id', (int) $data['course_id'])
            ->whereRaw('status IN (?, ?)', ['ongoing', 'upcoming'])
            ->first();

        if (empty($cohort)) {
            throw new \InvalidArgumentException('Invalid cohort selected for trial enrollment.');
        }

        return $this->buildTrialEnrollmentPayloadFromCohort($cohort);
    }

    private function buildTrialEnrollmentPayloadFromCohort(array $cohort): array
    {
        $trialType = $cohort['trial_type'] ?? null;
        $trialValue = (int) ($cohort['trial_value'] ?? 0);

        if (!\in_array($trialType, ['days', 'views'], true) || $trialValue < 1) {
            throw new \InvalidArgumentException('Trial is not available for this cohort.');
        }

        if ($trialType !== 'days') {
            return [
                'trial_expiry_date' => null,
                'lessons_taken' => 0,
            ];
        }

        return [
            'trial_expiry_date' => (new \DateTimeImmutable())
                ->modify('+' . $trialValue . ' days')
                ->format('Y-m-d H:i:s'),
            'lessons_taken' => 0,
        ];
    }

    private function isTrialExpired(?string $trialExpiresAt): bool
    {
        if ($trialExpiresAt === null || trim($trialExpiresAt) === '') {
            return false;
        }

        $expiryTimestamp = strtotime($trialExpiresAt);

        return $expiryTimestamp !== false && $expiryTimestamp < time();
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
        $cohort = $this->getEnrollmentCohort($data);

        if (empty($cohort)) {
            return [
                'status' => 'failed',
                'message' => 'Invalid cohort selected for payment.',
            ];
        }

        if ((bool) $cohort['is_free']) {
            return [
                'status' => 'failed',
                'message' => 'This course is free and should not be added to payment.',
            ];
        }

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

        $reference = 'COURSE-MOB-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
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
            'platform' => 'mobile',
            'expires_at' => $this->computePendingPaymentExpiryAt(),
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
        $checkout = $this->prepareCheckout($data);

        if (($checkout['status'] ?? '') !== 'ready') {
            return $checkout;
        }

        $reference = $this->generateCheckoutReference('COURSE');

        $this->pdo->beginTransaction();

        try {
            $paymentId = $this->payment->insert([
                'profile_id' => $checkout['profile_id'],
                'reference' => $reference,
                'amount' => $checkout['total'],
                'status' => 'pending',
                'method' => 'online',
                'platform' => 'web',
                'expires_at' => $this->computePendingPaymentExpiryAt(),
            ]);

            $this->insertCheckoutPaymentItems(
                (int) $paymentId,
                (int) $data['program_id'],
                $checkout['payable_items']
            );

            $paystack = new PaystackService();

            $payment = $paystack->initialize([
                'email' => $data['email'],
                'amount' => $checkout['total'],
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

    public function completeOfflinePayment(array $data): array
    {
        $checkout = $this->prepareCheckout($data);

        if (($checkout['status'] ?? '') !== 'ready') {
            return $checkout;
        }

        $reference = $this->generateCheckoutReference('OFFLINE');

        $this->pdo->beginTransaction();

        try {
            $paymentId = (int) $this->payment->insert([
                'profile_id' => $checkout['profile_id'],
                'reference' => $reference,
                'amount' => $checkout['total'],
                'status' => 'pending',
                'method' => 'offline',
                'platform' => 'web',
                'message' => 'Offline payment recorded successfully.',
            ]);

            $this->insertCheckoutPaymentItems(
                $paymentId,
                (int) $data['program_id'],
                $checkout['payable_items']
            );

            $res = $this->fulfillSuccessfulPayment(
                [
                    'id' => $paymentId,
                    'profile_id' => $checkout['profile_id'],
                    'reference' => $reference,
                ],
                $this->getPaymentItems($paymentId)
            );

            if (($res['status'] ?? '') !== 'success') {
                throw new \RuntimeException($res['message'] ?? 'Offline payment fulfillment failed.');
            }

            $this->pdo->commit();

            return [
                'status' => 'success',
                'reference' => $reference,
                'payment_status' => 'success',
                'message' => 'Offline payment completed successfully.',
            ];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return [
                'status' => 'failed',
                'message' => $e->getMessage(),
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
            $res = $this->fulfillSuccessfulPayment($payment, $items);

            $this->pdo->commit();

            return $res;
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
                $enrollmentPayload = [
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
                ];

                if (($enrollmentPayload['enrollment_type'] ?? null) === 'trial') {
                    $enrollmentPayload = [
                        ...$enrollmentPayload,
                        ...$this->buildTrialEnrollmentPayload($enrollmentPayload),
                    ];
                }

                $enrollmentInserted = $this->enrollmentModel->insert($enrollmentPayload);

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

    private function prepareCheckout(array $data): array
    {
        $user = $this->registerUserIfNotExists($data);

        if (empty($user) || empty($user['profiles'])) {
            return [
                'status' => 'failed',
                'message' => 'User registration failed'
            ];
        }

        $profileId = (int) $user['profiles'][0]['id'];
        $eligibility = $this->categorizeCheckoutItems(
            $profileId,
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

        return [
            'status' => 'ready',
            'profile_id' => $profileId,
            'payable_items' => $payableItems,
            'total' => $total,
        ];
    }

    private function generateCheckoutReference(string $prefix): string
    {
        return $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
    }

    private function insertCheckoutPaymentItems(int $paymentId, int $programId, array $items): void
    {
        foreach ($items as $item) {
            $this->paymentItem->insert([
                'payment_id' => $paymentId,
                'program_id' => $programId,
                'course_id' => $item['course_id'],
                'cohort_id' => $item['cohort_id'],
                'amount' => $item['amount']
            ]);
        }
    }

    private function getPaymentItems(int $paymentId): array
    {
        return $this->paymentItem
            ->where('payment_id', $paymentId)
            ->get();
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

    public function abandonExpiredPendingPayments(): int
    {
        $cutoff = date('Y-m-d H:i:s');

        return $this->payment->rawExecute(
            "
            UPDATE course_cohort_payments
            SET status = :status
            WHERE status = :pending_status
              AND expires_at IS NOT NULL
              AND expires_at <= :cutoff
            ",
            [
                'status' => 'abandoned',
                'pending_status' => 'pending',
                'cutoff' => $cutoff,
            ]
        );
    }

    public function getAbandonedCartReminderCandidates(int $limit = 200): array
    {
        return $this->getAbandonedCartReminderCandidatesAfterMinutes(0, $limit);
    }

    public function getAbandonedCartReminderCandidatesAfterMinutes(int $minutes, int $limit = 200): array
    {
        $minutes = max(0, $minutes);
        $limit = max(1, $limit);
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . $minutes . ' minutes'));

        return $this->payment->rawQuery(
            sprintf(
                "
                SELECT p.id
                FROM course_cohort_payments p
                WHERE p.status = 'abandoned'
                  AND p.method IN ('online', 'paystack')
                  AND p.created_at <= :cutoff
                  AND NOT EXISTS (
                      SELECT 1
                      FROM course_cohort_payments newer
                      INNER JOIN course_cohort_payment_items newer_items
                          ON newer_items.payment_id = newer.id
                      INNER JOIN course_cohort_payment_items current_items
                          ON current_items.payment_id = p.id
                         AND current_items.cohort_id = newer_items.cohort_id
                      WHERE newer.profile_id = p.profile_id
                        AND newer.platform = p.platform
                        AND (
                            newer.created_at > p.created_at
                            OR (newer.created_at = p.created_at AND newer.id > p.id)
                        )
                  )
                  AND NOT EXISTS (
                      SELECT 1
                      FROM program_course_cohort_enrollments e
                      INNER JOIN course_cohort_payment_items current_items
                          ON current_items.payment_id = p.id
                         AND current_items.cohort_id = e.cohort_id
                      WHERE e.profile_id = p.profile_id
                        AND COALESCE(e.payment_status, '') = 'success'
                  )
                ORDER BY p.created_at ASC
                LIMIT %d
                ",
                $limit
            ),
            [
                'cutoff' => $cutoff,
            ]
        );
    }

    public function getAbandonedCartDetails(int $paymentId): array
    {
        $rows = $this->payment->rawQuery(
            "
            SELECT
                p.id,
                p.profile_id,
                p.reference,
                p.amount,
                p.status,
                p.platform,
                p.method,
                p.expires_at,
                p.created_at,
                profile.first_name,
                profile.last_name,
                user.email,
                (
                    SELECT first_item.course_id
                    FROM course_cohort_payment_items first_item
                    WHERE first_item.payment_id = p.id
                    ORDER BY first_item.id ASC
                    LIMIT 1
                ) AS primary_course_id,
                (
                    SELECT first_cohort.slug
                    FROM course_cohort_payment_items first_item
                    INNER JOIN program_course_cohorts first_cohort
                        ON first_cohort.id = first_item.cohort_id
                    WHERE first_item.payment_id = p.id
                    ORDER BY first_item.id ASC
                    LIMIT 1
                ) AS primary_cohort_slug,
                GROUP_CONCAT(
                    DISTINCT CONCAT(course.title, ' - ', cohort.title)
                    ORDER BY item.id ASC
                    SEPARATOR ', '
                ) AS checkout_items
            FROM course_cohort_payments p
            LEFT JOIN program_profiles profile
                ON profile.id = p.profile_id
            LEFT JOIN cbt_users user
                ON user.id = profile.user_id
            LEFT JOIN course_cohort_payment_items item
                ON item.payment_id = p.id
            LEFT JOIN learning_courses course
                ON course.id = item.course_id
            LEFT JOIN program_course_cohorts cohort
                ON cohort.id = item.cohort_id
            WHERE p.id = :payment_id
            GROUP BY
                p.id,
                p.profile_id,
                p.reference,
                p.amount,
                p.status,
                p.platform,
                p.method,
                p.expires_at,
                p.created_at,
                profile.first_name,
                profile.last_name,
                user.email
            LIMIT 1
            ",
            [
                'payment_id' => $paymentId,
            ]
        );

        return $rows[0] ?? [];
    }

    public function shouldSendAbandonedCartReminder(int $paymentId): bool
    {
        $rows = $this->payment->rawQuery(
            "
            SELECT 1
            FROM course_cohort_payments p
            WHERE p.id = :payment_id
              AND p.status = 'abandoned'
              AND p.method IN ('online', 'paystack')
              AND NOT EXISTS (
                  SELECT 1
                  FROM course_cohort_payments newer
                  INNER JOIN course_cohort_payment_items newer_items
                      ON newer_items.payment_id = newer.id
                  INNER JOIN course_cohort_payment_items current_items
                      ON current_items.payment_id = p.id
                     AND current_items.cohort_id = newer_items.cohort_id
                  WHERE newer.profile_id = p.profile_id
                    AND newer.platform = p.platform
                    AND (
                        newer.created_at > p.created_at
                        OR (newer.created_at = p.created_at AND newer.id > p.id)
                    )
              )
              AND NOT EXISTS (
                  SELECT 1
                  FROM program_course_cohort_enrollments e
                  INNER JOIN course_cohort_payment_items current_items
                      ON current_items.payment_id = p.id
                     AND current_items.cohort_id = e.cohort_id
                  WHERE e.profile_id = p.profile_id
                    AND COALESCE(e.payment_status, '') = 'success'
              )
            LIMIT 1
            ",
            [
                'payment_id' => $paymentId,
            ]
        );

        return !empty($rows);
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

    private function fulfillSuccessfulPayment(array $payment, array $items): array
    {
        if (empty($payment['id']) || empty($payment['profile_id']) || empty($payment['reference'])) {
            return [
                'status' => 'failed',
                'message' => 'Invalid payment payload.',
            ];
        }

        $this->payment
            ->where('id', (int) $payment['id'])
            ->update(['status' => 'success']);

        $enrolledItems = [];

        foreach ($items as $item) {
            $enrolled = $this->markEnrollmentAsPaidOrCreate([
                'profile_id' => (int) $payment['profile_id'],
                'program_id' => (int) $item['program_id'],
                'course_id' => (int) $item['course_id'],
                'cohort_id' => (int) $item['cohort_id'],
                'payment_reference' => (string) $payment['reference'],
            ]);

            if ($enrolled) {
                $enrolledItems[] = $this->buildEnrollmentNotificationItem($item);
            }
        }

        if (!empty($enrolledItems)) {
            $this->dispatchEnrollmentNotifications((int) $payment['profile_id'], $enrolledItems);
        }

        return [
            'status' => 'success',
            'message' => 'Payment fulfilled successfully.',
        ];
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

    public function checkPaymentStatus(string $reference): array
    {
        $payment = $this->payment
            ->where('reference', $reference)
            ->first();

        if (empty($payment)) {
            return [
                'nextAction' => 'payment',
                'message' => 'Payment reference not found.',
            ];
        }

        $items = $this->getPaymentItems((int) $payment['id']);
        $firstItem = $items[0] ?? null;

        if (empty($firstItem)) {
            return [
                'nextAction' => 'payment',
                'message' => 'Payment has no checkout item.',
            ];
        }

        $cohort = $this->programCourseCohort
            ->select(['id', 'program_id', 'course_id', 'is_free', 'status', 'trial_type', 'trial_value'])
            ->where('id', (int) $firstItem['cohort_id'])
            ->first();

        if (($payment['status'] ?? null) !== 'success') {
            return [
                'nextAction' => 'payment',
                'message' => 'Payment is not successful yet.',
            ];
        }

        $enrollment = $this->enrollmentModel
            ->where('profile_id', (int) $payment['profile_id'])
            ->where('cohort_id', (int) $firstItem['cohort_id'])
            ->first();

        return [
            'nextAction' => !empty($cohort) && !empty($enrollment)
                ? $this->resolveEnrollmentNextAction($cohort, $enrollment)
                : 'payment',
            'message' => !empty($enrollment)
                ? 'Payment completed.'
                : 'Payment completed but enrollment is not ready yet.',
        ];
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

    private function computePendingPaymentExpiryAt(): string
    {
        return date(
            'Y-m-d H:i:s',
            strtotime('+' . self::PAYMENT_EXPIRY_MINUTES . ' minutes')
        );
    }
}
