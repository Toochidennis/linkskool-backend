<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\AiGradingReview;
use V3\App\Models\Explore\CohortTasksSubmission;

class BulkAutoGradeService
{
    private AutoGradeService $autoGradeService;
    private AiGradingReview $review;
    private PdfService $pdfService;

    public function __construct(\PDO $pdo, private CohortTasksSubmission $submission)
    {
        $this->autoGradeService = new AutoGradeService();
        $this->pdfService = new PdfService();
        $this->review  = new AiGradingReview($pdo);
    }

    public function process(array $submissionIds, int $gradedBy): string
    {
        $batchId = 'AI-' . date('YmdHis') . '-' . uniqid();
        $this->grade([
            'submission_ids' => $submissionIds,
            'graded_by' => $gradedBy,
            'batch_id' => $batchId,
            'persist_review' => true,
            'apply_submission_state' => true,
        ]);
        return $batchId;
    }

    public function autoGradeSubmissions(array $data): array
    {
        return $this->grade($data);
    }

    private function grade(array $data): array
    {
        $submissionIds = array_values(array_unique(array_map('intval', $data['submission_ids'] ?? [])));
        $batchId = $data['batch_id'] ?? ('AI-' . date('YmdHis') . '-' . uniqid());
        $gradedBy = isset($data['graded_by']) ? (int) $data['graded_by'] : null;
        $persistReview = (bool) ($data['persist_review'] ?? false);
        $applySubmissionState = (bool) ($data['apply_submission_state'] ?? false);

        if (empty($submissionIds)) {
            return [
                'batch_id' => $batchId,
                'results' => [],
                'summary' => [
                    'total' => 0,
                    'processed' => 0,
                    'failed' => 0,
                ],
            ];
        }

        $rows = $this->fetchSubmissionsByIds($submissionIds);
        [$results, $failed] = $this->gradeRows(
            $rows,
            $batchId,
            $gradedBy,
            $persistReview,
            $applySubmissionState
        );

        if ($persistReview) {
            $results = $this->fetchBatchResults($batchId);
        }

        return [
            'batch_id' => $batchId,
            'results' => $results,
            'summary' => [
                'total' => count($submissionIds),
                'processed' => count($results),
                'failed' => $failed,
            ],
        ];
    }

    private function fetchSubmissionsByIds(array $submissionIds): array
    {
        $bindings = [];
        $placeholders = [];
        foreach ($submissionIds as $index => $id) {
            $key = 'id_' . $index;
            $placeholders[] = ':' . $key;
            $bindings[$key] = $id;
        }

        $sql = "
            SELECT
                s.id,
                s.lesson_id,
                s.profile_id,
                s.submission_type,
                s.text_content,
                s.link_url,
                s.files,
                l.title AS lesson_title,
                l.assignment_instructions,
                l.assignment_url
            FROM cohort_tasks_submissions s
            LEFT JOIN program_course_cohort_lessons l
                ON l.id = s.lesson_id
            WHERE s.id IN (" . implode(', ', $placeholders) . ")
        ";

        return $this->submission->rawQuery($sql, $bindings);
    }

    private function fetchBatchResults(string $batchId): array
    {
        $sql = "
            SELECT
                r.*,
                s.profile_id,
                s.lesson_id,
                s.submission_type,
                s.text_content,
                s.link_url,
                s.files,
                l.assignment_url,
                p.first_name,
                p.last_name
            FROM ai_grading_reviews r
            LEFT JOIN cohort_tasks_submissions s
                ON s.id = r.submission_id
            LEFT JOIN program_course_cohort_lessons l
                ON l.id = s.lesson_id
            LEFT JOIN program_profiles p
                ON p.id = s.profile_id
            WHERE r.batch_id = :batch_id
            ORDER BY r.id ASC
        ";

        $rows = $this->submission->rawQuery($sql, ['batch_id' => $batchId]);

        return array_map(function (array $row): array {
            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));
            $fullName = trim($firstName . ' ' . $lastName);

            return [
                'submission_id' => (int) $row['submission_id'],
                'profile_id' => (int) $row['profile_id'],
                'student_name' => $fullName !== '' ? $fullName : null,
                'lesson_id' => (int) $row['lesson_id'],
                'assignment_url' => $row['assignment_url'] ?? null,
                'submitted' => $this->buildSubmittedPayload($row),
                'score' => max(0, min(100, (float) ($row['score'] ?? 0))),
                'comment' => $row['comment'] ?? 'Grading failed.',
                'status' => $row['status'],
            ];
        }, $rows);
    }

    private function gradeRows(
        array $rows,
        string $batchId,
        ?int $gradedBy,
        bool $persistReview,
        bool $applySubmissionState
    ): array {
        $results = [];
        $failed = 0;

        foreach ($rows as $row) {
            $questionText = $this->buildQuestion($row);
            $answerText = $this->buildAnswer($row);
            $graded = $this->autoGradeService->grade($questionText, $answerText);

            $score = max(0, min(100, (float) ($graded['score'] ?? 0)));
            $comment = $graded['comment'] ?? 'Grading failed.';

            if (isset($graded['error'])) {
                $failed++;
            }

            if ($applySubmissionState && $gradedBy !== null && !isset($graded['error'])) {
                $this->applyAutoGradeToSubmission($row, $score, $comment, $gradedBy);
            }

            if ($persistReview && $gradedBy !== null) {
                $this->review->insert([
                    'batch_id' => $batchId,
                    'lesson_id' => $row['lesson_id'],
                    'submission_id' => $row['id'],
                    'score' => $score,
                    'comment' => $comment,
                    'status' => 'pending',
                    'created_by' => $gradedBy,
                ]);
            }

            $results[] = [
                'submission_id' => (int) $row['id'],
                'profile_id' => (int) ($row['profile_id'] ?? 0),
                'lesson_id' => (int) ($row['lesson_id'] ?? 0),
                'assignment_url' => $row['assignment_url'] ?? null,
                'submitted' => $this->buildSubmittedPayload($row),
                'score' => $score,
                'comment' => $comment,
                'ai_error' => $graded['error'] ?? null,
            ];
        }

        return [$results, $failed];
    }

    private function applyAutoGradeToSubmission(
        array $submission,
        float $score,
        string $comment,
        int $gradedBy
    ): void {
        $this->submission
            ->where('id', $submission['id'])
            ->update([
                'assigned_score' => $score,
                'remark' => $this->getRemarkByScore($score),
                'comment' => $comment,
                'graded_by' => $gradedBy,
                'graded_at' => date('Y-m-d H:i:s'),
            ]);
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

    private function buildQuestion(array $submission): string
    {
        $assignmentPath = $this->toAbsolutePath((string) ($submission['assignment_url'] ?? ''));
        if ($assignmentPath !== null) {
            $text = trim((string) $this->pdfService->extract($assignmentPath));
            if ($text !== '') {
                return $text;
            }
        }

        $lessonTitle = trim((string) ($submission['lesson_title'] ?? ''));
        $instructions = trim((string) ($submission['assignment_instructions'] ?? ''));

        $parts = [];
        if ($lessonTitle !== '') {
            $parts[] = "Lesson: {$lessonTitle}";
        }
        if ($instructions !== '') {
            $parts[] = "Assignment Instructions: {$instructions}";
        }

        if (empty($parts)) {
            $parts[] = 'Grade this assignment submission for quality, relevance, and completeness.';
        }

        return implode("\n\n", $parts);
    }

    private function buildAnswer(array $submission): string
    {
        $submissionType = trim((string) ($submission['submission_type'] ?? $submission['type'] ?? ''));
        $textContent = trim((string) ($submission['text_content'] ?? ''));
        $linkUrl = trim((string) ($submission['link_url'] ?? ''));
        $files = $this->decodeFiles((string) ($submission['files'] ?? ''));

        if ($submissionType === 'text' && $textContent !== '') {
            return $textContent;
        }

        if ($submissionType === 'upload' && !empty($files)) {
            $firstPath = $this->toAbsolutePath((string) ($files[0]['file_name'] ?? ''));
            if ($firstPath !== null) {
                $text = trim((string) $this->pdfService->extract($firstPath));
                if ($text !== '') {
                    return $text;
                }
            }
        }

        $parts = [];

        if ($submissionType !== '') {
            $parts[] = "Submission Type: {$submissionType}";
        }

        if ($textContent !== '') {
            $parts[] = "Text Answer:\n{$textContent}";
        }

        if ($linkUrl !== '') {
            $parts[] = "Submitted Link: {$linkUrl}";
        }

        if (!empty($files)) {
            $fileNames = [];
            foreach ($files as $file) {
                $name = trim((string) ($file['file_name'] ?? $file['old_file_name'] ?? ''));
                if ($name !== '') {
                    $fileNames[] = $name;
                }
            }

            if (!empty($fileNames)) {
                $parts[] = 'Attached Files: ' . implode(', ', $fileNames);
            }
        }

        if (empty($parts)) {
            return 'No answer content provided.';
        }

        return implode("\n\n", $parts);
    }

    private function decodeFiles(string $encoded): array
    {
        $files = json_decode($encoded, true);
        return \is_array($files) ? $files : [];
    }

    private function buildSubmittedPayload(array $submission): array
    {
        return [
            'submission_type' => $submission['submission_type'] ?? $submission['type'] ?? null,
            'text_content' => $submission['text_content'] ?? null,
            'link_url' => $submission['link_url'] ?? null,
            'files' => $this->decodeFiles((string) ($submission['files'] ?? '')),
        ];
    }

    private function toAbsolutePath(string $path): ?string
    {
        $relativePath = trim($path);
        if ($relativePath === '') {
            return null;
        }

        $fullPath = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/')
            . '/'
            . ltrim($relativePath, '/');

        return is_file($fullPath) ? $fullPath : null;
    }
}
