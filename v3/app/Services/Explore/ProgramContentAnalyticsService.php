<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Program;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class ProgramContentAnalyticsService
{
    private Program $programModel;
    private ProgramCourseCohortLesson $programCourseCohortLessonModel;
    private ProgramContentService $programContentService;

    public function __construct(\PDO $pdo)
    {
        $this->programModel = new Program($pdo);
        $this->programCourseCohortLessonModel = new ProgramCourseCohortLesson($pdo);
        $this->programContentService = new ProgramContentService($pdo);
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
            'total_courses_enrolled' => (int) ($row['total_courses_enrolled'] ?? 0),
            'total_courses_completed' => (int) ($row['total_courses_completed'] ?? 0),

            'total_lessons' => (int) ($row['total_lessons'] ?? 0),
            'lessons_completed' => (int) ($row['lessons_completed'] ?? 0),

            'overall_quiz_score' => ($row['avg_quiz_score'] ?? null) !== null
                ? round((float) $row['avg_quiz_score'], 2)
                : 0.0,

            'assignments_submitted' => (int) ($row['assignments_submitted'] ?? 0),

            'programs' => $this->programContentService->getProgramsWithCourses(null, $profileId)
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
}
