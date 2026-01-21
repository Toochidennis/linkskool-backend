<?php

namespace V3\App\Services\Explore;

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
                'video_url' => $data['video_url'],
                'display_order' => $data['display_order'],
                'write_up_content' => $data['write_up_content'] ?? null,
                'assignment_instructions' => $data['assignment_instructions'] ?? null,
                'assignment_due_date' => $data['assignment_due_date'] ?? null,
                'is_final_lesson' => $data['is_final_lesson'] ?? false,
                'author_name' => $data['author_name'],
                'author_id' => $data['author_id'],
                'lesson_date' => $data['lesson_date'],
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

            $this->saveQuiz([
                'lesson_id' => $lessonId,
                'course_name' => $data['course_name'] ?? null,
                'cohort_id' => $data['cohort_id'],
                'course_id' => $data['course_id'],
                'program_id' => $data['program_id'],
            ]);

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
                'video_url' => $data['video_url'],
                'display_order' => $data['display_order'],
                'write_up_content' => $data['write_up_content'] ?? null,
                'assignment_instructions' => $data['assignment_instructions'] ?? null,
                'assignment_due_date' => $data['assignment_due_date'] ?? null,
                'is_final_lesson' => $data['is_final_lesson'] ?? false,
                'lesson_date' => $data['lesson_date'],
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

            $this->deleteAndInsertQuiz($data['lesson_id'], $data);

            $this->cleanupOldFiles($data, $fileUrls);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function deleteAndInsertQuiz($lessonId, array $data)
    {
        if (isset($_FILES['quiz'])) {
            $this->cohortLessonQuiz
                ->where('lesson_id', $lessonId)
                ->delete();

            $this->saveQuiz([
                'lesson_id' => $lessonId,
                'course_name' => $data['course_name'] ?? null,
                'cohort_id' => $data['cohort_id'],
                'course_id' => $data['course_id'],
                'program_id' => $data['program_id'],
            ]);
        }
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

    private function saveQuiz(array $params): void
    {
        if (!isset($_FILES['quiz'])) {
            throw new \Exception("Quiz file is required.");
        }

        $questions = json_decode(file_get_contents($_FILES['quiz']['tmp_name']), true);
        if (empty($questions)) {
            throw new \Exception("No questions found in quiz file.");
        }

        foreach ($questions as $question) {
            $this->insertQuizQuestion($question, $params);
        }
    }

    private function insertQuizQuestion(array $question, array $params): void
    {
        $payload = [
            'lesson_id' => $params['lesson_id'],
            'course_name' => $params['course_name'] ?? null,
            'cohort_id' => $params['cohort_id'],
            'course_id' => $params['course_id'],
            'program_id' => $params['program_id'],
            'title' => $question['question_text'],
            'type' => 'qo',
        ];

        $options = [];
        for ($i = 1; $i <= 4; $i++) {
            $options[] = [
                'text' => trim($question["option_{$i}_text"] ?? ''),
                'option_files' => [],
            ];
        }

        if (!isset($question['answer']) || !is_numeric($question['answer'])) {
            throw new \Exception('Answer must be a numeric 1-based index ' . $question['question_text']);
        }

        $oneBasedIndex = (int) $question['answer'];
        $zeroBasedIndex = $oneBasedIndex - 1;

        if ($zeroBasedIndex < 0 || $zeroBasedIndex >= \count($options)) {
            throw new \Exception(
                "Invalid correct option index: {$oneBasedIndex}"
            );
        }

        $correctText = $options[$zeroBasedIndex]['text'];

        $payload['answer'] = json_encode($options);
        $payload['correct'] = json_encode([
            'text' => $correctText,
            'order' => $zeroBasedIndex,
        ]);

        $questionId = $this->cohortLessonQuiz->insert($payload);
        if (!$questionId) {
            throw new \Exception("Failed to save quiz question: " . $question['question_text']);
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
