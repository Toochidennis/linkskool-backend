<?php

namespace V3\App\Services\Explore;

use PDO;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\Exam;
use V3\App\Models\Explore\AuditLog;
use V3\App\Models\Portal\ELearning\Quiz;

class ExamService
{
    private PDO $pdo;
    private Exam $exam;
    private AuditLog $auditLog;
    private FileHandler $handler;
    private Quiz $quiz;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->exam = new Exam($pdo);
        $this->auditLog = new AuditLog($pdo);
        $this->quiz = new Quiz($pdo);
        $this->handler = new FileHandler();
    }

    public function createQuestions(array $data): array
    {
        $questionsData = $data['data'];
        $settings = $data['settings'];
        $questionsByYear = [];

        try {
            $this->pdo->beginTransaction();

            foreach ($questionsData as $group) {
                $year = $group['year'];
                foreach ($group['questions'] as $question) {
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
                        'topic' => $question['topic'] ?? '',
                        'topic_id' => $question['topic_id'] ?? null,
                        'passage' => $question['passage'] ?? '',
                        'passage_id' => $question['passage_id'] ?? null,
                        'instruction' => $question['instruction'] ?? '',
                        'instruction_id' => $question['instruction_id'] ?? null,
                        'explanation' => $question['explanation'] ?? '',
                        'explanation_id' => $question['explanation_id'] ?? null,
                        'type' => $question['question_type'] === 'multiple_choice' ? 'qo' : 'qs',
                        'answer' => $this->json($options),
                        'correct' => $this->json($question['correct']),
                        'course_id' => $settings['course_id'],
                        'course_name' => $settings['course_name'],
                        'year' => $year,
                    ];

                    $newId = $this->quiz->insert($payload);

                    if (!$newId) {
                        throw new \Exception('Failed to insert question');
                    }

                    $questionsByYear[$year][] = $newId;
                }
            }

            $examIds = [];
            foreach ($questionsByYear as $year => $questionIds) {
                $examIds[$year] = $this->createExam($settings, $questionIds, $year);
                $this->logAction(
                    'File Upload',
                    $settings['user_id'],
                    $settings['username'],
                    $examIds[$year],
                    'exam_upload',
                    "Uploaded questions for {$settings['course_name']} for year $year"
                );

                if (!$examIds[$year] || $examIds[$year] <= 0) {
                    throw new \Exception("Failed to create exam for year $year");
                }
            }

            $this->pdo->commit();

            return $examIds;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function createExam(array $settings, array $questionIds, $year): int
    {
        $payload = [
            'exam_type' => $settings['exam_type_id'],
            'course_id' => $settings['course_id'],
            'course_name' => $settings['course_name'],
            'description' => $settings['description'] ?? '',
            'url' => $this->json($questionIds),
            'year' => $year,
        ];

        return $this->exam->insert($payload);
    }

    private function logAction(
        string $action,
        int $userId,
        string $username,
        int $actionId = 0,
        ?string $actionType = null,
        ?string $details = null
    ): void {

        $payload = [
            'action' => $action,
            'user_id' => $userId,
            'username' => $username,
            'details' => $details,
            'status' => 'Completed',
            'action_id' => $actionId,
            'action_type' => $actionType,
        ];

        $this->auditLog->insert($payload);
    }

    private function json(array $data): string
    {
        return json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
