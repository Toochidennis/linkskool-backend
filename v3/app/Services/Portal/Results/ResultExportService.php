<?php

namespace V3\App\Services\Portal\Results;

use V3\App\Models\Portal\Academics\Assessment;
use V3\App\Models\Portal\Academics\Student;
use League\Csv\Writer;

class ResultExportService
{
    private Student $student;
    private Assessment $assessment;

    public function __construct(\PDO $pdo)
    {
        $this->student = new Student($pdo);
        $this->assessment = new Assessment($pdo);
    }

    public function sendResults()
    {
        $pivot = $this->formatAllStudentResultsPivoted(2025, 3);

        $assessmentCols = $pivot['assessment_columns'];
        $byClass = $pivot['by_class'];

        foreach ($byClass as $className => $rows) {
            // Sanitize filename
            $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $className);
            $filePath = __DIR__ . "/../../../../public/exports/{$safe}.csv";

            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            $csv = Writer::createFromPath($filePath, 'w+');

            // Header: fixed cols + dynamic assessment cols + trailing fixed cols
            $header = array_merge(
                ['school_name', 'student_id', 'student_name', 'subject'],
                $assessmentCols,
                ['total_score', 'term', 'session']
            );
            $csv->insertOne($header);

            // Rows
            foreach ($rows as $row) {
                $record = [
                    $row['school_name'],
                    $row['student_id'],
                    $row['student_name'],
                    $row['subject'],
                ];

                foreach ($assessmentCols as $col) {
                    $record[] = $row[$col] ?? '';
                }

                $record[] = $row['total_score'];
                $record[] = $row['term'];
                $record[] = $row['session'];

                $csv->insertOne($record);
            }

            echo "✅ CSV written: {$filePath}\n";
        }
    }

    private function fetchAllStudentResults(array $filters)
    {
        return $this->student
            ->select(columns: [
                'students_record.id',
                "CONCAT(surname, ' ', first_name, ' ', middle) as student_name",
                'students_record.registration_no AS reg_no',
                'course_table.course_name AS subject',
                'result_table.result',
                'result_table.new_result',
                'result_table.total AS total_score',
                'result_table.grade',
                'result_table.term',
                'result_table.year AS session',
                'class_table.class_name'
            ])
            ->join('result_table', 'students_record.id = result_table.reg_no')
            ->join('course_table', 'course_table.id = result_table.course')
            ->join('class_table', 'class_table.id = result_table.class')
            ->where('result_table.year', '=', $filters['year'])
            ->where('result_table.term', '=', $filters['term'])
            ->orderBy('class_table.class_name')
            ->orderBy('surname')
            ->get();
    }

    public function fetchLevelAssessments(int $levelId)
    {
        $assessments = $this->assessment
            ->select(columns: ['assesment_name AS assessment_name', 'max_score'])
            ->where('type', '<>', 1)
            ->where('level', '=', $levelId)
            ->orderBy('id')
            ->get();

        if (empty($assessments)) {
            $assessments = $this->assessment
                ->select(columns: ['assesment_name AS assessment_name', 'max_score'])
                ->where('type', '<>', 1)
                ->where('level', '=', "")
                ->orderBy('id')
                ->get();
        }

        return $assessments;
    }

    private function parseAssessmentsForRow(array $row, array $defaultAssessments): array
    {
        // Returns ['1st CA' => 10, '2nd CA' => 9, 'Exam' => 70] etc.
        $scoresByName = [];

        if (!empty($row['new_result'])) {
            $decoded = json_decode($row['new_result'], true);

            // Case A: [{ "assessment_name": "...", "score": N }, ...]
            if (is_array($decoded) && isset($decoded[0]['assessment_name'])) {
                foreach ($decoded as $item) {
                    $name  = $item['assessment_name'] ?? null;
                    $score = $item['score'] ?? null;
                    if ($name !== null) {
                        $scoresByName[$name] = is_numeric($score) ? (float)$score : ($score === null ? '' : $score);
                    }
                }
            } elseif (\is_array($decoded)) {
                // Case B: {"1st CA": 10, "2nd CA": 8, "Exam": 70}
                foreach ($decoded as $name => $score) {
                    $scoresByName[$name] = is_numeric($score) ? (float)$score : ($score === null ? '' : $score);
                }
            }
            // else: bad JSON, fall through to empty
        } elseif (!empty($row['result'])) {
            // Legacy colon-separated scores aligned by default assessment order
            $split = explode(':', $row['result']);
            foreach ($defaultAssessments as $idx => $ass) {
                $name = $ass['assessment_name'];
                $scoresByName[$name] = array_key_exists($idx, $split) && $split[$idx] !== ''
                    ? (float)$split[$idx]
                    : '';
            }
        }

        return $scoresByName;
    }

    private function formatAllStudentResultsPivoted(int $year, int $term): array
    {
        $studentResults = $this->fetchAllStudentResults(['year' => $year, 'term' => $term]);
        $defaultAssessments = $this->fetchLevelAssessments(0);

        $allAssessmentNames = [];
        $tempRows = [];

        foreach ($studentResults as $row) {
            $scoresByName = $this->parseAssessmentsForRow($row, $defaultAssessments);

            foreach (array_keys($scoresByName) as $name) {
                $allAssessmentNames[$name] = true;
            }

            $tempRows[] = [
                'class_name'   => $row['class_name'],
                'school_name'  => 'Daughters of Divine Love',
                'student_id'   => $row['reg_no'],
                'student_name' => $row['student_name'],
                'subject'      => $row['subject'],
                'total_score'  => $row['total_score'],
                'term'         => (int)$row['term'],
                'session'      => $row['session'],
                'scores'       => $scoresByName,
            ];
        }

        $defaultOrder = array_map(fn($a) => $a['assessment_name'], $defaultAssessments);
        $extras = array_values(array_diff(array_keys($allAssessmentNames), $defaultOrder));
        sort($extras, SORT_NATURAL | SORT_FLAG_CASE);
        $assessmentColumns = array_values(array_unique(array_merge($defaultOrder, $extras)));

        // Build final rows grouped by class, each row includes dynamic assessment columns
        $groupedByClass = [];
        foreach ($tempRows as $r) {
            $output = [
                'school_name'  => $r['school_name'],
                'student_id'   => $r['student_id'],
                'student_name' => $r['student_name'],
                'subject'      => $r['subject'],
            ];

            // Fill assessment columns
            foreach ($assessmentColumns as $col) {
                $output[$col] = $r['scores'][$col] ?? '';
            }

            $output['total_score'] = $r['total_score'];
            $output['term']        = $r['term'];
            $output['session']     = $r['session'];

            $groupedByClass[$r['class_name']][] = $output;
        }

        return [
            'assessment_columns' => $assessmentColumns,
            'by_class' => $groupedByClass,
        ];
    }
}
