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

    public function getProgramProfilesAnalysis(int $programId): array
    {
        $program = $this->getProgramSummary($programId);

        if (empty($program)) {
            return [];
        }

        $rows = $this->getProgramProfileRows($programId);

        return [
            'program' => $program,
            'summary' => $this->buildProgramProfilesSummary($rows),
            'profiles' => $this->groupProgramProfiles($rows),
        ];
    }

    public function getProfileEnrollments(int $programId, int $profileId): array
    {
        $program = $this->getProgramSummary($programId);

        if (empty($program)) {
            return [];
        }

        $rows = $this->getProfileEnrollmentRows($programId, $profileId);

        if (empty($rows)) {
            return [
                'program' => $program,
                'profile' => [],
                'summary' => $this->emptyEnrollmentSummary(),
                'enrollments' => [],
            ];
        }

        return [
            'program' => $program,
            'profile' => $this->buildProfile($rows[0]),
            'summary' => $this->buildEnrollmentSummary($rows),
            'enrollments' => $this->buildEnrollments($rows),
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

    private function getProgramProfileRows(int $programId): array
    {
        return $this->programModel->rawQuery(
            "
            SELECT
                e.profile_id,
                pp.first_name,
                pp.last_name,
                u.email,
                u.phone,
                COUNT(*) AS total_enrollments,
                COALESCE(SUM(payment_stats.payment_attempt_count), 0) AS total_payment_attempts,
                COALESCE(SUM(payment_stats.successful_payment_count), 0) AS total_successful_payments,
                COALESCE(SUM(payment_stats.total_amount_paid), 0) AS total_amount_paid,
                GROUP_CONCAT(
                    DISTINCT CONCAT(e.course_id, '::', lc.title)
                    ORDER BY lc.title ASC
                    SEPARATOR '||'
                ) AS courses_filter,
                GROUP_CONCAT(
                    DISTINCT e.status
                    ORDER BY e.status ASC
                    SEPARATOR '||'
                ) AS enrollment_statuses_filter,
                GROUP_CONCAT(
                    DISTINCT e.enrollment_type
                    ORDER BY e.enrollment_type ASC
                    SEPARATOR '||'
                ) AS enrollment_types_filter,
                GROUP_CONCAT(
                    DISTINCT COALESCE(resolved_payment.resolved_payment_status, e.payment_status, 'unknown')
                    ORDER BY COALESCE(resolved_payment.resolved_payment_status, e.payment_status, 'unknown') ASC
                    SEPARATOR '||'
                ) AS payment_statuses_filter
            FROM program_course_cohort_enrollments e
            INNER JOIN program_profiles pp
                ON pp.id = e.profile_id
            LEFT JOIN cbt_users u
                ON u.id = pp.user_id
            INNER JOIN learning_courses lc
                ON lc.id = e.course_id
            LEFT JOIN (
                SELECT
                    pay.profile_id,
                    pi.program_id,
                    pi.course_id,
                    pi.cohort_id,
                    COUNT(*) AS payment_attempt_count,
                    SUM(CASE WHEN pay.status = 'success' THEN 1 ELSE 0 END) AS successful_payment_count,
                    COALESCE(SUM(CASE WHEN pay.status = 'success' THEN pi.amount ELSE 0 END), 0) AS total_amount_paid
                FROM course_cohort_payment_items pi
                INNER JOIN course_cohort_payments pay
                    ON pay.id = pi.payment_id
                WHERE pi.program_id = :program_id
                GROUP BY
                    pay.profile_id,
                    pi.program_id,
                    pi.course_id,
                    pi.cohort_id
            ) AS payment_stats
                ON payment_stats.profile_id = e.profile_id
               AND payment_stats.program_id = e.program_id
               AND payment_stats.course_id = e.course_id
               AND payment_stats.cohort_id = e.cohort_id
            LEFT JOIN (
                SELECT
                    resolved.profile_id,
                    resolved.program_id,
                    resolved.course_id,
                    resolved.cohort_id,
                    resolved.status AS resolved_payment_status
                FROM (
                    SELECT
                        pay.profile_id,
                        pi.program_id,
                        pi.course_id,
                        pi.cohort_id,
                        pay.status,
                        ROW_NUMBER() OVER (
                            PARTITION BY pay.profile_id, pi.program_id, pi.course_id, pi.cohort_id
                            ORDER BY
                                CASE WHEN pay.status = 'success' THEN 0 ELSE 1 END,
                                pay.created_at DESC,
                                pay.id DESC,
                                pi.id DESC
                        ) AS row_num
                    FROM course_cohort_payment_items pi
                    INNER JOIN course_cohort_payments pay
                        ON pay.id = pi.payment_id
                    WHERE pi.program_id = :program_id
                ) AS resolved
                WHERE resolved.row_num = 1
            ) AS resolved_payment
                ON resolved_payment.profile_id = e.profile_id
               AND resolved_payment.program_id = e.program_id
               AND resolved_payment.course_id = e.course_id
               AND resolved_payment.cohort_id = e.cohort_id
            WHERE e.program_id = :program_id
            GROUP BY
                e.profile_id,
                pp.first_name,
                pp.last_name,
                u.email,
                u.phone
            ORDER BY pp.first_name ASC, pp.last_name ASC, e.profile_id ASC
            ",
            [
                'program_id' => $programId,
            ]
        );
    }

    private function getProfileEnrollmentRows(int $programId, int $profileId): array
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
            INNER JOIN learning_courses lc
                ON lc.id = e.course_id
            LEFT JOIN program_course_cohorts c
                ON c.id = e.cohort_id
            WHERE e.program_id = :program_id
              AND e.profile_id = :profile_id
            ORDER BY lc.title ASC, c.title ASC, e.created_at DESC, e.id DESC
            ",
            [
                'program_id' => $programId,
                'profile_id' => $profileId,
            ]
        );
    }

    private function buildProgramProfilesSummary(array $rows): array
    {
        $totalEnrollments = 0;
        $totalPaymentAttempts = 0;
        $totalSuccessfulPayments = 0;
        $totalAmountPaid = 0;

        foreach ($rows as $row) {
            $totalEnrollments += (int) $row['total_enrollments'];
            $totalPaymentAttempts += (int) $row['total_payment_attempts'];
            $totalSuccessfulPayments += (int) $row['total_successful_payments'];
            $totalAmountPaid += (int) $row['total_amount_paid'];
        }

        return [
            'total_profiles' => count($rows),
            'total_enrollments' => $totalEnrollments,
            'total_payment_attempts' => $totalPaymentAttempts,
            'total_successful_payments' => $totalSuccessfulPayments,
            'total_amount_paid' => $totalAmountPaid,
            'currency' => 'NGN',
            'amount_unit' => 'kobo',
        ];
    }

    private function groupProgramProfiles(array $rows): array
    {
        return array_map(function (array $row): array {
            return [
                'profile_id' => (int) $row['profile_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'phone' => $row['phone'] ?? null,
                'summary' => [
                    'total_enrollments' => (int) $row['total_enrollments'],
                    'total_payment_attempts' => (int) $row['total_payment_attempts'],
                    'total_successful_payments' => (int) $row['total_successful_payments'],
                    'total_amount_paid' => (int) $row['total_amount_paid'],
                    'currency' => 'NGN',
                    'amount_unit' => 'kobo',
                ],
                'filters' => [
                    'courses' => $this->parseCourseFilters($row['courses_filter'] ?? ''),
                    'enrollment_statuses' => $this->parseListFilter($row['enrollment_statuses_filter'] ?? ''),
                    'enrollment_types' => $this->parseListFilter($row['enrollment_types_filter'] ?? ''),
                    'payment_statuses' => $this->parseListFilter($row['payment_statuses_filter'] ?? ''),
                ],
            ];
        }, $rows);
    }

    private function buildProfile(array $row): array
    {
        return [
            'profile_id' => (int) $row['profile_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
        ];
    }

    private function buildEnrollmentSummary(array $rows): array
    {
        $paymentAttemptCount = 0;
        $successfulPaymentCount = 0;
        $totalAmountPaid = 0;

        foreach ($rows as $row) {
            $paymentAttemptCount += (int) ($row['payment_attempt_count'] ?? 0);
            $successfulPaymentCount += (int) ($row['successful_payment_count'] ?? 0);
            $totalAmountPaid += (int) ($row['total_amount_paid'] ?? 0);
        }

        return [
            'total_enrollments' => count($rows),
            'total_payment_attempts' => $paymentAttemptCount,
            'total_successful_payments' => $successfulPaymentCount,
            'total_amount_paid' => $totalAmountPaid,
            'currency' => 'NGN',
            'amount_unit' => 'kobo',
        ];
    }

    private function emptyEnrollmentSummary(): array
    {
        return [
            'total_enrollments' => 0,
            'total_payment_attempts' => 0,
            'total_successful_payments' => 0,
            'total_amount_paid' => 0,
            'currency' => 'NGN',
            'amount_unit' => 'kobo',
        ];
    }

    private function buildEnrollments(array $rows): array
    {
        return array_map(function (array $row): array {
            $successfulPaymentCount = (int) ($row['successful_payment_count'] ?? 0);

            return [
                'enrollment_id' => (int) $row['enrollment_id'],
                'course_id' => (int) $row['course_id'],
                'course_name' => $row['course_name'],
                'cohort_id' => (int) $row['cohort_id'],
                'cohort_name' => $row['cohort_name'] ?? null,
                'cohort_slug' => $row['cohort_slug'] ?? null,
                'cohort_status' => $row['cohort_status'] ?? null,
                'enrollment_type' => $row['enrollment_type'] ?? null,
                'enrollment_status' => $row['enrollment_status'] ?? null,
                'payment_status' => $row['enrollment_payment_status'] ?? null,
                'payment_reference' => $row['enrollment_payment_reference'] ?? null,
                'lessons_taken' => $row['lessons_taken'] !== null ? (int) $row['lessons_taken'] : null,
                'trial_expiry_date' => $row['trial_expiry_date'] ?? null,
                'enrolled_at' => $row['enrolled_at'] ?? null,
                'payment_attempt_count' => (int) ($row['payment_attempt_count'] ?? 0),
                'successful_payment_count' => $successfulPaymentCount,
                'total_amount_paid' => (int) ($row['total_amount_paid'] ?? 0),
                'has_successful_payment' => $successfulPaymentCount > 0,
                'resolved_payment' => [
                    'status' => $row['resolved_payment_status'] ?? null,
                    'reference' => $row['resolved_payment_reference'] ?? null,
                    'amount' => $row['resolved_payment_amount'] !== null ? (int) $row['resolved_payment_amount'] : null,
                    'created_at' => $row['resolved_payment_created_at'] ?? null,
                ],
            ];
        }, $rows);
    }

    private function parseListFilter(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(explode('||', $value), fn ($item) => $item !== ''));
    }

    private function parseCourseFilters(string $value): array
    {
        $items = $this->parseListFilter($value);
        $courses = [];

        foreach ($items as $item) {
            [$courseId, $courseName] = array_pad(explode('::', $item, 2), 2, null);

            if ($courseId === null || $courseName === null) {
                continue;
            }

            $courses[] = [
                'id' => (int) $courseId,
                'name' => $courseName,
            ];
        }

        return $courses;
    }
}
