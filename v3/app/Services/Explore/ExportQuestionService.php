<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Enums\QuestionType;
use V3\App\Models\Explore\ExamType;
use V3\App\Models\Portal\ELearning\Quiz;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use V3\App\Models\Explore\Exam;

class ExportQuestionService
{
    private Quiz $quiz;
    private ExamType $examType;
    private Exam $exam;

    public function __construct(private \PDO $pdo)
    {
        $this->quiz = new Quiz($this->pdo);
        $this->examType = new ExamType($this->pdo);
        $this->exam = new Exam($this->pdo);
    }

    public function export()
    {
        ini_set('memory_limit', '2G');
        set_time_limit(0);

        $examTypeRow = $this->getExamType();

        foreach ($examTypeRow as $type) {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            $exams = $this->getExamsByType((int)$type['id']);
            if (empty($exams)) {
                continue;
            }

            // 1. Aggregate all questions by course_name (across all years)
            $allQuestionsByCourse = [];

            foreach ($exams as $exam) {
                if (empty($exam['questions'])) {
                    continue;
                }

                $questionsGrouped = $this->getQuestions($exam['questions']);
                foreach ($questionsGrouped as $questions) {
                    $courseName = $exam['course_id'] ?? 'Unknown';
                    if (!isset($allQuestionsByCourse[$courseName])) {
                        $allQuestionsByCourse[$courseName] = [];
                    }
                    $allQuestionsByCourse[$courseName] = array_merge(
                        $allQuestionsByCourse[$courseName],
                        $questions
                    );
                }
            }

            // 2. Create one sheet per course_name
            foreach ($allQuestionsByCourse as $courseName => $questions) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle(
                    substr($courseName ?: 'Unknown', 0, 31)
                );

                // Determine max number of options
                $maxOptions = 0;
                foreach ($questions as $q) {
                    if ($q['question_type'] === 'multiple_choice') {
                        $count = \count($q['options'] ?? []);
                        if ($count > $maxOptions) {
                            $maxOptions = $count;
                        }
                    }
                }

                // Build dynamic header
                $header = ['id', 'course_id', 'course_name', 'question_type', 'question_text'];
                for ($i = 1; $i <= $maxOptions; $i++) {
                    $header[] = "option_$i";
                }
                $header[] = 'answer';
                $header[] = 'year';

                // Write header row
                $col = 'A';
                foreach ($header as $h) {
                    $sheet->setCellValue("{$col}1", $h);
                    $col++;
                }

                // Write data rows
                $rowNum = 2;
                foreach ($questions as $q) {
                    $col = 'A';
                    $options = [];

                    if ($q['question_type'] === 'multiple_choice') {
                        $options = array_map(fn($opt) => $opt['text'] ?? '', $q['options'] ?? []);
                    }

                    $record = [
                        $q['question_id'],
                        $q['course_id'],
                        $q['course_name'],
                        $q['question_type'],
                        $q['title'],
                    ];

                    for ($i = 0; $i < $maxOptions; $i++) {
                        $record[] = $options[$i] ?? '';
                    }

                    $record[] = $q['answer'] ?? '';
                    $record[] = $q['year'] ?? '';

                    foreach ($record as $value) {
                        $sheet->setCellValue($col . $rowNum, $value);
                        $col++;
                    }
                    $rowNum++;
                }
            }

            // Ensure at least one sheet exists
            if ($spreadsheet->getSheetCount() === 0) {
                $spreadsheet->createSheet()->setTitle('No Data');
            }

            // Save workbook
            $filePath = __DIR__ . "/../../../public/exports/{$type['title']}.xlsx";
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            echo $filePath;
        }

        exit;
    }


    private function getExamType(): array
    {
        $result = $this->examType
            ->select(['id', 'title', 'shortname'])
            ->paginate(7, 4);

        var_dump($result['meta']);

        return $result['data'];
    }

    private function getExamsByType(int $typeId): array
    {
        $results = $this->exam
            ->select(['id', 'exam_type', 'url', 'course_name', 'course_id'])
            ->where('exam_type', '=', $typeId)
            ->orderBy('course_name', 'ASC')
            ->get();

        $examMap = [];
        foreach ($results as $res) {
            $url = str_replace('|', '', $res['url'] ?? '');
            $pairs = array_filter(explode(',', $url));
            $questionIds = array_map(
                fn($pair) => (int)explode(':', $pair)[0] ?? 0,
                $pairs
            );

            $examMap[] = [
                'id' => $res['id'],
                'course_name' => $res['course_name'],
                'course_id' => $res['course_id'],
                'questions' => $questionIds,
            ];
        }

        return $examMap;
    }

    private function getQuestions(array $questionIds = []): array
    {
        $result = $this->quiz
            ->select([
                'question_id',
                'course_id',
                'course_name',
                'content AS title',
                'type as question_type',
                'answer as options',
                'correct AS answer',
                'year',
            ])
            ->in('question_id', $questionIds)
            ->orderBy(['course_name' => 'ASC', 'year' => 'ASC'])
            ->get();
        // var_dump($result['meta']);

        $grouped = [];

        foreach ($result as $row) {
            $courseName = $row['course_id'] ?? "Unknown";
            $typeLabel = QuestionType::tryFrom($row['question_type'] ?? '')?->label() ?? 'unknown';

            if ($typeLabel === 'unknown') {
                continue;
            }
            $row['options'] = $this->json($row['options']);
            $row['question_type'] = $typeLabel;

            $grouped[$courseName][] = $row;
        }

        return $grouped;
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
