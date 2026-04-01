<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Program;

class ProgramEnrollmentAnalysisService
{
    private Program $programModel;

    public function __construct(private \PDO $pdo)
    {
        $this->programModel = new Program($pdo);
    }

    public function getProgramEnrollmentAnalysis(int $programId): array
    {
        $program = $this->getProgramSummary($programId);

        if (empty($program)) {
            return [];
        }

        $rows = $this->getEnrollmentRows($programId);

        return [
            'program' => $program,
            'summary' => $this->buildSummary($rows),
            'profiles' => $this->groupProfiles($rows),
        ];
    }

    private function getProgramSummary(int $programId): array
    {
        $rows = $this->programModel->rawQuery(
            "
            SELECT
                p.id,
                p.name,
                p.slug,
                p.shortname,
                p.status,
                p.start_date
            FROM programs p
            WHERE p.id = :program_id
            LIMIT 1
            ",
            [
                'program_id' => $programId,
            ]
        );

        if (empty($rows)) {
            return [];
        }

        $program = $rows[0];

        return [
            'id' => (int) $program['id'],
            'name' => $program['name'],
            'slug' => $program['slug'],
            'shortname' => $program['shortname'] ?? null,
            'status' => $program['status'] ?? null,
            'start_date' => $program['start_date'] ?? null,
        ];
    }

    private function getEnrollmentRows(int $programId): array
    {
        return $this->programModel->rawQuery(
            "
            SELECT
                e.id AS enrollment_id,
                e.profile_id,
                e.program_id,
                e.course_id,
                e.cohort_id,
                e.enrollment_type,
                e.status AS enrollment_status,
                e.payment_status AS enrollment_payment_status,
                e.payment_reference AS enrollment_payment_reference,
                e.lessons_taken,
                e.trial_expiry_date,
                e.created_at AS enrolled_at,
                pp.first_name,
                pp.last_name,
                u.email,
                u.phone,
                lc.title AS course_name,
                c.title AS cohort_name,
                c.slug AS cohort_slug,
                c.status AS cohort_status,
                (
                    SELECT COUNT(*)
                    FROM course_cohort_payment_items pi
                    INNER JOIN course_cohort_payments pay
                        ON pay.id = pi.payment_id
                    WHERE pay.profile_id = e.profile_id
                      AND pi.program_id = e.program_id
                      AND pi.course_id = e.course_id
                      AND pi.cohort_id = e.cohort_id
                ) AS payment_attempt_count,
                (
                    SELECT COUNT(*)
                    FROM course_cohort_payment_items pi
                    INNER JOIN course_cohort_payments pay
                        ON pay.id = pi.payment_id
                    WHERE pay.profile_id = e.profile_id
                      AND pi.program_id = e.program_id
                      AND pi.course_id = e.course_id
                      AND pi.cohort_id = e.cohort_id
                      AND pay.status = 'success'
                ) AS successful_payment_count,
                (
                    SELECT COALESCE(SUM(pi.amount), 0)
                    FROM course_cohort_payment_items pi
                    INNER JOIN course_cohort_payments pay
                        ON pay.id = pi.payment_id
                    WHERE pay.profile_id = e.profile_id
                      AND pi.program_id = e.program_id
                      AND pi.course_id = e.course_id
                      AND pi.cohort_id = e.cohort_id
                      AND pay.status = 'success'
                ) AS total_amount_paid,
                (
                    SELECT pay.reference
                    FROM course_cohort_payment_items pi
                    INNER JOIN course_cohort_payments pay
                        ON pay.id = pi.payment_id
                    WHERE pay.profile_id = e.profile_id
                      AND pi.program_id = e.program_id
                      AND pi.course_id = e.course_id
                      AND pi.cohort_id = e.cohort_id
                    ORDER BY
                        CASE WHEN pay.status = 'success' THEN 0 ELSE 1 END,
                        pay.created_at DESC,
                        pay.id DESC,
                        pi.id DESC
                    LIMIT 1
                ) AS resolved_payment_reference,
                (
                    SELECT pay.status
                    FROM course_cohort_payment_items pi
                    INNER JOIN course_cohort_payments pay
                        ON pay.id = pi.payment_id
                    WHERE pay.profile_id = e.profile_id
                      AND pi.program_id = e.program_id
                      AND pi.course_id = e.course_id
                      AND pi.cohort_id = e.cohort_id
                    ORDER BY
                        CASE WHEN pay.status = 'success' THEN 0 ELSE 1 END,
                        pay.created_at DESC,
                        pay.id DESC,
                        pi.id DESC
                    LIMIT 1
                ) AS resolved_payment_status,
                (
                    SELECT pi.amount
                    FROM course_cohort_payment_items pi
                    INNER JOIN course_cohort_payments pay
                        ON pay.id = pi.payment_id
                    WHERE pay.profile_id = e.profile_id
                      AND pi.program_id = e.program_id
                      AND pi.course_id = e.course_id
                      AND pi.cohort_id = e.cohort_id
                    ORDER BY
                        CASE WHEN pay.status = 'success' THEN 0 ELSE 1 END,
                        pay.created_at DESC,
                        pay.id DESC,
                        pi.id DESC
                    LIMIT 1
                ) AS resolved_payment_amount,
                (
                    SELECT pay.created_at
                    FROM course_cohort_payment_items pi
                    INNER JOIN course_cohort_payments pay
                        ON pay.id = pi.payment_id
                    WHERE pay.profile_id = e.profile_id
                      AND pi.program_id = e.program_id
                      AND pi.course_id = e.course_id
                      AND pi.cohort_id = e.cohort_id
                    ORDER BY
                        CASE WHEN pay.status = 'success' THEN 0 ELSE 1 END,
                        pay.created_at DESC,
                        pay.id DESC,
                        pi.id DESC
                    LIMIT 1
                ) AS resolved_payment_created_at
            FROM program_course_cohort_enrollments e
            INNER JOIN program_profiles pp
                ON pp.id = e.profile_id
            LEFT JOIN cbt_users u
                ON u.id = pp.user_id
            LEFT JOIN learning_courses lc
                ON lc.id = e.course_id
            LEFT JOIN program_course_cohorts c
                ON c.id = e.cohort_id
            WHERE e.program_id = :program_id
            ORDER BY pp.first_name ASC, pp.last_name ASC, e.created_at DESC, e.id DESC
            ",
            [
                'program_id' => $programId,
            ]
        );
    }

    private function buildSummary(array $rows): array
    {
        $profileIds = [];
        $paymentAttemptCount = 0;
        $successfulPaymentCount = 0;
        $totalAmountPaid = 0;

        foreach ($rows as $row) {
            $profileIds[(int) $row['profile_id']] = true;
            $paymentAttemptCount += (int) ($row['payment_attempt_count'] ?? 0);
            $successfulPaymentCount += (int) ($row['successful_payment_count'] ?? 0);
            $totalAmountPaid += (int) ($row['total_amount_paid'] ?? 0);
        }

        return [
            'total_profiles' => count($profileIds),
            'total_enrollments' => count($rows),
            'total_payment_attempts' => $paymentAttemptCount,
            'total_successful_payments' => $successfulPaymentCount,
            'total_amount_paid' => $totalAmountPaid,
            'currency' => 'NGN',
            'amount_unit' => 'kobo',
        ];
    }

    private function groupProfiles(array $rows): array
    {
        $profiles = [];

        foreach ($rows as $row) {
            $profileId = (int) $row['profile_id'];

            if (!isset($profiles[$profileId])) {
                $profiles[$profileId] = [
                    'profile_id' => $profileId,
                    'first_name' => $row['first_name'] ?? null,
                    'last_name' => $row['last_name'] ?? null,
                    'email' => $row['email'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'summary' => [
                        'total_enrollments' => 0,
                        'total_payment_attempts' => 0,
                        'total_successful_payments' => 0,
                        'total_amount_paid' => 0,
                        'currency' => 'NGN',
                        'amount_unit' => 'kobo',
                    ],
                    'enrollments' => [],
                ];
            }

            $attemptCount = (int) ($row['payment_attempt_count'] ?? 0);
            $successfulPaymentCount = (int) ($row['successful_payment_count'] ?? 0);
            $totalAmountPaid = (int) ($row['total_amount_paid'] ?? 0);

            $profiles[$profileId]['summary']['total_enrollments']++;
            $profiles[$profileId]['summary']['total_payment_attempts'] += $attemptCount;
            $profiles[$profileId]['summary']['total_successful_payments'] += $successfulPaymentCount;
            $profiles[$profileId]['summary']['total_amount_paid'] += $totalAmountPaid;

            $profiles[$profileId]['enrollments'][] = [
                'enrollment_id' => (int) $row['enrollment_id'],
                'course_id' => (int) $row['course_id'],
                'course_name' => $row['course_name'] ?? null,
                'cohort_id' => (int) $row['cohort_id'],
                'cohort_name' => $row['cohort_name'] ?? null,
                'cohort_slug' => $row['cohort_slug'] ?? null,
                'cohort_status' => $row['cohort_status'] ?? null,
                'enrollment_type' => $row['enrollment_type'] ?? null,
                'enrollment_status' => $row['enrollment_status'] ?? null,
                'payment_status' => $row['enrollment_payment_status'] ?? null,
                'payment_reference' => $row['enrollment_payment_reference'] ?? null,
                'lessons_taken' => isset($row['lessons_taken']) ? (int) $row['lessons_taken'] : null,
                'trial_expiry_date' => $row['trial_expiry_date'] ?? null,
                'enrolled_at' => $row['enrolled_at'] ?? null,
                'payment_attempt_count' => $attemptCount,
                'successful_payment_count' => $successfulPaymentCount,
                'total_amount_paid' => $totalAmountPaid,
                'has_successful_payment' => $successfulPaymentCount > 0,
                'resolved_payment' => [
                    'status' => $row['resolved_payment_status'] ?? null,
                    'reference' => $row['resolved_payment_reference'] ?? null,
                    'amount' => isset($row['resolved_payment_amount']) ? (int) $row['resolved_payment_amount'] : null,
                    'created_at' => $row['resolved_payment_created_at'] ?? null,
                ],
            ];
        }

        foreach ($profiles as &$profile) {
            usort($profile['enrollments'], function (array $left, array $right): int {
                return [$left['course_name'], $left['cohort_name'], $left['enrollment_id']]
                    <=>
                    [$right['course_name'], $right['cohort_name'], $right['enrollment_id']];
            });
        }
        unset($profile);

        return array_values($profiles);
    }
}
