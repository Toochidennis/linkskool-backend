<?php

namespace V3\App\Services\Portal\ELearning;

use PDO;
use Throwable;
use V3\App\Common\Enums\ContentType;
use V3\App\Common\Enums\QuestionType;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\ELearning\Content;
use V3\App\Models\Portal\ELearning\Quiz;

class QuizService
{
    private Content $content;
    private Quiz $quiz;
    private PDO $pdo;
    private FileHandler $handler;

    private array $typeMap = [
        'multiple_choice' => QuestionType::MULTIPLE_CHOICE->value,
        'short_answer' => QuestionType::SHORT_ANSWER->value,
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->content = new Content($pdo);
        $this->quiz = new Quiz($pdo);
        $this->handler = new FileHandler(ContentType::QUIZ->value);
    }

    public function addQuiz(array $assessment): bool|int
    {
        $questions = $assessment['questions'];
        $settings = $assessment['setting'];
        $questionIds = [];

        try {
            $this->pdo->beginTransaction();

            foreach ($questions as $index => $question) {
                $questionFiles = $question['question_files'] ?? [];
                $options = $question['options'] ?? [];

                if (!empty($questionFiles)) {
                    $questionFiles = $this->handler->handleFiles(files: $questionFiles);
                }

                if (!empty($options)) {
                    foreach ($options as &$option) {
                        if (isset($option['option_files']) && !empty($option['option_files'])) {
                            $option['option_files'] =  $this->handler->handleFiles(files: $option['option_files']);
                        }
                    }
                }

                $payload = [
                    'title' => $question['question_text'],
                    'content' => $this->json($questionFiles),
                    'type' => $this->typeMap[$question['question_type']],
                    'parent' => $question['question_grade'],
                    'correct' => $this->json($question['correct']),
                    'course_id' => $settings['course_id'],
                    'course_name' => $settings['course_name'],
                    'term' => $settings['term'],
                    'answer' => $this->json($options)
                ];

                if ($newId = $this->quiz->insert($payload)) {
                    $questionIds[] = ['id' => $newId, 'rank' => $index];
                } else {
                    throw new \RuntimeException("Failed to insert question at $index");
                }
            }

            $contentId = $this->addSettings($settings, $questionIds);

            $this->pdo->commit();

            return $contentId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function addSettings(array $settings, array $questionIds): bool|int
    {
        $payload = [
            'title' => $settings['title'],
            'description' => $settings['description'],
            'type' => ContentType::QUIZ->value,
            'parent' => $settings['topic_id'] ?? 0,
            'category' => $settings['topic'],
            'outline' => $settings['syllabus_id'],
            'course_id' => $settings['course_id'],
            'course_name' => $settings['course_name'],
            'level' => $settings['level_id'],
            'path_label' => $this->json($settings['classes']),
            'author_id' => $settings['creator_id'],
            'author_name' => $settings['creator_name'],
            'term' => $settings['term'],
            'url' => $this->json($questionIds),
            'start_date' => $settings['start_date'],
            'end_date' => $settings['end_date'],
            'body' => $settings['duration'],
            'upload_date' => date('Y-m-d H:i:s'),
        ];

        return $this->content->insert($payload);
    }

    public function updateQuiz(array $assessments)
    {
        $questions = $assessments['questions'];
        $settings = $assessments['setting'];
        $questionIds = [];

        try {
            $this->pdo->beginTransaction();

            foreach ($questions as $index => $question) {
                $questionFiles = $question['question_files'] ?? [];
                $options = $question['options'] ?? [];

                if (!empty($questionFiles)) {
                    $questionFiles = $this->handler->handleFiles(files: $questionFiles, isUpdate: true);
                }

                if (!empty($options)) {
                    foreach ($options as &$option) {
                        if (isset($option['option_files']) && !empty($option['option_files'] ?? [])) {
                            $option['option_files'] = $this->handler->handleFiles(
                                files: $option['option_files'],
                                isUpdate: true
                            );
                        }
                    }
                }

                $payload = [
                    'title' => $question['question_text'],
                    'content' => $this->json($questionFiles),
                    'type' => $question['question_type'] === 'multiple_choice' ? 'qo' : 'qs',
                    'parent' => $question['question_grade'],
                    'correct' => $this->json($question['correct']),
                    'answer' => $this->json($options)
                ];

                if (!isset($question['question_id']) || $question['question_id'] == 0) {
                    $newId = $this->quiz->insert($payload);
                    $question['question_id'] = $newId;
                    $isSuccess = $newId > 0;
                } else {
                    $updated = $this->quiz
                        ->where('question_id', '=', $question['question_id'])
                        ->update($payload);

                    $isSuccess = $updated !== false;
                }

                if ($isSuccess) {
                    $questionIds[] = [
                        'id' => $question['question_id'],
                        'rank' => $index
                    ];
                } else {
                    throw new \RuntimeException("Failed to save question at $index");
                }
            }

            $contentId = $this->updateSettings($settings, $questionIds);

            $this->pdo->commit();

            return $contentId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateSettings(array $settings, array $questionIds): bool
    {
        $payload = [
            'title' => $settings['title'],
            'description' => $settings['description'],
            'parent' => $settings['topic_id'] ?? 0,
            'category' => $settings['topic'],
            'path_label' => $this->json($settings['classes']),
            'url' => $this->json($questionIds),
            'start_date' => $settings['start_date'],
            'end_date' => $settings['end_date'],
            'body' => $settings['duration'],
        ];

        return $this->content
            ->where('id', '=', $settings['id'])
            ->update($payload);
    }

    public function deleteQuiz(int $id, int $contentId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Fetch the quiz question
            $quiz = $this->quiz
                ->where('question_id', '=', $id)
                ->first();

            if (!$quiz) {
                throw new \Exception("Quiz question not found: $id");
            }

            // Delete question files
            $questionFiles = json_decode($quiz['content'], true);
            $this->handler->deleteFiles($questionFiles);

            // Delete option files
            $options = json_decode($quiz['answer'], true);
            foreach ($options as $option) {
                if (!empty($option['option_files'] ?? [])) {
                    $this->handler->deleteFiles($option['option_files']);
                }
            }

            // Delete the quiz row
            $this->quiz
                ->where('question_id', '=', $id)
                ->delete();

            // Find content(s) that reference this quiz ID
            $content = $this->content
                ->select(['url'])
                ->where('id', '=', $contentId)
                ->first();


            $url = json_decode($content['url'], true);

            // Remove the deleted ID
            $filtered = array_values(array_filter($url, fn($q) => $q['id'] != $id));

            // Only update if something changed
            if (\count($filtered) !== \count($url)) {
                $this->content
                    ->where('id', '=', $contentId)
                    ->update(['url' => $this->json($filtered)]);
            }

            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    private function json(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
