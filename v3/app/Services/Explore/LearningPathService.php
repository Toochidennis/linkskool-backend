<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CohortLessonQuiz;
use V3\App\Models\Explore\Program;
use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class LearningPathService
{
    private Program $programModel;
    private ProgramCourseCohort $programCourseCohortModel;
    private ProgramCourseCohortLesson $programCourseCohortLessonModel;
    private CohortLessonQuiz $cohortLessonQuizModel;

    public function __construct(\PDO $pdo)
    {
        $this->programModel = new Program($pdo);
        $this->programCourseCohortModel = new ProgramCourseCohort($pdo);
        $this->programCourseCohortLessonModel = new ProgramCourseCohortLesson($pdo);
        $this->cohortLessonQuizModel = new CohortLessonQuiz($pdo);
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
                    'image_url' => $row['program_image_url'],
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
                'image_url' => $row['course_image_url'],

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

    public function getActiveCohortByCourse(int $cohortId): array
    {
        $query = "
            SELECT
                p.*,
                lc.title AS course_title
            FROM program_course_cohorts p
            INNER JOIN learning_courses lc
                ON lc.id = p.course_id
            WHERE p.id = :cohort_id
            ORDER BY p.start_date ASC
            LIMIT 1
        ";

        $rows = $this->programCourseCohortModel->rawQuery($query, [
            'cohort_id' => $cohortId
        ]);

        $rows[0]['course_name'] = $rows[0]['course_title'];

        return $rows[0] ?? [];
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
                    'image' => $row['image_url'],
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


    public function getLessonQuiz(int $lessonId): array
    {
        $rows = $this->cohortLessonQuizModel
            ->select([
                'question_id',
                'title AS question',
                'type AS question_type',
                'answer',
                'correct'
            ])
            ->where('lesson_id', $lessonId)
            ->get();

        return array_map(fn($q) => [
            'id' => $q['question_id'],
            'question' => $q['question'],
            'type' => $q['question_type'],
            'options' => json_decode($q['answer'], true),
            'correct' => json_decode($q['correct'], true)
        ], $rows);
    }

    public function getUserLearningStats(int $profileId): array
    {
        $sql = "
            SELECT
                COUNT(DISTINCT e.cohort_id) AS total_courses_enrolled,
                COUNT(DISTINCT CASE 
                    WHEN e.status = 'completed' THEN e.cohort_id 
                END) AS total_courses_completed,

                COUNT(DISTINCT l.id) AS total_lessons,
                COUNT(DISTINCT CASE 
                    WHEN p.is_completed = 1 THEN l.id 
                END) AS lessons_completed,

                AVG(s.quiz_score) AS avg_quiz_score,
                COUNT(DISTINCT s.id) AS assignments_submitted

            FROM program_course_cohort_enrollments e

            INNER JOIN program_course_cohorts c
                ON c.id = e.cohort_id
                AND c.status = 'ongoing'

            INNER JOIN program_course_cohort_lessons l
                ON l.cohort_id = c.id
                AND l.status = 'published'

            LEFT JOIN cohort_lesson_progress p
                ON p.lesson_id = l.id
                AND p.profile_id = e.profile_id

            LEFT JOIN cohort_tasks_submissions s
                ON s.lesson_id = l.id
                AND s.profile_id = e.profile_id

            WHERE e.profile_id = :profile_id
        ";

        $row = $this->programModel->rawQuery($sql, [
            'profile_id' => $profileId
        ])[0] ?? [];

        return [
            'total_courses_enrolled'  => (int) ($row['total_courses_enrolled'] ?? 0),
            'total_courses_completed' => (int) ($row['total_courses_completed'] ?? 0),

            'total_lessons'     => (int) ($row['total_lessons'] ?? 0),
            'lessons_completed' => (int) ($row['lessons_completed'] ?? 0),

            'overall_quiz_score' => $row['avg_quiz_score'] !== null
                ? round((float) $row['avg_quiz_score'], 2)
                : 0.0,

            'assignments_submitted' => (int) ($row['assignments_submitted'] ?? 0),

            'programs' => $this->getProgramsWithCourses(null, $profileId)
        ];
    }

    public function getLessonPerformanceByProfile(int $profileId, int $cohortId): array
    {
        $rows = $this->fetchLessonPerformanceRowsByProfile($profileId, $cohortId);

        return $this->buildLessonPerformanceSummary($rows);
    }

    public function getLeaderboardByCohort(int $profileId, int $cohortId): array
    {
        $rows = $this->fetchLessonPerformanceRowsByCohort($cohortId);
        $profiles = [];

        foreach ($rows as $row) {
            $rowProfileId = (int) ($row['profile_id'] ?? 0);
            if ($rowProfileId <= 0) {
                continue;
            }

            if (!isset($profiles[$rowProfileId])) {
                $firstName = trim((string) ($row['first_name'] ?? ''));
                $lastName = trim((string) ($row['last_name'] ?? ''));
                $fullName = trim($firstName . ' ' . $lastName);

                $profiles[$rowProfileId] = [
                    'profile_id' => $rowProfileId,
                    'name' => $fullName !== '' ? $fullName : ('Profile ' . $rowProfileId),
                    'rows' => [],
                ];
            }

            $profiles[$rowProfileId]['rows'][] = $row;
        }

        $leaderboard = [];

        foreach ($profiles as $entry) {
            $summary = $this->buildLessonPerformanceSummary($entry['rows']);
            $leaderboard[] = [
                'profile_id' => $entry['profile_id'],
                'name' => $entry['name'],
                'score' => (float) ($summary['overall_score_percentage'] ?? 0.0),
            ];
        }

        usort($leaderboard, function (array $left, array $right): int {
            $scoreComparison = $right['score'] <=> $left['score'];

            if ($scoreComparison !== 0) {
                return $scoreComparison;
            }

            return strcasecmp($left['name'], $right['name']);
        });

        $topUsers = [];
        $currentProfilePosition = null;

        foreach ($leaderboard as $index => $entry) {
            $position = $index + 1;

            if ($entry['profile_id'] === $profileId) {
                $currentProfilePosition = $position;
            }

            if ($index < 20) {
                $topUsers[] = [
                    'position' => $position,
                    'name' => $entry['name'],
                ];
            }
        }

        return [
            'leaderboard' => $topUsers,
            'profile_position' => $currentProfilePosition,
        ];
    }

    private function fetchLessonPerformanceRowsByProfile(int $profileId, int $cohortId): array
    {
        $sql = "
            SELECT DISTINCT
                l.id AS lesson_id,
                l.title AS lesson_title,
                l.display_order,
                l.cohort_id,
                l.assignment_url,
                l.assignment_instructions,
                l.assignment_due_date,
                l.zoom_info,
                c.zoom_link,
                s.quiz_score,
                s.assigned_score,
                s.submission_type,
                s.text_content,
                s.link_url,
                s.files,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM cohort_lesson_quizzes q
                        WHERE q.lesson_id = l.id
                    ) THEN 1
                    ELSE 0
                END AS has_quiz,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM cohort_lesson_attendance a
                        WHERE a.lesson_id = l.id
                          AND a.profile_id = :attendance_profile_id
                    ) THEN 1
                    ELSE 0
                END AS has_attendance
            FROM program_course_cohort_enrollments e
            INNER JOIN program_course_cohort_lessons l
                ON l.cohort_id = e.cohort_id
               AND l.status = 'published'
            LEFT JOIN program_course_cohorts c
                ON c.id = l.cohort_id
            LEFT JOIN cohort_tasks_submissions s
                ON s.id = (
                    SELECT s2.id
                    FROM cohort_tasks_submissions s2
                    WHERE s2.lesson_id = l.id
                      AND s2.profile_id = :submission_profile_id
                    ORDER BY s2.id DESC
                    LIMIT 1
                )
            WHERE e.profile_id = :profile_id
              AND e.cohort_id = :cohort_id
            ORDER BY l.display_order ASC, l.id ASC
        ";

        return $this->programCourseCohortLessonModel->rawQuery($sql, [
            'profile_id' => $profileId,
            'cohort_id' => $cohortId,
            'submission_profile_id' => $profileId,
            'attendance_profile_id' => $profileId,
        ]);
    }

    private function fetchLessonPerformanceRowsByCohort(int $cohortId): array
    {
        $sql = "
            SELECT DISTINCT
                e.profile_id,
                p.first_name,
                p.last_name,
                l.id AS lesson_id,
                l.title AS lesson_title,
                l.display_order,
                l.cohort_id,
                l.assignment_url,
                l.assignment_instructions,
                l.assignment_due_date,
                l.zoom_info,
                c.zoom_link,
                s.quiz_score,
                s.assigned_score,
                s.submission_type,
                s.text_content,
                s.link_url,
                s.files,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM cohort_lesson_quizzes q
                        WHERE q.lesson_id = l.id
                    ) THEN 1
                    ELSE 0
                END AS has_quiz,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM cohort_lesson_attendance a
                        WHERE a.lesson_id = l.id
                          AND a.profile_id = e.profile_id
                    ) THEN 1
                    ELSE 0
                END AS has_attendance
            FROM program_course_cohort_enrollments e
            INNER JOIN program_profiles p
                ON p.id = e.profile_id
            INNER JOIN program_course_cohort_lessons l
                ON l.cohort_id = e.cohort_id
               AND l.status = 'published'
            LEFT JOIN program_course_cohorts c
                ON c.id = l.cohort_id
            LEFT JOIN cohort_tasks_submissions s
                ON s.id = (
                    SELECT s2.id
                    FROM cohort_tasks_submissions s2
                    WHERE s2.lesson_id = l.id
                      AND s2.profile_id = e.profile_id
                    ORDER BY s2.id DESC
                    LIMIT 1
                )
            WHERE e.cohort_id = :cohort_id
            ORDER BY e.profile_id ASC, l.display_order ASC, l.id ASC
        ";

        return $this->programCourseCohortLessonModel->rawQuery($sql, [
            'cohort_id' => $cohortId,
        ]);
    }

    private function buildLessonPerformanceSummary(array $rows): array
    {
        $attendanceSupposed = 0;
        $attendanceTaken = 0;
        $assignmentsSupposed = 0;
        $assignmentsDone = 0;
        $quizzesSupposed = 0;
        $quizzesTaken = 0;
        $scoreSum = 0.0;
        $scoreCount = 0;
        $lessons = [];

        foreach ($rows as $row) {
            $hasAttendance = (bool) ($row['has_attendance'] ?? false);
            $hasQuiz = (bool) ($row['has_quiz'] ?? false);
            $quizScore = $row['quiz_score'] !== null ? (float) $row['quiz_score'] : null;
            $assignmentScore = $row['assigned_score'] !== null ? (float) $row['assigned_score'] : null;

            $attendanceExpected = $this->isAttendanceExpected($row);
            $assignmentExpected = $this->isAssignmentExpected($row);
            $assignmentDone = $this->isAssignmentDone($row);
            $quizTaken = $quizScore !== null;

            if ($attendanceExpected) {
                $attendanceSupposed++;
                if ($hasAttendance) {
                    $attendanceTaken++;
                }
            }

            if ($assignmentExpected) {
                $assignmentsSupposed++;
            }

            if ($assignmentDone) {
                $assignmentsDone++;
            }

            if ($hasQuiz) {
                $quizzesSupposed++;
            }

            if ($quizTaken) {
                $quizzesTaken++;
            }

            if ($hasQuiz) {
                $scoreSum += $quizScore ?? 0.0;
                $scoreCount++;
            }

            if ($assignmentExpected) {
                $scoreSum += $assignmentScore ?? 0.0;
                $scoreCount++;
            }

            $lessons[] = [
                'lesson_id' => (int) $row['lesson_id'],
                'title' => $row['lesson_title'],
                'display_order' => (int) $row['display_order'],
                'attendance_taken' => $hasAttendance,
                'quiz_score' => $quizScore,
                'assignment_score' => $assignmentScore,
            ];
        }

        return [
            'overall_score_percentage' => $scoreCount > 0
                ? round($scoreSum / $scoreCount, 2)
                : 0.0,
            'attendance' => [
                'taken' => $attendanceTaken,
                'supposed' => $attendanceSupposed,
            ],
            'assignments' => [
                'taken' => $assignmentsDone,
                'supposed' => $assignmentsSupposed,
            ],
            'quizzes' => [
                'taken' => $quizzesTaken,
                'supposed' => $quizzesSupposed,
            ],
            'lessons' => $lessons,
        ];
    }

    private function isAttendanceExpected(array $row): bool
    {
        if ($this->hasNonEmptyValue($row['zoom_link'] ?? null)) {
            return true;
        }

        if (!$this->hasNonEmptyValue($row['zoom_info'] ?? null)) {
            return false;
        }

        $zoomInfo = json_decode((string) $row['zoom_info'], true);
        if (\is_array($zoomInfo)) {
            if ($this->hasNonEmptyValue($zoomInfo['url'] ?? null)) {
                return true;
            }

            return !empty($zoomInfo);
        }

        return false;
    }

    private function isAssignmentExpected(array $row): bool
    {
        return $this->hasNonEmptyValue($row['assignment_url'] ?? null)
            || $this->hasNonEmptyValue($row['assignment_instructions'] ?? null)
            || $this->hasNonEmptyValue($row['assignment_due_date'] ?? null);
    }

    private function isAssignmentDone(array $row): bool
    {
        if ($this->hasNonEmptyValue($row['submission_type'] ?? null)) {
            return true;
        }

        if ($this->hasNonEmptyValue($row['text_content'] ?? null)) {
            return true;
        }

        if ($this->hasNonEmptyValue($row['link_url'] ?? null)) {
            return true;
        }

        if (!$this->hasNonEmptyValue($row['files'] ?? null)) {
            return false;
        }

        $files = json_decode((string) $row['files'], true);
        return \is_array($files) && !empty($files);
    }

    private function hasNonEmptyValue(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
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
                'image_url' => $row['course_image_url'],
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
                'image_url' => $row['cohort_image_url'],
                'learning_type' => $row['learning_type'],
                'whatsapp_group_link' => $row['cohort_whatsapp_group_link'] ?? null,
                'is_enrolled' => (bool) $row['is_enrolled'],
            ],
        ];
    }
}
