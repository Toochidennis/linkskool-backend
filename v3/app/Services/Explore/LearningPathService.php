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

            lc.id           AS course_id,
            lc.title        AS course_title,
            lc.description  AS course_description,
            lc.image_url    AS course_image_url,

            c.id            AS active_cohort_id,
            c.is_free       AS is_free,
            c.trial_type    AS trial_type,
            c.trial_value   AS trial_value,
            c.cost          AS cost,

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
            AND c.status = 'ongoing'

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
                'trial_expiry_date' => $row['trial_expiry_date']
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
            AND p.status = 'ongoing'
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

    public function getLessonById(int $lessonId, int $profileId): array
    {
        $sql = "
            SELECT 
                l.*,
                s.assignment,
                s.quiz_score,
                s.created_at AS submitted_at,
                EXISTS (
                    SELECT 1 
                    FROM cohort_lesson_quizzes q
                    WHERE q.lesson_id = l.id
                ) AS has_quiz
            FROM program_course_cohort_lessons l
            LEFT JOIN cohort_tasks_submissions s
                ON s.lesson_id = l.id
                AND s.profile_id = :profile_id
            WHERE l.id = :lesson_id
            LIMIT 1
        ";

        $row = $this->programCourseCohortLessonModel
            ->rawQuery($sql, [
                'lesson_id' => $lessonId,
                'profile_id'   => $profileId
            ]);

        if (empty($row)) {
            return [];
        }

        return $this->formatLessonWithSubmission($row[0]);
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
                'files' => $files ?: null,
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
                'submitted_at' => $row['submitted_at'] ?? null,
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
                'assignment_submission_type' => $row['assignment_submission_type'] ?? 'upload',
                'is_final_lesson' => (bool) $row['is_final_lesson'],
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


    public function getCohortLessonsWithSubmission(
        int $cohortId,
        int $profileId
    ): array {
        $sql = "
            SELECT 
                l.*,
                s.assignment,
                s.quiz_score,
                s.created_at AS submitted_at,
                EXISTS (
                    SELECT 1 
                    FROM cohort_lesson_quizzes q
                    WHERE q.lesson_id = l.id
                ) AS has_quiz
            FROM program_course_cohort_lessons l
            LEFT JOIN cohort_tasks_submissions s
                ON s.lesson_id = l.id
                AND s.profile_id = :profile_id
            WHERE l.cohort_id = :cohort_id
            AND l.status = 'published'
            ORDER BY l.display_order ASC
        ";

        $rows = $this->programCourseCohortLessonModel->rawQuery($sql, [
            'cohort_id'  => $cohortId,
            'profile_id' => $profileId
        ]);

        return array_map([$this, 'formatLessonWithSubmission'], $rows);
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
}
