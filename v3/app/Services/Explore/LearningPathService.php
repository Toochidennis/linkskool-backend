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
        $courseEnrollmentStatus = $profileId
            ? $this->getUserEnrolledCourseIds($profileId)
            : [];

        $age = $birthDate !== null
            ? (int) date('Y') - (int) substr($birthDate, 0, 4)
            : null;

        $rows = $this->programModel
            ->select([
                'programs.id AS program_id',
                'programs.name AS program_name',
                'programs.description AS program_description',
                'programs.image_url AS program_image_url',
                'programs.age_groups AS program_age_groups',

                'learning_courses.id AS course_id',
                'learning_courses.title AS course_title',
                'learning_courses.description AS course_description',
                'learning_courses.image_url AS course_image_url',

                'program_course_cohorts.id AS active_cohort_id',
                'program_course_cohorts.is_free',
                'program_course_cohorts.trial_type',
                'program_course_cohorts.trial_value',
                'program_course_cohorts.cost'
            ])
            ->join(
                'program_courses',
                'program_courses.program_id = programs.id',
                'LEFT'
            )
            ->join(
                'learning_courses',
                'learning_courses.id = program_courses.course_id',
                'LEFT'
            )
            ->join('program_course_cohorts', function ($join) {
                $join->on('program_course_cohorts.course_id', '=', 'program_courses.id');
                $join->on('program_course_cohorts.status', '=', 'ongoing');
            }, 'LEFT')
            ->where('programs.status', 'published')
            ->where('program_courses.is_active', 1)
            ->get();

        return $this->groupProgramsWithCoursesWithAgeFilter(
            $rows,
            $age,
            $courseEnrollmentStatus
        );
    }

    private function groupProgramsWithCoursesWithAgeFilter(
        array $rows,
        ?int $age,
        array $courseEnrollmentStatus
    ): array {
        $programs = [];

        foreach ($rows as $row) {
            if (!$row['course_id']) {
                continue;
            }

            $pid = $row['program_id'];

            // Initialize program once
            if (!isset($programs[$pid])) {
                $ageGroups = json_decode($row['program_age_groups'], true) ?? [];

                $hasEnrollmentInProgram = isset(
                    $enrolledCourseIds[(int) $row['course_id']]
                );

                if ($age !== null && !$hasEnrollmentInProgram) {
                    if (!$this->matchesAgeGroup($ageGroups, $age)) {
                        continue;
                    }
                }

                $programs[$pid] = [
                    'program_id' => $pid,
                    'name' => $row['program_name'],
                    'description' => $row['program_description'],
                    'image_url' => $row['program_image_url'],
                    'courses' => []
                ];
            }

            // Program might have been skipped
            if (!isset($programs[$pid])) {
                continue;
            }

            $enrollmentStatus = $courseEnrollmentStatus[$row['course_id']] ?? null;

            $programs[$pid]['courses'][] = [
                'course_id' => $row['course_id'],
                'course_name' => $row['course_title'],
                'description' => $row['course_description'],
                'image_url' => $row['course_image_url'],

                'has_active_cohort' => (bool) $row['active_cohort_id'],
                'cohort_id' => $row['active_cohort_id'],

                'is_free' => $row['active_cohort_id']
                    ? (bool) $row['is_free']
                    : null,

                'trial_type' => $row['active_cohort_id']
                    ? $row['trial_type']
                    : null,

                'trial_value' => $row['active_cohort_id']
                    ? (int) $row['trial_value']
                    : null,

                'cost' => $row['active_cohort_id']
                    ? (float) $row['cost']
                    : null,

                'is_enrolled' => $enrollmentStatus !== null,
                'is_completed' => $enrollmentStatus === 'completed',
                'enrollment_status' => $enrollmentStatus
            ];
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

    private function getUserEnrolledCourseIds(int $profileId): array
    {
        $rows = $this->programCourseCohortModel
            ->rawQuery(
                "SELECT DISTINCT course_id, status
                        FROM program_course_cohort_enrollments
                        WHERE profile_id = :profile_id",
                ['profile_id' => $profileId]
            );

        return array_column($rows, 'status', 'course_id');
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
            ->where('cohort_lesson_id', $lessonId)
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
