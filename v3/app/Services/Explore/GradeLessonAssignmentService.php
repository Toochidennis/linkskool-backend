<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CohortTasksSubmission;

class GradeLessonAssignmentService
{
    private CohortTasksSubmission $submission;

    public function __construct(\PDO $pdo)
    {
        $this->submission = new CohortTasksSubmission($pdo);
    }

        public function getLessonSubmissions(int $lessonId): array
    {
        $sql = "
            SELECT
                s.id,
                s.profile_id,
                s.cohort_id,
                s.lesson_id,
                s.submission_type,
                s.text_content,
                s.link_url,
                s.files,
                s.quiz_score,
                s.assigned_score,
                s.remark,
                s.comment,
                s.graded_by,
                s.graded_at,
                s.notified_by,
                s.notified_at,
                s.created_at,
                s.updated_at,
                p.first_name,
                p.last_name
            FROM cohort_tasks_submissions s
            LEFT JOIN program_profiles p
                ON p.id = s.profile_id
            WHERE s.lesson_id = :lesson_id
            ORDER BY s.created_at DESC
        ";

        $rows = $this->submission->rawQuery($sql, [
            'lesson_id' => $lessonId
        ]);

        return array_map(function (array $row) {
            $files = !empty($row['files'])
                ? json_decode($row['files'], true)
                : null;

            $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

            return [
                'id' => (int) $row['id'],
                'lesson_id' => (int) $row['lesson_id'],
                'cohort_id' => $row['cohort_id'],
                'profile' => [
                    'id' => (int) $row['profile_id'],
                    'first_name' => $row['first_name'] ?? null,
                    'last_name' => $row['last_name'] ?? null,
                    'full_name' => $fullName !== '' ? $fullName : null,
                ],
                'submission' => [
                    'type' => $row['submission_type'] ?? null,
                    'text_content' => $row['text_content'] ?? null,
                    'link_url' => $row['link_url'] ?? null,
                    'files' => \is_array($files) ? $files : null,
                    'quiz_score' => $row['quiz_score'] !== null
                        ? (float) $row['quiz_score']
                        : null,
                ],
                'grading' => [
                    'assigned_score' => $row['assigned_score'] !== null
                        ? (float) $row['assigned_score']
                        : null,
                    'remark' => $row['remark'] ?? null,
                    'comment' => $row['comment'] ?? null,
                    'graded_by' => $row['graded_by'] !== null ? (int) $row['graded_by'] : null,
                    'graded_at' => $row['graded_at'] ?? null,
                ],
                'notification' => [
                    'notified_by' => $row['notified_by'] !== null ? (int) $row['notified_by'] : null,
                    'notified_at' => $row['notified_at'] ?? null,
                ],
                'created_at' => $row['created_at'] ?? null,
                'updated_at' => $row['updated_at'] ?? null,
            ];
        }, $rows);
    }

    public function gradeSubmissions(array $data): bool
    {
        $submissionIds = array_column($data['results'], 'submission_id');
        $scoreMap = array_column($data['results'], 'score', 'submission_id');
        $commentMap = array_column($data['results'], 'comment', 'submission_id');

        foreach ($submissionIds as $submissionId) {
            $gradeData = [
                'submission_id' => $submissionId,
                'assigned_score' => $scoreMap[$submissionId] ?? null,
                'comment' => $commentMap[$submissionId] ?? null,
                'graded_by' => $data['graded_by'],
                'notify_student' => $data['notify_student'] ?? false,
            ];

            if (!$this->gradeSubmission($gradeData)) {
                return false;
            }
        }

        return true;
    }

    private function gradeSubmission(array $data): bool
    {
        $score = (float) ($data['assigned_score'] ?? 0);
        $remark = $data['remark'] ?? $this->getRemarkByScore($score);

        $updated =  $this->submission
            ->where('id', $data['submission_id'])
            ->update([
                'assigned_score' => $score,
                'remark' => $remark,
                'comment' => $data['comment'] ?? null,
                'graded_by' => $data['graded_by'],
                'graded_at' => date('Y-m-d H:i:s'),
            ]);

        if (!$updated) {
            return false;
        }

        if (!empty($data['notify_student'])) {
            $this->notifyStudents([$data['submission_id']], $data['graded_by']);
        }

        return true;
    }

    private function getRemarkByScore(float|int $score): string
    {
        $normalizedScore = max(0, min(100, (float) $score));

        if ($normalizedScore >= 85) {
            return 'Excellent Work!';
        }

        if ($normalizedScore >= 70) {
            return 'Good Job!';
        }

        if ($normalizedScore >= 60) {
            return 'Good Effort, but there is room for improvement.';
        }

        if ($normalizedScore >= 50) {
            return 'Fair Effort, but there is room for improvement.';
        }

        return 'Needs Improvement. Please review the material and try again.';
    }

    public function notifyStudents(array $submissionIds, int $notifiedBy): void
    {
        $submissions = $this->submission
            ->in('id', $submissionIds)
            ->get();

        if (!$submissions) {
            return;
        }

        foreach ($submissions as $submission) {
            if (!empty($submission['notified_at'])) {
                continue; // already notified
            }

            EventDispatcher::dispatch(
                new SubmissionGraded(
                    $submission['id'],
                    $submission['profile_id'],
                    $submission['lesson_id'],
                    $submission['assigned_score'],
                    $submission['remark'],
                    $submission['comment']
                )
            );

            $this->submission
                ->where('id', $submission['id'])
                ->update([
                    'notified_at' => date('Y-m-d H:i:s'),
                    'notified_by' => $notifiedBy,
                ]);
        }
    }
}
