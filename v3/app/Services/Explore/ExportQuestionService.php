<?php

namespace V3\App\Services\Explore;

use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use V3\App\Common\Enums\QuestionType;
use V3\App\Models\Explore\Exam;
use V3\App\Models\Explore\ExamType;
use V3\App\Models\Portal\ELearning\Quiz;

class ExportQuestionService
{
    private Quiz $quiz;
    private ExamType $examType;
    private Exam $exam;

    private const CHUNK_SIZE = 500; // Process questions in chunks
    private const MAX_SHEET_NAME_LENGTH = 31;

    public function __construct(private \PDO $pdo)
    {
        $this->quiz = new Quiz($this->pdo);
        $this->examType = new ExamType($this->pdo);
        $this->exam = new Exam($this->pdo);
    }

    public function export(): string
    {
        // Set memory and time limits
        ini_set('memory_limit', '512M'); // Reduced from 2G
        set_time_limit(300); // 5 minutes timeout

        try {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            $exams = $this->getExamsByType(34);
            if (empty($exams)) {
                throw new Exception('No exam found');
            }

            // Process questions in batches to avoid memory issues
            $allQuestionsByCourse = $this->aggregateQuestionsByCourse($exams);

            if (empty($allQuestionsByCourse)) {
                throw new Exception('No questions found for export');
            }

            // Create sheets for each course
            $this->createCourseSheets($spreadsheet, $allQuestionsByCourse);

            // Ensure at least one sheet exists
            if ($spreadsheet->getSheetCount() === 0) {
                $spreadsheet->createSheet()->setTitle('No Data');
            }

            // Save workbook
            $filePath = $this->saveSpreadsheet($spreadsheet);

            // Clean up
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $allQuestionsByCourse);
            gc_collect_cycles();

            return $filePath;
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            throw $e;
        }
    }

    private function aggregateQuestionsByCourse(array $exams): array
    {
        $allQuestionsByCourse = [];

        foreach ($exams as $exam) {
            if (empty($exam['questions'])) {
                continue;
            }

            // Process in chunks to avoid memory issues
            $questionChunks = array_chunk($exam['questions'], self::CHUNK_SIZE);

            foreach ($questionChunks as $chunk) {
                $questionsGrouped = $this->getQuestions($chunk);

                foreach ($questionsGrouped as $courseName => $questions) {
                    $courseKey = $exam['course_id'] ?? $courseName;

                    if (!isset($allQuestionsByCourse[$courseKey])) {
                        $allQuestionsByCourse[$courseKey] = [
                            'name' => $exam['course_name'] ?? $courseName,
                            'questions' => []
                        ];
                    }

                    // Use array_merge instead of spread operator for memory efficiency
                    $allQuestionsByCourse[$courseKey]['questions'] = array_merge(
                        $allQuestionsByCourse[$courseKey]['questions'],
                        $questions
                    );
                }

                // Clear memory after each chunk
                unset($questionsGrouped);
            }
        }

        return $allQuestionsByCourse;
    }

    private function createCourseSheets(Spreadsheet $spreadsheet, array $allQuestionsByCourse): void
    {
        $usedSheetNames = [];

        foreach ($allQuestionsByCourse as $courseId => $courseData) {
            $questions = $courseData['questions'];
            $courseName = $courseData['name'] ?? $courseId;

            if (empty($questions)) {
                continue;
            }

            try {
                $sheet = $spreadsheet->createSheet();
                $sheetTitle = $this->sanitizeSheetName($courseName);

                // Handle duplicate sheet names by appending a number
                $originalTitle = $sheetTitle;
                $counter = 1;
                while (in_array($sheetTitle, $usedSheetNames, true)) {
                    $suffix = '_' . $counter;
                    $maxLength = self::MAX_SHEET_NAME_LENGTH - strlen($suffix);
                    $sheetTitle = substr($originalTitle, 0, $maxLength) . $suffix;
                    $counter++;
                }

                $usedSheetNames[] = $sheetTitle;
                $sheet->setTitle($sheetTitle);

                // Determine max number of options
                //  $maxOptions = $this->getMaxOptions($questions);

                // Build and write header
                $header = $this->buildHeader(6);
                $sheet->fromArray($header, null, 'A1');

                // Write data rows efficiently
                $this->writeDataRows($sheet, $questions, 6);

                // Free memory after processing each sheet
                unset($questions);
                gc_collect_cycles();
            } catch (Exception $e) {
                // Log the error but continue with other sheets - don't skip any data
                error_log("Error creating sheet for course '$courseName' (ID: $courseId): " . $e->getMessage());
                continue;
            }
        }
    }

    private function sanitizeSheetName(string $name): string
    {
        if (empty($name)) {
            return 'Unknown';
        }

        // Excel sheet name rules:
        // 1. Max 31 characters
        // 2. Cannot contain: \ / ? * [ ] : and also ! (used for cell references)
        // 3. Cannot start or end with apostrophe
        // 4. Cannot be empty

        // Replace invalid characters with underscore (including !)
        $name = preg_replace('/[\\\\\/?\*\[\]:!]/', '_', $name);

        // Remove leading/trailing apostrophes and spaces
        $name = trim($name, "' \t\n\r\0\x0B");

        // If name is empty after sanitization, use default
        if (empty($name)) {
            return 'Unknown';
        }

        // If name starts with a number, prefix it to avoid cell reference conflicts
        if (preg_match('/^[0-9]/', $name)) {
            $name = 'Course_' . $name;
        }

        // Truncate to max length
        $name = substr($name, 0, self::MAX_SHEET_NAME_LENGTH);

        // Ensure no trailing apostrophe after truncation
        $name = rtrim($name, "'");

        return $name ?: 'Unknown';
    }

    private function getMaxOptions(array $questions): int
    {
        $maxOptions = 0;

        foreach ($questions as $q) {
            if ($q['question_type'] === 'multiple_choice' && !empty($q['options'])) {
                $count = \count($q['options']);
                if ($count > $maxOptions) {
                    $maxOptions = $count;
                }
            }
        }

        return $maxOptions;
    }

    private function buildHeader(int $maxOptions): array
    {
        $header = [
            'id',
            'course_id',
            'course_name',
            'passage',
            'question_image',
            'instruction',
            'explanation',
            'question_type',
            'question_text'
        ];

        for ($i = 1; $i <= $maxOptions; $i++) {
            $header[] = "option_{$i}_text";
            $header[] = "option_{$i}_image";
        }

        $header[] = 'answer';
        $header[] = 'year';

        return $header;
    }

    private function writeDataRows($sheet, array $questions, int $maxOptions): void
    {
        $rowNum = 2;
        $batchSize = 100; // Write in batches
        $dataRows = [];

        foreach ($questions as $q) {
            $options = [];

            if ($q['question_type'] === 'multiple_choice' && !empty($q['options'])) {
                $options = $q['options'];
            }

            // Sanitize cell values to prevent Excel formula interpretation
            $record = [
                $this->sanitizeCellValue($q['question_id'] ?? ''),
                $this->sanitizeCellValue($q['course_id'] ?? ''),
                '',
                '',
                '',
                '',
                $this->sanitizeCellValue($q['course_name'] ?? ''),
                $this->sanitizeCellValue($q['question_type'] ?? ''),
                $this->sanitizeCellValue($q['title'] ?? ''),
            ];

            // Add options (both text and image for each option)
            for ($i = 0; $i < $maxOptions; $i++) {
                $record[] = $this->sanitizeCellValue($options[$i]['text'] ?? '');
                $record[] = $this->sanitizeCellValue($options[$i]['image'] ?? '');
            }

            $record[] = $this->sanitizeCellValue($q['answer'] ?? '');
            $record[] = $this->sanitizeCellValue($q['year'] ?? '');

            $dataRows[] = $record;

            // Write in batches to improve performance
            if (\count($dataRows) >= $batchSize) {
                $sheet->fromArray($dataRows, null, 'A' . $rowNum);
                $rowNum += \count($dataRows);
                $dataRows = [];
            }
        }

        // Write remaining rows
        if (!empty($dataRows)) {
            $sheet->fromArray($dataRows, null, 'A' . $rowNum);
        }
    }

    private function saveSpreadsheet(Spreadsheet $spreadsheet): string
    {
        $filePath = __DIR__ . "/../../../public/exports/SSCE1.xlsx";

        if (!is_dir(dirname($filePath))) {
            if (!mkdir(dirname($filePath), 0777, true) && !is_dir(dirname($filePath))) {
                throw new Exception("Failed to create export directory");
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }


    private function getExamsByType(int $typeId): array
    {
        $results = $this->exam
            ->select(['id', 'exam_type', 'url', 'course_name', 'course_id'])
            ->where('exam_type', '=', $typeId)
            ->orderBy('course_name', 'ASC')
            ->groupBy(['course_id', 'year'])
            ->get();

        $examMap = [];

        foreach ($results as $res) {
            $url = $res['url'] ?? '';
            if (empty($url)) {
                continue;
            }

            $url = str_replace('|', '', $url);
            $pairs = array_filter(explode(',', $url));

            $questionIds = [];
            foreach ($pairs as $pair) {
                $parts = explode(':', $pair);
                if (!empty($parts[0])) {
                    $questionIds[] = (int)$parts[0];
                }
            }

            if (empty($questionIds)) {
                continue;
            }

            $examMap[] = [
                'id' => $res['id'],
                'course_name' => $res['course_name'] ?? 'Unknown',
                'course_id' => $res['course_id'] ?? 'unknown',
                'questions' => $questionIds,
            ];
        }

        return $examMap;
    }

    private function getQuestions(array $questionIds = []): array
    {
        if (empty($questionIds)) {
            return [];
        }

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

        $grouped = [];

        foreach ($result as $row) {
            $courseName = $row['course_id'] ?? 'Unknown';
            $typeLabel = QuestionType::tryFrom($row['question_type'] ?? '')?->label();

            if ($typeLabel === null || $typeLabel === 'unknown') {
                continue;
            }

            $row['options'] = $this->json($row['options']);
            $row['question_type'] = $typeLabel;

            if (!isset($grouped[$courseName])) {
                $grouped[$courseName] = [];
            }

            if ($row['question_type'] === 'multiple_choice') {
                if (preg_match('/^\^[A-Z]/', $row['answer'] ?? '')) {
                    $row['answer'] = substr($row['answer'], 1);
                } elseif (!empty($row['options'])) {
                    $optionTexts = array_column($row['options'], 'text');
                    $index = array_search($row['answer'], $optionTexts, true);
                    // Return 1-based index (or 0-based if you prefer)
                    $row['answer'] = $index !== false ? ($index + 1) : $row['answer'];
                }
            }

            $grouped[$courseName][] = $row;
        }

        return $grouped;
    }

    private function json(?string $data): array
    {
        if (empty($data)) {
            return [];
        }

        try {
            $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            return \is_array($decoded) ? $decoded : [];
        } catch (\JsonException $e) {
            error_log("JSON decode error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sanitize cell values to prevent Excel from interpreting them as formulas or cell references
     * Values containing ! can be interpreted as sheet references (e.g., Sheet1!A1)
     */
    private function sanitizeCellValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Convert to string
        $value = (string)$value;

        // If value contains characters that Excel interprets specially, prefix with apostrophe
        // This forces Excel to treat it as text
        // Characters: = + - @ ! (formula triggers and cell reference operator)
        if (preg_match('/^[=+\-@!]/', $value) || strpos($value, '!') !== false) {
            return "'" . $value;
        }

        return $value;
    }
}
