<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\SubmissionGraded;
use V3\App\Models\Explore\CohortLessonQuiz;
use V3\App\Models\Explore\CohortTasksSubmission;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class ProgramCourseCohortLessonService
{
    private ProgramCourseCohortLesson $cohortLesson;
    private CohortLessonQuiz $cohortLessonQuiz;
    private CohortTasksSubmission $submission;
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->cohortLesson = new ProgramCourseCohortLesson($pdo);
        $this->cohortLessonQuiz = new CohortLessonQuiz($pdo);
        $this->submission = new CohortTasksSubmission($pdo);
    }

    public function addLessonToCohort(array $data): bool
    {
        try {
            $this->pdo->beginTransaction();

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));

            $payload = [
                'slug' => $slug,
                'cohort_id' => $data['cohort_id'],
                'course_id' => $data['course_id'],
                'program_id' => $data['program_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'goals' => $data['goals'] ?? null,
                'objectives' => $data['objectives'] ?? null,
                'recorded_video_url' => $data['recorded_video_url'] ?? null,
                'video_url' => $data['video_url'] ?? null,
                'zoom_info' => json_encode($data['zoom_info'] ?? []),
                'display_order' => $data['display_order'],
                'write_up_content' => $data['write_up_content'] ?? null,
                'assignment_instructions' => $data['assignment_instructions'] ?? null,
                'assignment_due_date' => !empty($data['assignment_due_date']) ? $data['assignment_due_date'] : null,
                'assignment_submission_type' => $data['assignment_submission_type'] ?? 'upload',
                'is_final_lesson' => $data['is_final_lesson'] ?? false,
                'author_name' => $data['author_name'],
                'author_id' => $data['author_id'],
                'lesson_date' => $data['lesson_date'],
                'status' => $data['status'] ?? 'draft',
            ];

            if ($data['is_final_lesson'] && !isset($_FILES['certificate'])) {
                throw new \Exception('Certificate file is required for final lessons.');
            }

            if ($this->isYouTubeUrl($data['video_url'])) {
                $payload['thumbnail'] = $this->getYouTubeThumbnail($data['video_url']);
            }

            $fileUrls = $this->processNewFiles();
            $payload = [...$payload, ...$fileUrls];

            $lessonId = $this->cohortLesson->insert($payload);
            if (!$lessonId) {
                throw new \Exception("Failed to insert lesson record");
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateLesson(array $data)
    {
        try {
            $this->pdo->beginTransaction();

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));

            $payload = [
                'slug' => $slug,
                'cohort_id' => $data['cohort_id'],
                'course_id' => $data['course_id'],
                'program_id' => $data['program_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'goals' => $data['goals'] ?? null,
                'objectives' => $data['objectives'] ?? null,
                'recorded_video_url' => $data['recorded_video_url'] ?? null,
                'video_url' => $data['video_url'] ?? null,
                'zoom_info' => json_encode($data['zoom_info'] ?? []),
                'display_order' => $data['display_order'],
                'write_up_content' => $data['write_up_content'] ?? null,
                'assignment_instructions' => $data['assignment_instructions'] ?? null,
                'assignment_due_date' => !empty($data['assignment_due_date']) ? $data['assignment_due_date'] : null,
                'assignment_submission_type' => $data['assignment_submission_type'] ?? 'upload',
                'is_final_lesson' => $data['is_final_lesson'] ?? false,
                'lesson_date' => $data['lesson_date'],
                'status' => $data['status'] ?? 'draft',
            ];

            if ($data['is_final_lesson'] && !isset($_FILES['certificate'])) {
                throw new \Exception('Certificate file is required for final lessons.');
            }

            if ($this->isYouTubeUrl($data['video_url'])) {
                $payload['thumbnail'] = $this->getYouTubeThumbnail($data['video_url']);
            }

            $fileUrls = $this->processNewFiles(isUpdate: true);

            $payload = [...$payload, ...$fileUrls];

            $lessonId = $this->cohortLesson
                ->where('id', $data['lesson_id'])
                ->update(array_filter($payload, fn($value) => $value !== null));

            if (!$lessonId) {
                throw new \Exception("Failed to insert lesson record");
            }

            $this->cleanupOldFiles($data, $fileUrls);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $lessonId, string $status)
    {
        return $this->cohortLesson
            ->where('id', $lessonId)
            ->update(['status' => $status]);
    }

    private function processNewFiles($isUpdate = false): array
    {
        $urls = [
            'assignment_url' => null,
            'material_url' => null,
            'certificate_url' => null,
        ];

        $material = $_FILES['material'] ?? null;

        if (!$material && !$isUpdate) {
            throw new \Exception("Material file is required.");
        }

        if ($material) {
            $urls['material_url'] = StorageService::saveFile($material);
        }

        if (isset($_FILES['assignment'])) {
            $urls['assignment_url'] = StorageService::saveFile($_FILES['assignment']);
        }
        if (isset($_FILES['certificate'])) {
            $urls['certificate_url'] = StorageService::saveFile($_FILES['certificate']);
        }

        return $urls;
    }

    private function cleanupOldFiles(array $data, array $fileUrls): void
    {
        if (isset($data['old_assignment_url'], $fileUrls['assignment_url'])) {
            StorageService::deleteFile($data['old_assignment_url']);
        }
        if (isset($data['old_material_url'], $fileUrls['material_url'])) {
            StorageService::deleteFile($data['old_material_url']);
        }
        if (isset($data['old_certificate_url'], $fileUrls['certificate_url'])) {
            StorageService::deleteFile($data['old_certificate_url']);
        }
    }

    private function isYouTubeUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return \in_array($host, [
            'www.youtube.com',
            'youtube.com',
            'm.youtube.com',
            'youtu.be'
        ], true);
    }

    private function getYouTubeVideoId(string $url): ?string
    {
        // Pattern for youtube.com/watch?v=ID
        if (preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Pattern for youtu.be/ID
        if (preg_match('/youtu\.be\/([^?&]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Pattern for youtube.com/embed/ID
        if (preg_match('/\/embed\/([^?&]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function getYouTubeThumbnail(string $url): ?string
    {
        $videoId = $this->getYouTubeVideoId($url);

        return $videoId
            ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg"
            : null;
    }

    public function getLessonsByCohortId(int $cohortId): array
    {
        $sql = "
            SELECT 
                l.*,
                EXISTS (
                    SELECT 1 
                    FROM cohort_lesson_quizzes q
                    WHERE q.lesson_id = l.id
                ) AS has_quiz
            FROM program_course_cohort_lessons l
            WHERE l.cohort_id = :cohort_id
            ORDER BY l.display_order ASC
        ";

        $rows = $this->cohortLesson
            ->rawQuery($sql, ['cohort_id' => $cohortId]);

        return array_map(function ($row) {
            $row['has_quiz'] = (bool) $row['has_quiz'];
            $row['zoom_info'] = !empty($row['zoom_info']) ? json_decode($row['zoom_info'], true) : null;
            return $row;
        }, $rows);
    }

    public function getLessonQuiz(int $lessonId): array
    {
        $quizzes = $this->cohortLessonQuiz
            ->select([
                'question_id',
                'title AS question_text',
                'answer AS options',
                'correct',
            ])
            ->where('lesson_id', $lessonId)
            ->get();

        return array_map(fn($q) => [
            'question_id' => $q['question_id'],
            'question_text' => $q['question_text'],
            'options' => json_decode($q['options'], true),
            'correct' => json_decode($q['correct'], true),
        ], $quizzes);
    }

    public function deleteLesson(int $id): bool
    {
        $lesson = $this->cohortLesson
            ->where('id', $id)
            ->first();

        if (!$lesson) {
            return true;
        }

        $hasQuiz = $this->cohortLessonQuiz
            ->where('lesson_id', $id)
            ->exists();

        if ($hasQuiz) {
            $this->cohortLessonQuiz
                ->where('lesson_id', $id)
                ->delete();
        }

        if (!empty($lesson['material_url'])) {
            StorageService::deleteFile($lesson['material_url']);
        }
        if (!empty($lesson['assignment_url'])) {
            StorageService::deleteFile($lesson['assignment_url']);
        }
        if (!empty($lesson['certificate_url'])) {
            StorageService::deleteFile($lesson['certificate_url']);
        }

        return $this->cohortLesson
            ->where('id', $id)
            ->delete();
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

    public function gradeSubmission(array $data): bool
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
