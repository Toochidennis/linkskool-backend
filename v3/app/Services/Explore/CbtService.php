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
                'picref'
            ])
            ->get();

        $meta = [];
        foreach ($rows as $row) {
            $meta[$row['id']] = [
                'title'       => $row['title'],
                'desc'        => $row['description'],
                'short'       => $row['shortname'],
                'pic'         => $this->formatRef($row['picref'] ?? '')
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
            $courseId   = $row['course_id'];
            $examId     = $row['id'];

            // Create exam type group
            if (!isset($formatted[$examTypeId])) {
                $meta = $examMeta[$examTypeId] ?? [];
                $formatted[$examTypeId] = [
                    'exam_type_id'   => $examTypeId,
                    'exam_image'     => $meta['pic'] ?? '',
                    'exam_title'     => $meta['title'] ?? '',
                    'exam_desc'      => $meta['desc'] ?? '',
                    'exam_shortname' => $meta['short'] ?? '',
                    'courses'        => []
                ];
            }

            // Add course
            if (!isset($formatted[$examTypeId]['courses'][$courseId])) {
                $formatted[$examTypeId]['courses'][$courseId] = [
                    'course_id'   => $courseId,
                    'course_name' => $row['course_name'],
                    'years'       => []
                ];
            }

            // Add year
            $formatted[$examTypeId]['courses'][$courseId]['years'][] = [
                'exam_id' => $examId,
                'year'    => $row['year']
            ];
        }

        // Convert associative arrays to sequential arrays for JSON
        return array_values(array_map(function ($exam) {
            $exam['courses'] = array_values($exam['courses']);
            return $exam;
        }, $formatted));
    }

    /**
     * Format absolute URL for image reference
     */
    private function formatRef(string $url): string
    {
        return "http://" . $_SERVER["SERVER_NAME"] . "/$url";
    }

    public function getExamWithQuestions(int $examId): array
    {
        // 1. Fetch the exam record
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
            ->where('id', '=', $examId)
            ->first();

        if (!$exam) {
            return [
                'success' => false,
                'message' => 'Exam not found'
            ];
        }

        // 2. Extract question IDs from url (like old code)
        $url = str_replace('|', '', $exam['url'] ?? '');
        $exam['url'] = ''; // old code wiped it
        $questionPairs = array_filter(explode(',', $url));
        $questionIds = [];
        $orderMap = [];

        foreach ($questionPairs as $index => $pair) {
            if (empty($pair)) {
                continue;
            }

            $parts = explode(':', $pair);
            $id = (int)($parts[0] ?? 0);
            if ($id) {
                $questionIds[] = ['id' => $id];
                $orderMap[$id] = $index;
            }
        }

        if (empty($questionIds)) {
            return [
                'success' => true,
                'exam' => $exam,
                'questions' => []
            ];
        }

        $questions = $this->getQuestions($questionIds);

        // 4. Preserve original order
        usort($questions, function ($a, $b) use ($orderMap) {
            return ($orderMap[$a['question_id']] ?? 0) <=> ($orderMap[$b['question_id']] ?? 0);
        });

        // 5. Group questions by parent (like old code)
        $grouped = [];
        foreach ($questions as $q) {
            $parent = $q['question_grade'] ?? 0;
            $grouped[$parent][] = $q;
        }

        // 6. Final structure
        return [
            'success' => true,
            'exam' => [
                'id'          => $exam['id'],
                'title'       => $exam['title'],
                'description' => $exam['description'],
                'course_id'   => $exam['course_id'],
                'course_name' => $exam['course_name'],
                'body'        => $this->json($exam['body'] ?? null),
            ],
            'questions' => $grouped
        ];
    }

    private function getQuestions(array $questionIds): array
    {
        $ids = array_values(array_map(
            fn($item) => (int)$item['id'],
            $questionIds
        ));

        if (empty($ids)) {
            return [];
        }

        $questions = $this->quiz
            ->select([
                'question_id',
                'parent AS question_grade',
                'content AS question_files',
                'title AS question_text',
                'type AS question_type',
                'answer AS options',
                'correct',
            ])
            ->in('question_id', $ids)
            ->get();

        if (!$questions) {
            return [];
        }

        $filtered = [];

        foreach ($questions as $question) {
            // Decode and normalize
            $type = $question['question_type'] ?? '';
            $text = trim($question['question_text'] ?? '');

            // Skip if missing essential fields
            if (empty($type) || empty($text)) {
                continue;
            }

            $typeLabel = QuestionType::tryFrom($type)?->label() ?? '';
            if (empty($typeLabel)) {
                continue;
            }

            $question['question_type'] = $typeLabel;
            $question['question_files'] = $this->json($question['question_files']);
            $question['options'] = $this->json($question['options']);
            $question['correct'] = $this->json($question['correct']);

            // Validation logic per type
            if ($typeLabel === 'multiple_choice') {
                if (empty($question['options']) || empty($question['correct'])) {
                    continue;
                }
            } elseif ($typeLabel === 'short_answer') {
                if (empty($question['correct'])) {
                    continue;
                }
            }

            $filtered[] = $question;
        }

        return $filtered;
    }

    private function json(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : [];
    }
}
