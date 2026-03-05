<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Lesson\LessonPublished;
use V3\App\Models\Explore\CohortLessonQuiz;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class ProgramCourseCohortLessonService
{
    private ProgramCourseCohortLesson $cohortLesson;
    private CohortLessonQuiz $cohortLessonQuiz;
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->cohortLesson = new ProgramCourseCohortLesson($pdo);
        $this->cohortLessonQuiz = new CohortLessonQuiz($pdo);
    }

    public function addLessonToCohort(array $data): bool
    {
        $lessonId = null;

        try {
            $this->pdo->beginTransaction();

            $slug = $this->generateUuidV4();

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

            $fileUrls = $this->processNewFiles($data);
            $payload = [...$payload, ...$fileUrls];

            $lessonId = $this->cohortLesson->insert($payload);
            if (!$lessonId) {
                throw new \Exception("Failed to insert lesson record");
            }

            $this->pdo->commit();

            if (($payload['status'] ?? 'draft') === 'published') {
                EventDispatcher::dispatch(new LessonPublished((int) $lessonId));
            }

            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateLesson(array $data)
    {
        $previousStatus = null;

        try {
            $this->pdo->beginTransaction();

            $existingLesson = $this->cohortLesson
                ->where('id', $data['lesson_id'])
                ->first();
            $previousStatus = $existingLesson['status'] ?? null;

            $payload = [
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

            $fileUrls = $this->processNewFiles($data, isUpdate: true);

            $payload = [...$payload, ...$fileUrls];

            $lessonId = $this->cohortLesson
                ->where('id', $data['lesson_id'])
                ->update(array_filter($payload, fn($value) => $value !== null));

            if (!$lessonId) {
                throw new \Exception("Failed to insert lesson record");
            }

            $this->cleanupOldFiles($data, $fileUrls);

            $this->pdo->commit();

            if ($previousStatus !== 'published' && ($payload['status'] ?? 'draft') === 'published') {
                EventDispatcher::dispatch(new LessonPublished((int) $data['lesson_id']));
            }

            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $lessonId, string $status)
    {
        $lesson = $this->cohortLesson
            ->where('id', $lessonId)
            ->first();

        $updated = $this->cohortLesson
            ->where('id', $lessonId)
            ->update(['status' => $status]);

        if ($updated && ($lesson['status'] ?? null) !== 'published' && $status === 'published') {
            EventDispatcher::dispatch(new LessonPublished($lessonId));
        }

        return $updated;
    }

    private function processNewFiles(array $data, bool $isUpdate = false): array
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
            $urls['material_url'] = StorageService::saveFile(
                $material,
                $this->buildLessonGroupPath($data, 'materials')
            );
        }

        if (isset($_FILES['assignment'])) {
            $urls['assignment_url'] = StorageService::saveFile(
                $_FILES['assignment'],
                $this->buildLessonGroupPath($data, 'assignments')
            );
        }
        if (isset($_FILES['certificate'])) {
            $urls['certificate_url'] = StorageService::saveFile(
                $_FILES['certificate'],
                $this->buildLessonGroupPath($data, 'certificates')
            );
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

    private function buildLessonGroupPath(array $data, string $assetType): string
    {
        $programId = (int)($data['program_id'] ?? 0);
        $courseId = (int)($data['course_id'] ?? 0);
        $cohortId = (int)($data['cohort_id'] ?? 0);
        $lessonSlug = $this->toSlug((string)($data['title'] ?? 'lesson'));

        return "explore/programs/{$programId}/courses/{$courseId}/cohorts/{$cohortId}/lessons/{$lessonSlug}/{$assetType}";
    }

    private function toSlug(string $text): string
    {
        return strtolower(trim((string)preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }

    private function generateUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
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
}
