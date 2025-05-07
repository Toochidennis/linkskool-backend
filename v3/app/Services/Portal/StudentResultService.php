<?php

/**
 * This class helps handles student's course result
 *
 * PHP version 8.2+
 *
 * @category Service
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Services\Portal;

use V3\App\Models\Portal\Assessment;
use V3\App\Models\Portal\Course;
use V3\App\Models\Portal\Grade;
use V3\App\Models\Portal\Result;
use V3\App\Models\Portal\Student;

class StudentResultService
{
    private Result $result;
    private Course $course;
    private Assessment $assessment;
    private Student $student;
    private Grade $grade;

    public function __construct(\PDO $pdo)
    {
        $this->result = new Result($pdo);
        $this->course = new Course($pdo);
        $this->student = new Student($pdo);
        $this->grade = new Grade($pdo);
        $this->assessment = new Assessment($pdo);
    }

    /**
     * Retrieves average scores grouped by academic year and term for a specific student.
     *
     * @param array $filters An associative array with:
     *                       - 'id' => student registration number.
     *
     * @return array Returns a structured array grouped by year with each term’s average score.
     *               Format: [
     *                   '2024' => [
     *                       'terms' => [
     *                           ['term' => 1, 'average_score' => '63.00'],
     *                           ['term' => 2, 'average_score' => '71.50'],
     *                       ]
     *                   ],
     *                   ...
     *               ]
     */
    public function getResultTerms(array $filters)
    {
        $terms = $this->result
            ->select(
                columns: [
                    'reg_no',
                    'class class_id',
                    'year',
                    'term',
                    "avg(result_table.total) AS average_score"
                ]
            )
            ->where('reg_no', $filters['id'])
            ->where('total', 'IS  NOT', null)
            ->groupBy(['class', 'year', 'term'])
            ->orderBy(['year' => 'DESC', 'term' => 'ASC'])
            ->get();

        $structured = [];

        foreach ($terms as $row) {
            $year = $row['year'];
            $termValue = $row['term'];

            if ($year === '0000') {
                continue;
            }

            $termName = match ($termValue) {
                1 => 'First Term',
                2 => 'Second Term',
                3 => 'Third Term',
                default => 'Unknown Term'
            };

            if (!isset($structured[$year])) {
                $structured[$year] = [
                    'year' => (int) $year,
                    'terms' => []
                ];
            }

            $structured[$year]['terms'][] = [
                'term_name' => $termName,
                'term_value' => (int) $termValue,
                'average_score' => (float) number_format((float)$row['average_score'], 2)
            ];
        }

        return array_values($structured);
    }

    /**
     * Retrieves a list of assessments for a given level.
     * If no level-specific assessments exist, it falls back to default (no level).
     *
     * @param string $levelId The level ID to filter assessments by.
     *
     * @return array An array of assessments with their names and maximum scores.
     */
    public function getAssessments(int $levelId)
    {
        $assessments = $this->assessment
            ->select(columns: ['assesment_name AS assessment_name', 'max_score'])
            ->where('type', '<>', 1)
            ->where('level', '=', $levelId)
            ->orderBy('id')
            ->get();

        if (!$assessments) {
            $assessments = $this->assessment
                ->select(columns: ['assesment_name AS assessment_name', 'max_score'])
                ->where('type', '<>', 1)
                ->where('level', '=', "")
                ->orderBy('id')
                ->get();
        }

        return $assessments;
    }

    public function getStudentResult(array $filters)
    {
        return $this->course
            ->select(columns: [
                'course_table.course_name',
                'result_table.result',
                'result_table.new_result',
                'result_table.total',
                'result_table.grade',
                'result_table.comment'
            ])
            ->join('result_table', 'course_table.id = result_table.course')
            ->where('result_table.class', '=', $filters['class_id'])
            ->where('result_table.year', '=', $filters['year'])
            ->where('result_table.term', '=', $filters['term'])
            ->where('result_table.reg_no', '=', $filters['student_id'])
            ->orderBy('course_name')
            ->get();
    }

    public function transformResult($result, $assessments)
    {
        $transformed = [];

        foreach ($result as $row) {
            $structured = [];

            if (!empty($row['result']) && empty($row['new_result'])) {
                $splitScores = explode(':', $row['result']);
                foreach ($assessments as $index => $assess) {
                    $structured[] = [
                        'assessment_name' => $assess['assessment_name'],
                        'score' => isset($splitScores[$index]) ? (int) $splitScores[$index] : '',
                        'max_score' => $assess['max_score']
                    ];
                }
            } elseif (!empty($row['new_result'])) {
                $structured = json_decode($row['new_result'], true);
            } else {
                foreach ($assessments as $assess) {
                    $structured[] = [
                        'assessment_name' => $assess['assessment_name'],
                        'score' => '',
                        'max_score' => $assess['max_score']
                    ];
                }
            }

            $remark = $this->getRemark($row['total']);

            $transformed[] = [
                'course_name' => $row['course_name'],
                'assessments' => $structured,
                'total' => $row['total'],
                'grade' => $row['grade'],
                'remark' => $remark,
            ];
        }

        return $transformed;
    }
    /**
     * Retrieves all grade definitions from the database ordered by descending start scores.
     *
     * @return array An array of grades with keys: grade_symbol, start, and remark.
     */
    private function getGrades()
    {
        return $this->grade
            ->select(columns: ['grade_symbol', 'start', 'remark'])
            ->orderBy(columns: 'start', direction: 'DESC')
            ->get();
    }

    /**
     * Determines grade symbol, remark, and pass status based on score.
     *
     * @param float|int $score The total score.
     * @return string Returns an array with keys:
     *                    - 'grade' (string)
     *                    - 'remark' (string)
     *                    - 'status' (int: 1=pass, 0=fail),
     */
    private function getRemark($score)
    {
        if (!is_numeric($score)) {
            return '';
        }

        $grades = $this->getGrades();

        foreach ($grades as $grade) {
            if ($score >= $grade['start']) {
                return $grade['remark'];
            }
        }

        return '';
    }

    public function calculateStudentAverage(array $result): float
    {
        $totalScore = 0;
        $count = 0;

        foreach ($result as $row) {
            if (isset($row['total']) && is_numeric($row['total'])) {
                $totalScore += $row['total'];
                $count++;
            }
        }

        return $count > 0 ? round($totalScore / $count, 2) : 0;
    }

    public function getClassAverageAndPosition(array $filters, string $targetRegNo): array
    {
        $results = $this->result
            ->select(['reg_no', 'total'])
            ->where('class', '=', $filters['class_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->get();

        // Group totals by student
        $studentScores = [];
        foreach ($results as $row) {
            if (!isset($studentScores[$row['reg_no']])) {
                $studentScores[$row['reg_no']] = ['sum' => 0, 'count' => 0];
            }

            if (is_numeric($row['total'])) {
                $studentScores[$row['reg_no']]['sum'] += $row['total'];
                $studentScores[$row['reg_no']]['count']++;
            }
        }

        // Calculate averages
        $averages = [];
        foreach ($studentScores as $regNo => $data) {
            $averages[$regNo] = round($data['sum'] / max(1, $data['count']), 2);
        }

        // Sort by average descending
        arsort($averages);

        // Find position
        $position = 0;
        $rank = 1;
        foreach ($averages as $regNo => $avg) {
            if ($regNo === $targetRegNo) {
                $position = $rank;
                break;
            }
            $rank++;
        }

        return [
            'position' => $position,
            'average' => $averages[$targetRegNo] ?? 0,
            'total_students' => count($averages)
        ];
    }
}
