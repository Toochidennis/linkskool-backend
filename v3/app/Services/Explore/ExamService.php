<?php

namespace V3\App\Services\Explore;

use PDO;
use V3\App\Common\Enums\QuestionType;
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
                    $this->logAction(
                        'Exam Creation Failed',
                        $settings['user_id'],
                        $settings['username'],
                        0,
                        'exam_upload_failed',
                        "Failed to create exam for {$settings['course_name']} for year $year"
                    );
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
            'upload_date' => date('Y-m-d H:i:s'),
            'year' => $year,
        ];

        return $this->exam->insert($payload);
    }

    public function getExams(int $page = 1, $limit = 25): array
    {
        return $this->exam
            ->select(['id', 'course_name',, 'year', 'upload_date'])
            ->orderBy('course_name', 'DESC')
            ->paginate($limit, $page);
    }

    public function getQuestions(int $examId): array
    {
        $exam = $this->exam
            ->select(['url'])
            ->where('id', '=', $examId)
            ->first();

        if (!$exam) {
            return [];
        }

        $questionIds = json_decode($exam['url'], true, 512, JSON_THROW_ON_ERROR);

        if (empty($questionIds) || !\is_array($questionIds)) {
            return [];
        }

        $questions = $this->quiz
            ->select([
                'question_id',
                'title AS question_text',
                'content AS question_files',
                'topic',
                'topic_id',
                'passage',
                'passage_id',
                'instruction',
                'instruction_id',
                'explanation',
                'explanation_id',
                'type as question_type',
                'answer as options',
                'correct',
            ])
            ->in('question_id', $questionIds)
            ->get();

        if (empty($questions)) {
            return [];
        }

        $questions = array_map(function ($question) {
            $question['question_type'] = QuestionType::tryFrom($question['question_type'])?->label() ?? 'Unknown';
            $question['question_files'] = $this->decode($question['question_files']);
            $question['options'] = $this->decode($question['options']);
            $question['correct'] = $this->decode($question['correct']);
            return $question;
        }, $questions);

        return $questions;
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

    private function decode(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return \is_array($decoded) ? $decoded : [];
    }
}
