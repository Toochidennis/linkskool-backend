<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CohortLessonProgress;
use V3\App\Models\Explore\ProgramCohortCourseEnrollment;

class CohortLessonProgressService
{
    private CohortLessonProgress $cohortLessonProgress;
    private ProgramCohortCourseEnrollment $enrollment;

    public function __construct(\PDO $pdo)
    {
        $this->cohortLessonProgress = new CohortLessonProgress($pdo);
        $this->enrollment = new ProgramCohortCourseEnrollment($pdo);
    }

    public function markLessonAsCompleted(array $data): bool
    {
        $exists = $this->cohortLessonProgress
            ->where('profile_id', $data['profile_id'])
            ->where('lesson_id', $data['lesson_id'])
            ->exists();

        if (!$exists) {
            $this->cohortLessonProgress->insert([
                'profile_id' => $data['profile_id'],
                'lesson_id' => $data['lesson_id'],
                'course_id' => $data['course_id'],
                'cohort_id' => $data['cohort_id'],
                'program_id' => $data['program_id'],
                'is_completed' => 1,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // always update last activity
        $this->updateEnrollmentProgress($data);

        // only mark completed if rules pass
        if ($this->hasCompletedCohort($data)) {
            $this->markEnrollmentCompleted($data);
        }

        return true;
    }

    private function hasCompletedCohort(array $data): bool
    {
        $sql = "
            SELECT
                COUNT(l.id) AS total_lessons,
                COUNT(p.id) AS completed_lessons,
                SUM(CASE 
                    WHEN l.is_final_lesson = 1 AND p.is_completed = 1 THEN 1 
                    ELSE 0 
                END) AS final_lesson_completed
            FROM program_course_cohort_lessons l
            LEFT JOIN cohort_course_lesson_progress p
                ON p.lesson_id = l.id
                AND p.profile_id = :profile_id
                AND p.is_completed = 1
            WHERE l.cohort_id = :cohort_id
            AND l.status = 'published'
        ";

        $row = $this->cohortLessonProgress->rawQuery($sql, [
            'profile_id' => $data['profile_id'],
            'cohort_id'  => $data['cohort_id'],
        ])[0] ?? null;

        if (!$row) {
            return false;
        }

        return
            (int) $row['total_lessons'] > 0 &&
            (int) $row['total_lessons'] === (int) $row['completed_lessons'] &&
            (int) $row['final_lesson_completed'] === 1;
    }

    private function updateEnrollmentProgress(array $data): void
    {
        $this->enrollment
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->where('course_id', $data['course_id'])
            ->update([
                'last_lesson_completed_at' => date('Y-m-d H:i:s')
            ]);
    }

    private function markEnrollmentCompleted(array $data): void
    {
        $this->enrollment
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->where('course_id', $data['course_id'])
            ->where('status', '!=', 'completed')
            ->update([
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ]);
    }
}
