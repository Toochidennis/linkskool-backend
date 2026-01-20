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
                'cohort_lesson_id' => $lessonId,
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
                'id' => $data['id'],
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
                'lesson_date' => $data['lesson_date'],
            ];

            if ($data['is_final_lesson'] && !isset($_FILES['certificate'])) {
                throw new \Exception('Certificate file is required for final lessons.');
            }

            if ($this->isYouTubeUrl($data['video_url'])) {
                $payload['thumbnail'] = $this->getYouTubeThumbnail($data['video_url']);
            }

            $fileUrls = $this->processNewFiles();

            if (isset($data['old_assignment_url']) && isset($fileUrls['assignment_url'])) {
                StorageService::deleteFile($data['old_assignment_url']);
            }
            if (isset($data['old_material_url']) && isset($fileUrls['material_url'])) {
                StorageService::deleteFile($data['old_material_url']);
            }
            if (isset($data['old_certificate_url']) && isset($fileUrls['certificate_url'])) {
                StorageService::deleteFile($data['old_certificate_url']);
            }

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

    private function processNewFiles()
    {
        $urls = [
            'assignment_url' => null,
            'material_url' => null,
            'certificate_url' => null,
        ];

        if (!isset($_FILES['material'])) {
            throw new \Exception("Material file is required.");
        }

        $urls['material_url'] = StorageService::saveFile($_FILES['material']);

        if (isset($_FILES['assignment'])) {
            $urls['assignment_url'] = StorageService::saveFile($_FILES['assignment']);
        }
        if (isset($_FILES['certificate'])) {
            $urls['certificate_url'] = StorageService::saveFile($_FILES['certificate']);
        }

        return $urls;
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
            'cohort_lesson_id' => $params['cohort_lesson_id'],
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

        $correctAnswer = trim($question['answer'] ?? '');
        $correctIndex = $this->findOptionIndex($options, $correctAnswer);

        if ($correctIndex === -1) {
            throw new \Exception(
                "Correct answer '{$correctAnswer}' not found in options for question: {$question['question']}"
            );
        }

        $payload['answer'] = json_encode($options);
        $payload['correct'] = json_encode([
            'text' => $correctAnswer,
            'order' => $correctIndex
        ]);

        $questionId = $this->cohortLessonQuiz->insert($payload);
        if (!$questionId) {
            throw new \Exception("Failed to save quiz question: " . $question['question']);
        }
    }

    private function findOptionIndex(array $options, string $answerText): int
    {
        foreach ($options as $index => $option) {
            if (strtolower($option['text']) === strtolower($answerText)) {
                return $index;
            }
        }
        return -1;
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
                    WHERE q.cohort_lesson_id = l.id
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

    public function getLessonQuiz(int $cohortLessonId): array
    {
        $quizzes = $this->cohortLessonQuiz
            ->select([
                'question_id',
                'title AS question_text',
                'answer AS options',
                'correct',
            ])
            ->where('cohort_lesson_id', $cohortLessonId)
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
            ->where('cohort_lesson_id', $id)
            ->exists();

        if ($hasQuiz) {
            $this->cohortLessonQuiz
                ->where('cohort_lesson_id', $id)
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
