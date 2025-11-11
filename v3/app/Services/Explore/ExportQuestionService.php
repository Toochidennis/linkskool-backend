<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Enums\QuestionType;
use V3\App\Models\Portal\ELearning\Quiz;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportQuestionService
{
    private Quiz $quiz;

    public function __construct(private \PDO $pdo)
    {
        $this->quiz = new Quiz($this->pdo);
    }

    public function export()
    {
        $grouped = $this->getQuestions(); // grouped by course_name
        $spreadsheet = new Spreadsheet();

        // Remove the default blank sheet
        $spreadsheet->removeSheetByIndex(0);

        foreach ($grouped as $courseName => $questions) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(substr($courseName ?: 'Unknown', 0, 31)); // Excel sheet title limit

            // // Determine max number of options in this subject
            // $maxOptions = 0;
            // foreach ($questions as $q) {
            //     if ($q['question_type'] === 'multiple_choice') {
            //         $count = \count($q['options'] ?? []);
            //         var_dump($count);
            //         if ($count > $maxOptions) {
            //             $maxOptions = $count;
            //         }
            //     }
            // }

            // Build dynamic header
            $header = [
                'id',
                'course_id',
                'course_name',
                'question_type',
                'question_text',
                'options',
            ];

            // // Add variable option headers
            // var_dump($maxOptions);
            // for ($i = 1; $i <= $maxOptions; $i++) {
            //     $header[] = "option_$i";
            //     var_dump($header);
            // }

            $header[] = 'answer';
            $header[] = 'year';

            // Write header row
            $col = 'A';
            foreach ($header as $h) {
                $sheet->setCellValue($col . '1', strtoupper($h));
                $col++;
            }

            // Write data rows
            $rowNum = 2;
            foreach ($questions as $q) {
                $col = 'A';
                $options = [];

                // if ($q['question_type'] === 'multiple_choice') {
                //     $options = $q['options'] ?? [];
                //     $options = array_map(fn($opt) => $opt['text'] ?? '', $options);
                // }

                // Build record line
                $record = [
                    $q['question_id'],
                    $q['course_id'],
                    $q['course_name'],
                    $q['question_type'],
                    $q['title'],
                    json_encode($q['options'] ?? []),
                ];

                // // Fill options dynamically
                // for ($i = 0; $i < $maxOptions; $i++) {
                //     $record[] = $options[$i] ?? '';
                // }

                $record[] = $q['answer'] ?? '';
                $record[] = $q['year'] ?? '';

                // Write each column
                foreach ($record as $value) {
                    $sheet->setCellValue($col . $rowNum, $value);
                    $col++;
                }

                $rowNum++;
            }

            // Optional: auto-size columns
            // foreach (range('A', $col) as $columnID) {
            //     $sheet->getColumnDimension($columnID)->setAutoSize(true);
            // }
        }

        // Save workbook
        $filePath = __DIR__ . "/../../../public/exports/questions.xlsx";
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        echo $filePath;
        exit;
    }

    private function getQuestions()
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
            ->limit(10)
            ->get();
        $grouped = [];

        foreach ($result as $row) {
            $courseName = $row['course_name'] ?? 'Unknown';
            $typeLabel = QuestionType::tryFrom($row['question_type'] ?? '')?->label() ?? 'unknown';
            $row['options'] = $this->json($row['options']);
            $row['question_type'] = $typeLabel;
            var_dump($typeLabel);

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
