<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Models\Explore\Program;
use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class ProgramContentService
{
    private Program $programModel;
    private ProgramCourseCohort $programCourseCohortModel;
    private ProgramCourseCohortLesson $programCourseCohortLessonModel;

    public function __construct(\PDO $pdo)
    {
        $this->programModel = new Program($pdo);
        $this->programCourseCohortModel = new ProgramCourseCohort($pdo);
        $this->programCourseCohortLessonModel = new ProgramCourseCohortLesson($pdo);
    }

    public function getProgramsWithCourses(
        ?string $birthDate = null,
        ?int $profileId = null
    ): array {
        $age = $birthDate !== null
            ? (int) date('Y') - (int) substr($birthDate, 0, 4)
            : null;

        $sql = "
        SELECT
            p.id            AS program_id,
            p.name          AS program_name,
            p.description   AS program_description,
            p.image_url    AS program_image_url,
            p.age_groups    AS program_age_groups,
            p.slug AS program_slug,

            lc.id           AS course_id,
            lc.title        AS course_title,
            lc.description  AS course_description,
            lc.image_url    AS course_image_url,

            c.id            AS active_cohort_id,
            c.is_free       AS is_free,
            c.trial_type    AS trial_type,
            c.trial_value   AS trial_value,
            c.cost          AS cost,
            c.discount      AS discount,
            c.learning_type  AS learning_type,
            c.video_url       AS video_url,
            c.slug            AS cohort_slug,
            c.enrollment_deadline AS enrollment_deadline,
            c.start_date       AS cohort_start_date,
            c.end_date         AS cohort_end_date,

            e.status        AS enrollment_status,
            e.payment_status AS payment_status,
            e.lessons_taken  AS lessons_taken,
            e.trial_expiry_date AS trial_expiry_date

        FROM programs p

        INNER JOIN program_courses pc
            ON pc.program_id = p.id
            AND pc.is_active = 1

        INNER JOIN learning_courses lc
            ON lc.id = pc.course_id

        LEFT JOIN program_course_cohorts c
            ON c.program_id = p.id
            AND c.course_id = lc.id
            AND c.status IN ('ongoing', 'upcoming')

        LEFT JOIN program_course_cohort_enrollments e
            ON e.program_id = p.id
            AND e.course_id = lc.id
            AND e.profile_id = :profile_id

        WHERE p.status = 'published'
        ORDER BY p.id, lc.id
    ";

        $rows = $this->programModel->rawQuery($sql, [
            'profile_id' => $profileId
        ]);

        return $this->groupProgramsWithCoursesWithAgeFilter($rows, $age);
    }

    public function getProgramsWithCoursesContent(
        ?string $birthDate = null,
        ?int $profileId = null
    ): array {
        $age = $birthDate !== null
            ? (int) date('Y') - (int) substr($birthDate, 0, 4)
            : null;

        $sql = "
        SELECT
            p.id            AS program_id,
            p.name          AS program_name,
            p.age_groups    AS program_age_groups,
            p.slug AS program_slug,

            lc.id           AS course_id,
            lc.title        AS course_title,
            lc.description  AS course_description,
            lc.image_url    AS course_image_url,
            lc.slug         AS course_slug,

            c.id            AS active_cohort_id,
            c.slug          AS cohort_slug,
            c.is_free       AS is_free,
            c.status          AS cohort_status,
            c.start_date       AS cohort_start_date,
            c.trial_type       AS trial_type,
            c.trial_value      AS trial_value,

            e.id            AS enrollment_id,
            e.payment_status AS payment_status,
            e.enrollment_type AS enrollment_type,
            e.trial_expiry_date AS trial_expiry_date,
            e.lessons_taken  AS lessons_taken

        FROM programs p

        INNER JOIN program_courses pc
            ON pc.program_id = p.id
            AND pc.is_active = 1

        INNER JOIN learning_courses lc
            ON lc.id = pc.course_id

        LEFT JOIN program_course_cohorts c
            ON c.program_id = p.id
            AND c.course_id = lc.id
            AND c.status IN ('ongoing', 'upcoming')

        LEFT JOIN program_course_cohort_enrollments e
            ON e.program_id = p.id
            AND e.course_id = lc.id
            AND e.cohort_id = c.id
            AND e.profile_id = :profile_id

        WHERE p.status = 'published'
        ORDER BY p.id, lc.id
    ";

        $rows = $this->programModel->rawQuery($sql, [
            'profile_id' => $profileId
        ]);

        return $this->groupProgramContentWithAgeFilter($rows, $age);
    }

    public function getProgramContentBySlug(int $profileId, string $programSlug): array
    {
        $sql = "
        SELECT
            p.id            AS program_id,
            p.name          AS program_name,
            p.age_groups    AS program_age_groups,
            p.slug AS program_slug,

            lc.id           AS course_id,
            lc.title        AS course_title,
            lc.description  AS course_description,
            lc.image_url    AS course_image_url,
            lc.slug         AS course_slug,

            c.id            AS active_cohort_id,
            c.slug          AS cohort_slug,
            c.is_free       AS is_free,
            c.status          AS cohort_status,
            c.start_date       AS cohort_start_date,
            c.trial_type       AS trial_type,
            c.trial_value      AS trial_value,

            e.id            AS enrollment_id,
            e.payment_status AS payment_status,
            e.enrollment_type AS enrollment_type,
            e.trial_expiry_date AS trial_expiry_date,
            e.lessons_taken  AS lessons_taken

        FROM programs p

        INNER JOIN program_courses pc
            ON pc.program_id = p.id
            AND pc.is_active = 1

        INNER JOIN learning_courses lc
            ON lc.id = pc.course_id

        LEFT JOIN program_course_cohorts c
            ON c.program_id = p.id
            AND c.course_id = lc.id
            AND c.status IN ('ongoing', 'upcoming')

        LEFT JOIN program_course_cohort_enrollments e
            ON e.program_id = p.id
            AND e.course_id = lc.id
            AND e.cohort_id = c.id
            AND e.profile_id = :profile_id

        WHERE p.status = 'published'
            AND p.slug = :program_slug
        ORDER BY lc.id
    ";

        $rows = $this->programModel->rawQuery($sql, [
            'profile_id' => $profileId,
            'program_slug' => $programSlug,
        ]);

        return $this->groupProgramContentWithAgeFilter($rows, null);
    }

    private function groupProgramsWithCoursesWithAgeFilter(
        array $rows,
        ?int $age
    ): array {
        $programs = [];

        foreach ($rows as $row) {
            if (!$row['course_id']) {
                continue;
            }

            $pid = (int) $row['program_id'];

            if (!isset($programs[$pid])) {
                $programs[$pid] = [
                    'program_id' => $pid,
                    'name' => $row['program_name'],
                    'description' => $row['program_description'],
                    'image_url' => AssetUrl::fromAppUrl($row['program_image_url']),
                    'slug' => $row['program_slug'],
                    'courses' => [],
                    '_course_map' => [],
                    '_has_enrollment' => false,
                    '_age_groups' => json_decode($row['program_age_groups'] ?? '[]', true) ?? []
                ];
            }

            // Track enrollment per program
            if ($row['enrollment_status'] !== null) {
                $programs[$pid]['_has_enrollment'] = true;
            }

            // Deduplicate courses
            if (isset($programs[$pid]['_course_map'][$row['course_id']])) {
                continue;
            }

            $programs[$pid]['_course_map'][$row['course_id']] = true;

            $programs[$pid]['courses'][] = [
                'course_id' => (int) $row['course_id'],
                'course_name' => $row['course_title'],
                'description' => $row['course_description'],
                'image_url' => AssetUrl::fromAppUrl($row['course_image_url']),

                'has_active_cohort' => $row['active_cohort_id'] !== null,
                'cohort_id' => $row['active_cohort_id'],
                'slug' => $row['active_cohort_id'] !== null ? $row['cohort_slug'] : null,

                'discount' => $row['active_cohort_id'] !== null
                    ? (int) $row['discount']
                    : null,

                'learning_type' => $row['active_cohort_id'] !== null
                    ? $row['learning_type']
                    : null,

                'video_url' => $row['active_cohort_id'] !== null
                    ? $row['video_url']
                    : null,

                'is_free' => $row['active_cohort_id'] !== null
                    ? (bool) $row['is_free']
                    : null,

                'trial_type' => $row['active_cohort_id'] !== null
                    ? $row['trial_type']
                    : null,

                'trial_value' => $row['active_cohort_id'] !== null
                    ? (int) $row['trial_value']
                    : null,

                'cost' => $row['active_cohort_id'] !== null
                    ? (float) $row['cost']
                    : null,

                'is_enrolled' => $row['enrollment_status'] !== null,
                'is_completed' => $row['enrollment_status'] === 'completed',
                'enrollment_status' => $row['enrollment_status'],
                'payment_status' => $row['payment_status'],
                'lessons_taken' => $row['lessons_taken'] !== null
                    ? (int) $row['lessons_taken']
                    : null,
                'trial_expiry_date' => $row['trial_expiry_date'] ?? null,
                'enrollment_deadline' => $row['enrollment_deadline'] ?? null,
                'cohort_start_date' => $row['cohort_start_date'] ?? null,
                'cohort_end_date' => $row['cohort_end_date'] ?? null,
            ];
        }

        // Apply age filter AFTER aggregation
        $programs = array_filter($programs, function ($program) use ($age) {
            if ($age === null) {
                return true;
            }

            if ($program['_has_enrollment']) {
                return true;
            }

            return $this->matchesAgeGroup($program['_age_groups'], $age);
        });

        // Cleanup internal fields
        foreach ($programs as &$program) {
            unset(
                $program['_course_map'],
                $program['_has_enrollment'],
                $program['_age_groups']
            );
        }

        return array_values($programs);
    }

    private function groupProgramContentWithAgeFilter(
        array $rows,
        ?int $age
    ): array {
        $programs = [];
        $enrolledCourses = [];
        $enrolledCourseMap = [];

        foreach ($rows as $row) {
            if (!$row['course_id']) {
                continue;
            }

            $pid = (int) $row['program_id'];

            if (!isset($programs[$pid])) {
                $programs[$pid] = [
                    'program_id' => $pid,
                    'name' => $row['program_name'],
                    'slug' => $row['program_slug'],
                    'courses' => [],
                    '_course_map' => [],
                    '_has_enrollment' => false,
                    '_age_groups' => json_decode($row['program_age_groups'] ?? '[]', true) ?? []
                ];
            }

            $course = $this->buildProgramCourseResponse($row);

            // Track enrollment per program
            if ($row['enrollment_id'] !== null) {
                $programs[$pid]['_has_enrollment'] = true;

                $enrolledCourseKey = $row['active_cohort_id'] ?? $row['course_id'];
                if (!isset($enrolledCourseMap[$enrolledCourseKey])) {
                    $enrolledCourseMap[$enrolledCourseKey] = true;
                    $enrolledCourses[] = $course;
                }
            }

            // Deduplicate courses
            if (isset($programs[$pid]['_course_map'][$row['course_id']])) {
                continue;
            }

            $programs[$pid]['_course_map'][$row['course_id']] = true;
            $programs[$pid]['courses'][] = $course;
        }

        // Apply age filter AFTER aggregation
        $programs = array_filter($programs, function ($program) use ($age) {
            if ($age === null) {
                return true;
            }

            if ($program['_has_enrollment']) {
                return true;
            }

            return $this->matchesAgeGroup($program['_age_groups'], $age);
        });

        // Cleanup internal fields
        foreach ($programs as &$program) {
            unset(
                $program['_course_map'],
                $program['_has_enrollment'],
                $program['_age_groups']
            );
        }

        return [
            'programs' => array_values($programs),
            'enrolled_courses' => [
                'courses' => $enrolledCourses,
            ],
        ];
    }

    private function buildProgramCourseResponse(array $row): array
    {
        $hasActiveCohort = $row['active_cohort_id'] !== null;
        $access = $this->resolveCourseAccess($row);

        return [
            'course_id' => (int) $row['course_id'],
            'course_name' => $row['course_title'],
            'description' => $row['course_description'],
            'image_url' => AssetUrl::fromAppUrl($row['course_image_url']),
            'slug' => $hasActiveCohort ? $row['cohort_slug'] : null,
            'cohort_id' => $hasActiveCohort ? (int) $row['active_cohort_id'] : null,
            'is_free' => (bool) ($row['is_free'] ?? false),
            'nextAction' => $access['nextAction'],
        ];
    }

    private function resolveCourseAccess(array $row): array
    {
        $hasEnrollment = $row['enrollment_id'] !== null;
        $trialExpiresAt = $row['trial_expiry_date'] ?? null;
        $isUpcoming = $this->isCohortWaiting($row);
        $isReserved = ($row['enrollment_type'] ?? null) === 'reserved'
            || ($row['payment_status'] ?? null) === 'reserved';
        $isPaidAccess = \in_array($row['enrollment_type'] ?? null, ['paid', 'free'], true)
            || ($row['payment_status'] ?? null) === 'success';
        $isTrialAccess = $this->hasActiveTrialAccess($row);

        if (!$hasEnrollment) {
            return $this->courseAccess('description', null, null);
        }

        if ($isReserved || (($row['enrollment_type'] ?? null) === 'trial' && !$isTrialAccess)) {
            return $this->courseAccess('payment', $trialExpiresAt, null);
        }

        if ($isUpcoming && ($isPaidAccess || $isTrialAccess)) {
            return $this->courseAccess('waiting', $trialExpiresAt, $row['cohort_start_date'] ?? null);
        }

        if ($isPaidAccess || $isTrialAccess) {
            return $this->courseAccess('content', $trialExpiresAt, null);
        }

        return $this->courseAccess('payment', $trialExpiresAt, null);
    }

    private function isCohortWaiting(array $row): bool
    {
        if (($row['cohort_status'] ?? null) === 'upcoming') {
            return true;
        }

        $startDate = $row['cohort_start_date'] ?? null;
        if ($startDate === null || trim((string) $startDate) === '') {
            return false;
        }

        $startTimestamp = strtotime((string) $startDate);

        return $startTimestamp !== false && $startTimestamp > time();
    }

    private function hasActiveTrialAccess(array $row): bool
    {
        if (($row['enrollment_type'] ?? null) !== 'trial') {
            return false;
        }

        $trialType = $row['trial_type'] ?? null;
        $trialValue = (int) ($row['trial_value'] ?? 0);

        if ($trialType === 'days') {
            return $trialValue > 0 && !$this->isTrialExpired($row['trial_expiry_date'] ?? null);
        }

        if ($trialType === 'views') {
            return $trialValue > 0 && (int) ($row['lessons_taken'] ?? 0) < $trialValue;
        }

        return false;
    }

    private function courseAccess(string $nextAction, ?string $trialExpiresAt, ?string $lockedUntil): array
    {
        return [
            'nextAction' => $nextAction,
            'trialExpiresAt' => $trialExpiresAt,
            'lockedUntil' => $lockedUntil,
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

    private function matchesAgeGroup(array $ageGroups, int $age): bool
    {
        foreach ($ageGroups as $group) {
            $min = $group['min'];
            $max = $group['max'];

            if ($age >= $min && ($max === null || $age <= $max)) {
                return true;
            }
        }

        return false;
    }

    public function getActiveCohortByCourse(int|string $cohortIdentifier, ?int $profileId = null): array
    {
        $cohortIdentifier = trim((string) $cohortIdentifier);
        $isCohortId = ctype_digit($cohortIdentifier);
        $whereClause = $isCohortId ? 'c.id = :cohort_id' : 'c.slug = :slug';

        $query = "
            SELECT
                p.id AS program_id,
                p.slug AS program_slug,
                p.name AS program_name,
                p.description AS program_description,
                p.image_url AS program_image_url,
                p.sponsor AS program_sponsor,
                p.start_date AS program_start_date,
                p.video_url AS program_video_url,
                p.onboarding_steps AS program_onboarding_steps,
                p.whatsapp_group_link AS program_whatsapp_group_link,
                lc.id AS course_id,
                lc.slug AS course_slug,
                lc.title AS course_name,
                lc.description AS course_description,
                lc.image_url AS course_image_url,
                c.id AS cohort_id,
                c.slug AS cohort_slug,
                c.title AS cohort_title,
                c.description AS cohort_description,
                c.benefits AS cohort_benefits,
                c.start_date,
                c.end_date,
                c.instructor_name,
                c.discount,
                c.cost,
                c.trial_type,
                c.trial_value,
                c.delivery_mode,
                c.video_url,
                c.image_url AS cohort_image_url,
                c.is_free,
                c.enrollment_deadline,
                c.learning_type,
                c.whatsapp_group_link AS cohort_whatsapp_group_link,
                CASE
                    WHEN :profile_id_check IS NOT NULL AND EXISTS (
                        SELECT 1
                        FROM program_course_cohort_enrollments e
                        WHERE e.profile_id = :profile_id_match
                            AND e.cohort_id = c.id
                    ) THEN 1
                    ELSE 0
                END AS has_enrolled
            FROM program_course_cohorts c
            INNER JOIN programs p
                ON p.id = c.program_id
            INNER JOIN program_courses pc
                ON pc.program_id = p.id
                AND pc.course_id = c.course_id
                AND pc.is_active = 1
            INNER JOIN learning_courses lc
                ON lc.id = c.course_id
            WHERE {$whereClause}
                AND p.status = :status
                AND c.status IN ('ongoing', 'upcoming')
            LIMIT 1
        ";

        $params = [
            'status' => 'published',
            'profile_id_check' => $profileId,
            'profile_id_match' => $profileId,
        ];
        if ($isCohortId) {
            $params['cohort_id'] = (int) $cohortIdentifier;
        } else {
            $params['slug'] = $cohortIdentifier;
        }

        $rows = $this->programCourseCohortModel->rawQuery($query, $params);

        if (empty($rows)) {
            return [];
        }

        $row = $rows[0];

        return [
            'program' => [
                'id' => (int) $row['program_id'],
                'slug' => $row['program_slug'],
                'name' => $row['program_name'],
                'description' => $row['program_description'],
                'image_url' => AssetUrl::fromAppUrl($row['program_image_url']),
                'sponsor' => $row['program_sponsor'],
                'start_date' => $row['program_start_date'] ?? null,
                'video_url' => $row['program_video_url'] ?? null,
                'onboarding_steps' => isset($row['program_onboarding_steps']) ?
                    json_decode($row['program_onboarding_steps'], true)
                    : null,
                'whatsapp_group_link' => $row['program_whatsapp_group_link'] ?? null,
            ],
            'course' => [
                'courseId' => (int) $row['course_id'],
                'slug' => $row['course_slug'],
                'courseName' => $row['course_name'],
                'description' => $row['course_description'],
                'image_url' => AssetUrl::fromAppUrl($row['course_image_url']),
            ],
            'cohort' => [
                'cohortId' => (int) $row['cohort_id'],
                'slug' => $row['cohort_slug'],
                'title' => $row['cohort_title'],
                'description' => $row['cohort_description'],
                'benefits' => $row['cohort_benefits'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'instructor_name' => $row['instructor_name'],
                'discount' => $row['discount'] !== null ? (int) $row['discount'] : null,
                'cost' => $row['cost'] !== null ? (float) $row['cost'] : null,
                'trial_type' => $row['trial_type'],
                'trial_value' => $row['trial_value'] !== null ? (int) $row['trial_value'] : null,
                'delivery_mode' => $row['delivery_mode'],
                'video_url' => $row['video_url'],
                'is_free' => (bool) $row['is_free'],
                'image_url' => AssetUrl::fromAppUrl($row['cohort_image_url']),
                'enrollment_deadline' => $row['enrollment_deadline'],
                'learning_type' => $row['learning_type'],
                'whatsapp_group_link' => $row['cohort_whatsapp_group_link'] ?? null,
                'hasEnrolled' => (bool) $row['has_enrolled'],
            ],
        ];
    }

    public function getLessonsByCohort(int $cohortId): array
    {
        return $this->programCourseCohortLessonModel
            ->select([
                'id',
                'title',
                'description',
                'video_url',
                'display_order',
                'is_final_lesson',
            ])
            ->where('cohort_id', $cohortId)
            ->where('status', 'published')
            ->orderBy('display_order', 'ASC')
            ->get();
    }

    public function getLessonsByCohortWithNextCourse(int $cohortId, int $profileId): array
    {
        $lessons = $this->getLessonsByCohort($cohortId);

        $cohort = $this->programCourseCohortModel
            ->select(['next_cohort'])
            ->where('id', $cohortId)
            ->first();

        if (empty($cohort) || $cohort['next_cohort'] === null) {
            return [
                'lessons' => $lessons,
                'next_course' => null,
            ];
        }

        $nextCohort = json_decode($cohort['next_cohort'], true) ?? null;

        $sql = "
            SELECT
                p.course_id,
                p.next_cohort,
                lc.title AS course_name,
                lc.description AS course_description,
                lc.image_url,
                EXISTS (
                    SELECT 1
                    FROM program_course_cohort_enrollments e
                    WHERE e.profile_id = :profile_id
                    AND e.cohort_id = p.id
                ) AS is_enrolled
            FROM program_course_cohorts p
            INNER JOIN learning_courses lc
                ON lc.id = p.course_id
            WHERE p.id = :cohort_id
            LIMIT 1
        ";

        $rows = $this->programCourseCohortModel->rawQuery($sql, [
            'cohort_id' => $nextCohort['id'],
            'profile_id' => $profileId,
        ]);

        $row = $rows[0];

        return [
            'lessons' => $lessons,
            'next_course' => !empty($nextCohort['id'])
                ? [
                    'id' => $nextCohort['id'],
                    'course_name' => html_entity_decode($row['course_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'description' => $row['course_description'],
                    'course_id' => (int) $row['course_id'],
                    'image' => AssetUrl::fromAppUrl($row['image_url']),
                    'is_enrolled' => (bool) $row['is_enrolled'],
                ] : null,
        ];
    }

    public function getLessonById(int $lessonId, int $profileId): array
    {
        $sql = "
            SELECT 
                l.id,
                l.title,
                l.description,
                l.goals,
                l.objectives,
                l.video_url,
                l.recorded_video_url,
                l.material_url,
                l.assignment_url,
                l.certificate_url,
                l.assignment_instructions,
                l.assignment_submission_type,
                l.is_final_lesson,
                l.display_order,
                l.lesson_date,
                l.assignment_due_date,
                l.zoom_info,

                s.id AS submission_id,
                s.submission_type,
                s.files,
                s.text_content,
                s.link_url,
                s.quiz_score,
                s.assigned_score,
                s.remark,
                s.comment,
                s.graded_at,
                s.notified_at,
                s.created_at AS submission_created_at,
                CASE WHEN a.lesson_id IS NULL THEN 0 ELSE 1 END AS has_attendance,
                EXISTS (
                    SELECT 1 
                    FROM cohort_lesson_quizzes q
                    WHERE q.lesson_id = l.id
                ) AS has_quiz
            FROM program_course_cohort_lessons l
            LEFT JOIN cohort_tasks_submissions s
                ON s.lesson_id = l.id
                AND s.profile_id = :profile_id
            LEFT JOIN (
                SELECT DISTINCT lesson_id
                FROM cohort_lesson_attendance
                WHERE profile_id = :attendance_profile_id
            ) a
                ON a.lesson_id = l.id
            WHERE l.id = :lesson_id
            LIMIT 1
        ";

        $row = $this->programCourseCohortLessonModel
            ->rawQuery($sql, [
                'lesson_id' => $lessonId,
                'profile_id' => $profileId,
                'attendance_profile_id' => $profileId,
            ]);

        if (empty($row)) {
            return [];
        }

        return $this->formatLessonWithSubmission($row[0]);
    }

    public function getCohortLessonsWithSubmission(
        int $cohortId,
        int $profileId
    ): array {
        $sql = "
            SELECT 
                l.id,
                l.title,
                l.description,
                l.goals,
                l.objectives,
                l.video_url,
                l.recorded_video_url,
                l.material_url,
                l.assignment_url,
                l.certificate_url,
                l.assignment_instructions,
                l.assignment_submission_type,
                l.is_final_lesson,
                l.display_order,
                l.lesson_date,
                l.assignment_due_date,
                l.zoom_info,

                s.id AS submission_id,
                s.submission_type,
                s.files,
                s.text_content,
                s.link_url,
                s.quiz_score,
                s.assigned_score,
                s.remark,
                s.comment,
                s.graded_at,
                s.notified_at,
                s.created_at AS submission_created_at,
                CASE WHEN a.lesson_id IS NULL THEN 0 ELSE 1 END AS has_attendance,
                EXISTS (
                    SELECT 1 
                    FROM cohort_lesson_quizzes q
                    WHERE q.lesson_id = l.id
                ) AS has_quiz
            FROM program_course_cohort_lessons l
            LEFT JOIN cohort_tasks_submissions s
                ON s.lesson_id = l.id
                AND s.profile_id = :profile_id
            LEFT JOIN (
                SELECT DISTINCT lesson_id
                FROM cohort_lesson_attendance
                WHERE profile_id = :attendance_profile_id
            ) a
                ON a.lesson_id = l.id
            WHERE l.cohort_id = :cohort_id
            AND l.status = 'published'
            ORDER BY l.display_order ASC
        ";

        $rows = $this->programCourseCohortLessonModel->rawQuery($sql, [
            'cohort_id'  => $cohortId,
            'profile_id' => $profileId,
            'attendance_profile_id' => $profileId,
        ]);

        return array_map([$this, 'formatLessonWithSubmission'], $rows);
    }

    private function formatLessonWithSubmission(array $row): array
    {
        $submission = null;

        if (!empty($row['submission_type'])) {
            $files = !empty($row['files'])
                ? json_decode($row['files'], true)
                : [];

            $submission = [
                'submission_type' => $row['submission_type'],
                'assignment' => !empty($files) ? $files[0]['file_name'] : null,
                'text_content' => $row['text_content'] ?? null,
                'link_url' => $row['link_url'] ?? null,
                'quiz_score' => $row['quiz_score'] !== null
                    ? (float) $row['quiz_score']
                    : null,

                'assigned_score' => $row['assigned_score'] !== null
                    ? (float) $row['assigned_score']
                    : null,
                'remark' => $row['remark'] ?? null,
                'comment' => $row['comment'] ?? null,
                'graded_at' => $row['graded_at'] ?? null,
                'notified_at' => $row['notified_at'] ?? null,
                'submitted_at' => $row['submission_created_at'] ?? null,
            ];
        }

        return [
            'lesson' => [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'goals' => $row['goals'],
                'objectives' => $row['objectives'],
                'video_url' => $row['video_url'],
                'recorded_video_url' => $row['recorded_video_url'],
                'material_url' => $row['material_url'],
                'assignment_url' => $row['assignment_url'],
                'certificate_url' => $row['certificate_url'],
                'assignment_instructions' => $row['assignment_instructions'],
                'assignment_submission_type' => $row['assignment_submission_type'],
                'is_final_lesson' => (bool) $row['is_final_lesson'],
                'has_attendance' => (bool) ($row['has_attendance'] ?? false),
                'display_order' => (int) $row['display_order'],
                'lesson_date' => $row['lesson_date'],
                'assignment_due_date' => $row['assignment_due_date'] ?? null,
                'has_quiz' => (bool) $row['has_quiz'],
                'live_session_info' => !empty($row['zoom_info'])
                    ? json_decode($row['zoom_info'], true)
                    : null
            ],

            'submission' => $submission
        ];
    }


    public function getUpcomingCohorts(string $slug, int $profileId): array
    {
        $sql = "
        SELECT
            p.id AS program_id,
            p.slug AS program_slug,
            p.name AS program_name,
            p.sponsor AS program_sponsor,
            p.video_url AS program_video_url,
            p.onboarding_steps AS program_onboarding_steps,
            p.whatsapp_group_link AS program_whatsapp_group_link,
            lc.id AS course_id,
            lc.slug AS course_slug,
            lc.title AS course_name,
            lc.description AS course_description,
            lc.image_url AS course_image_url,
            c.id AS cohort_id,
            c.slug AS cohort_slug,
            c.title AS cohort_title,
            c.description AS cohort_description,
            c.start_date,
            c.end_date,
            c.instructor_name,
            c.delivery_mode,
            c.video_url,
            c.image_url AS cohort_image_url,
            c.learning_type,
            c.whatsapp_group_link AS cohort_whatsapp_group_link,
            EXISTS (
                    SELECT 1
                    FROM program_course_cohort_enrollments e
                    WHERE e.cohort_id = c.id
                    AND e.profile_id = :profile_id
            ) AS is_enrolled
        FROM program_course_cohorts c
        INNER JOIN programs p
            ON p.id = c.program_id
        INNER JOIN program_courses pc
            ON pc.program_id = p.id
            AND pc.course_id = c.course_id
            AND pc.is_active = 1
        INNER JOIN learning_courses lc
            ON lc.id = c.course_id
        WHERE c.slug = :slug
            AND p.status = :status
            AND c.status IN ('ongoing', 'upcoming')
        LIMIT 1
        ";

        $rows = $this->programModel->rawQuery($sql, [
            'slug' => $slug,
            'status' => 'published',
            'profile_id' => $profileId,
        ]);

        if (empty($rows)) {
            return [];
        }

        $row = $rows[0];

        return [
            'program' => [
                'id' => (int) $row['program_id'],
                'slug' => $row['program_slug'],
                'name' => $row['program_name'],
                'sponsor' => $row['program_sponsor'],
                'video_url' => $row['program_video_url'] ?? null,
                'onboarding_steps' => isset($row['program_onboarding_steps']) ?
                    json_decode($row['program_onboarding_steps'], true)
                    : null,
                'whatsapp_group_link' => $row['program_whatsapp_group_link'] ?? null,
            ],
            'course' => [
                'courseId' => (int) $row['course_id'],
                'slug' => $row['course_slug'],
                'courseName' => $row['course_name'],
                'description' => $row['course_description'],
                'image_url' => AssetUrl::fromAppUrl($row['course_image_url']),
            ],
            'cohort' => [
                'cohortId' => (int) $row['cohort_id'],
                'slug' => $row['cohort_slug'],
                'title' => $row['cohort_title'],
                'description' => $row['cohort_description'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'instructor_name' => $row['instructor_name'],
                'delivery_mode' => $row['delivery_mode'],
                'video_url' => $row['video_url'],
                'image_url' => AssetUrl::fromAppUrl($row['cohort_image_url']),
                'learning_type' => $row['learning_type'],
                'whatsapp_group_link' => $row['cohort_whatsapp_group_link'] ?? null,
                'is_enrolled' => (bool) $row['is_enrolled'],
            ],
        ];
    }
}
