<?php

namespace V3\App\Services\Explore;

use PDO;
use V3\App\Common\Enums\QuestionType;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Common\Utilities\PathResolver;
use V3\App\Common\Utilities\QuestionImportFormatter;
use V3\App\Common\Utilities\QuestionImportParser;
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
    private string $contentPath;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->exam = new Exam($pdo);
        $this->auditLog = new AuditLog($pdo);
        $this->quiz = new Quiz($pdo);
        $this->handler = new FileHandler();
        $paths = PathResolver::getContentPaths();
        $this->contentPath = $paths['absolute'];
    }

    /**
     * Summary of createQuestions
     * @param array $data
     * @throws \Exception
     * @return array{errors: mixed, status: bool|array{exam_ids: array, status: bool}|array{exam_ids: int[], status: bool}}
     */
    public function createQuestions(array $data): array
    {
        $parsedData = QuestionImportParser::parse($data['file']);
        $formattedData = QuestionImportFormatter::format($parsedData);

        if (!empty($formattedData['errors'])) {
            return [
                'status' => false,
                'errors' => $formattedData['errors']
            ];
        }

        $questionsData = $formattedData['data'];
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
                        "Failed to create exam for {$settings['course_name']} for year $year",
                        'failed'
                    );
                    throw new \Exception("Failed to create exam for year $year");
                }
            }

            $this->pdo->commit();

            return [
                'status' => true,
                'exam_ids' => $examIds
            ];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Summary of createExam
     * @param array $settings
     * @param array $questionIds
     * @param int $year
     * @return bool|int
     */
    private function createExam(array $settings, array $questionIds, int $year): int
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

    /**
     * Summary of getExams
     * @param array $filters
     * @return array
     */
    public function getExams(array $filters): array
    {
        return $this->exam
            ->select(['id', 'description', 'course_name', 'year', 'upload_date'])
            ->orderBy('course_name')
            ->paginate($filters['page'] ?? 1, $filters['limit'] ?? 25);
    }

    /**
     * Summary of deleteExam
     * @param array $filters
     * @return bool
     */
    public function deleteExam(array $filters): bool
    {
        $exam = $this->exam
            ->select(columns: ['url', 'course_name', 'year'])
            ->where('id', '=', $filters['exam_id'])
            ->first();

        if (!$exam) {
            return false;
        }

        $questionIds = $this->decode($exam['url']);

        if (empty($questionIds)) {
            return false;
        }

        $this->deleteQuizContent($questionIds);

        $deletedExam = $this->exam
            ->where('id', '=', $filters['exam_id'])
            ->delete();

        if ($deletedExam) {
            $this->logAction(
                'Exam Deletion',
                $filters['user_id'],
                $filters['username'],
                $filters['exam_id'],
                'exam_deletion',
                "Deleted exam for {$exam['course_name']} for year {$exam['year']}"
            );
            return true;
        }

        return false;
    }

    /**
     * Summary of deleteQuizContent
     * @param array $questionIds
     * @return void
     */
    private function deleteQuizContent(array $questionIds): void
    {
        foreach ($questionIds as $questionId) {
            $question = $this->quiz->where('question_id', '=', $questionId)->first();

            if (empty($question)) {
                continue;
            }

            $questionFiles = $this->decode($question['content']);
            $this->deleteFiles($questionFiles);

            $options = $this->decode($question['answer']);
            foreach ($options as $option) {
                if (!empty($option['option_files'] ?? [])) {
                    $this->deleteFiles($option['option_files']);
                }
            }

            $this->quiz
                ->where('question_id', '=', $questionId)
                ->delete();
        }
    }

    /**
     * Summary of deleteFiles
     * @param array $files
     * @return void
     */
    private function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            $filePath = $file['file_name'] ?? $file['old_file_name'] ?? null;

            if ($filePath) {
                $absolute = $this->contentPath . basename($filePath);
                if (file_exists($absolute)) {
                    @unlink($absolute); // Suppress warning, continue even if file fails
                }
            }
        }
    }

    /**
     * Summary of getQuestions
     * @param int $examId
     * @return array
     */
    public function getQuestions(int $examId): array
    {
        $exam = $this->exam
            ->select(['url'])
            ->where('id', '=', $examId)
            ->first();

        if (!$exam) {
            return [];
        }

        $questionIds = $this->decode($exam['url']);

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
        ?string $details = null,
        string $status = 'success'
    ): void {

        $payload = [
            'action' => $action,
            'user_id' => $userId,
            'username' => $username,
            'details' => $details,
            'status' => $status,
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
