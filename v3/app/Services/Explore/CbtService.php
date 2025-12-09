<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Enums\QuestionType;
use V3\App\Models\Explore\Exam;
use V3\App\Models\Explore\ExamType;
use V3\App\Models\Portal\ELearning\Quiz;

class CbtService
{
    private Exam $exam;
    private ExamType $examType;
    private Quiz $quiz;

    public function __construct(\PDO $pdo)
    {
        $this->exam = new Exam($pdo);
        $this->examType = new ExamType($pdo);
        $this->quiz = new Quiz($pdo);
    }

    /**
     * Fetch all exam data grouped by exam_type → course → year
     */
    public function getFormattedExamHierarchy(): array
    {
        $examRows = $this->getExamRows();
        $examMeta = $this->getExamMeta();

        return $this->formatExamHierarchy($examRows, $examMeta);
    }

    /**
     * Fetch base exam rows (raw data)
     */
    private function getExamRows(): array
    {
        return $this->exam
            ->select([
                'id',
                'course_name',
                'course_id',
                'exam_type',
                'year'
            ])
            ->where('course_name', '<>', '')
            ->groupBy(['exam_type', 'course_id', 'year'])
            ->orderBy([
                'exam_type' => 'ASC',
                'course_id' => 'ASC',
                'year' => 'ASC'
            ])
            ->get();
    }

    /**
     * Fetch metadata for exam types (title, desc, etc.)
     */
    private function getExamMeta(): array
    {
        $rows = $this->examType
            ->select([
                'id',
                'title',
                'description',
                'shortname',
                'picref',
                'display_order'
            ])
            ->where('is_active', '=', 1)
            ->get();

        $meta = [];
        foreach ($rows as $row) {
            $meta[$row['id']] = [
                'title' => $row['title'],
                'desc' => $row['description'],
                'short' => $row['shortname'],
                'pic' => $this->formatRef($row['picref'] ?? ''),
                'display_order' => $row['display_order']
            ];
        }

        return $meta;
    }

    /**
     * Structure the data in a clear hierarchical format:
     * exam_type → courses → years
     */
    private function formatExamHierarchy(array $rows, array $examMeta): array
    {
        $formatted = [];

        foreach ($rows as $row) {
            $examTypeId = $row['exam_type'];
            $courseId = $row['course_id'];
            $examId = $row['id'];

            if (!isset($examMeta[$examTypeId])) {
                continue;
            }
            // Create exam type group
            if (!isset($formatted[$examTypeId])) {
                $meta = $examMeta[$examTypeId];
                $formatted[$examTypeId] = [
                    'exam_type_id' => $examTypeId,
                    'exam_image' => $meta['pic'],
                    'exam_title' => $meta['title'],
                    'exam_desc' => $meta['desc'],
                    'exam_shortname' => $meta['short'],
                    'exam_display_order' => $meta['display_order'],
                    'courses' => []
                ];
            }

            // Add course
            if (!isset($formatted[$examTypeId]['courses'][$courseId])) {
                $formatted[$examTypeId]['courses'][$courseId] = [
                    'course_id' => $courseId,
                    'course_name' => $row['course_name'],
                    'years' => []
                ];
            }

            // Add year
            $formatted[$examTypeId]['courses'][$courseId]['years'][] = [
                'exam_id' => $examId,
                'year' => $row['year']
            ];
        }

        // Convert associative arrays to sequential arrays for JSON
        $formatted = array_values(array_map(function ($exam) {
            $exam['courses'] = array_values($exam['courses']);
            return $exam;
        }, $formatted));

        // Sort by display_order in ascending order
        usort($formatted, fn($a, $b) => $a['exam_display_order'] <=> $b['exam_display_order']);

        return $formatted;
    }

    /**
     * Format absolute URL for image reference
     */
    private function formatRef(string $url): string
    {
        return "http://" . $_SERVER["SERVER_NAME"] . "/$url";
    }

    /**
     * Fetch exam details along with its questions
     */
    public function getExamWithQuestions(array $filters): array
    {
        // Fetch the exam record
        $exam = $this->exam
            ->select([
                'id',
                'title',
                'description',
                'course_name',
                'course_id',
                'body',
                'url'
            ])
            ->where('id', '=', $filters['exam_id'])
            ->first();

        if (!$exam) {
            return [
                'success' => false,
                'message' => 'Exam not found'
            ];
        }

        // Extract question IDs from url
        $questionIds = json_decode($exam['url'], true, 512, JSON_THROW_ON_ERROR);

        if (empty($questionIds)) {
            return [
                'success' => true,
                'exam' => $exam,
                'questions' => []
            ];
        }

        $questions = $this->getQuestions($questionIds, $filters);

        //Preserve original order
        usort(
            $questions,
            fn($a, $b) => ($orderMap[$a['question_id']] ?? 0) <=> ($orderMap[$b['question_id']] ?? 0)
        );

        // Group questions by parent
        $grouped = [];
        foreach ($questions as $q) {
            $parent = $q['question_grade'] ?? 0;
            $grouped[$parent][] = $q;
        }

        // Final structure
        return [
            'success' => true,
            'exam' => [
                'id' => $exam['id'],
                'title' => $exam['title'],
                'description' => $exam['description'],
                'course_id' => $exam['course_id'],
                'course_name' => $exam['course_name'],
                'body' => $this->json($exam['body'] ?? null),
            ],
            'questions' => $grouped
        ];
    }

    private function getQuestions(array $questionIds, array $filters): array
    {
        shuffle($questionIds);
        $selectedIds = \array_slice($questionIds, 0, $filters['limit'] ?? 50);

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
            ->in('question_id', $selectedIds)
            ->limit($filters['limit'] ?? 50)
            ->offset($filters['offset'] ?? 0)
            ->get();

        if (!$questions) {
            return [];
        }
        return array_map(function ($question) {
            $question['question_type'] = QuestionType::tryFrom($question['question_type'])?->label() ?? 'Unknown';
            $question['question_files'] = $this->json($question['question_files']);
            $question['options'] = $this->json($question['options']);
            $question['correct'] = $this->json($question['correct']);
            return $question;
        }, $questions);
    }

    private function json(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return \is_array($decoded) ? $decoded : [];
    }
}
