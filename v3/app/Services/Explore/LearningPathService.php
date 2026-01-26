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
        return $this->programCourseCohortModel
            ->where('id', $cohortId)
            ->where('status', 'ongoing')
            ->orderBy('start_date', 'ASC')
            ->first();
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
                s.created_at AS submitted_at
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
                'is_final_lesson' => (bool) $row['is_final_lesson'],
                'display_order' => (int) $row['display_order'],
                'lesson_date' => $row['lesson_date'],
                'assignment_due_date' => $row['assignment_due_date']
            ],

            'submission' => $row['assignment'] !== null ? [
                'assignment' => json_decode($row['assignment'], true),
                'quiz_score' => $row['quiz_score'] !== null
                    ? (int) $row['quiz_score']
                    : null,
                'submitted_at' => $row['submitted_at']
            ] : null
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
}
