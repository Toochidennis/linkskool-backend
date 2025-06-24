<?php

/**
 * Handles logic related to retrieving and formatting student academic results.
 *
 * PHP version 8.2+
 *
 * @category Service
 * @package Linkskool
 * @author ToochiDennis
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Services\Portal\Results;

use V3\App\Models\Portal\Academics\Assessment;
use V3\App\Models\Portal\Academics\Course;
use V3\App\Models\Portal\Results\Result;

class StudentResultService
{
    private Result $result;
    private Course $course;
    private Assessment $assessment;

    public function __construct(\PDO $pdo)
    {
        $this->result = new Result($pdo);
        $this->course = new Course($pdo);
        $this->assessment = new Assessment($pdo);
    }

    /**
     * Returns yearly term-wise average scores for a student.
     */
    public function getYearlyTermAverages(array $filters): array
    {
        $terms = $this->result
            ->select([
                'reg_no',
                'class AS class_id',
                'year',
                'term',
                'AVG(result_table.total) AS average_score'
            ])
            ->where('reg_no', $filters['id'])
            ->where('total', 'IS NOT', null)
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

            $termName = match ((int)$termValue) {
                1 => 'First Term',
                2 => 'Second Term',
                3 => 'Third Term',
                default => 'Unknown Term'
            };

            $structured[$year]['year'] = (int)$year;
            $structured[$year]['terms'][] = [
                'term_name' => $termName,
                'term_value' => (int)$termValue,
                'average_score' => round((float)$row['average_score'], 2)
            ];
        }

        return array_values($structured);
    }

    /**
     * Get assessment structure for a level or fallback to global (no-level).
     */
    private function getAssessmentsByLevel(int $levelId): array
    {
        $assessments = $this->assessment
            ->select(['assesment_name AS assessment_name', 'max_score'])
            ->where('type', '<>', 1)
            ->where('level', '=', $levelId)
            ->orderBy('id')
            ->get();

        return $assessments ?: $this->assessment
            ->select(['assesment_name AS assessment_name', 'max_score'])
            ->where('type', '<>', 1)
            ->where('level', '=', '')
            ->orderBy('id')
            ->get();
    }

    /**
     * Get raw student result data for a class/year/term.
     */
    private function getRawStudentResults(array $filters): array
    {
        return $this->course
            ->select([
                'course_table.id AS course_id',
                'course_table.course_name',
                'result_table.result',
                'result_table.new_result',
                'result_table.total',
                'result_table.grade',
                'result_table.remark'
            ])
            ->join('result_table', 'course_table.id = result_table.course')
            ->where('result_table.class', '=', $filters['class_id'])
            ->where('result_table.year', '=', $filters['year'])
            ->where('result_table.term', '=', $filters['term'])
            ->where('result_table.reg_no', '=', $filters['student_id'])
            ->orderBy('course_name')
            ->get();
    }

    /**
     * Formats raw result and assessment into structured breakdown.
     */
    private function formatCourseResults(array $results, array $assessments): array
    {
        $formatted = [];

        foreach ($results as $row) {
            $structured = [];

            if (!empty($row['result']) && $row['new_result'] === null) {
                $splitScores = explode(':', $row['result']);
                foreach ($assessments as $index => $assess) {
                    $structured[] = [
                        'assessment_name' => $assess['assessment_name'],
                        'score' => isset($splitScores[$index]) ? (int)$splitScores[$index] : ''
                    ];
                }
            } elseif (!empty($row['new_result'])) {
                $structured = json_decode($row['new_result'], true);
            } else {
                continue;
            }

            $formatted[] = [
                'course_name' => $row['course_name'],
                'assessments' => $structured,
                'total' => $row['total'],
                'grade' => $row['grade'],
                'remark' => $row['remark']
            ];
        }

        return $formatted;
    }

    /**
     * Calculates average and position of a student in class for a term.
     */
    private function getStudentClassAverageAndPosition(array $filters): array
    {
        $results = $this->result
            ->select(['reg_no', 'total'])
            ->where('class', '=', $filters['class_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->get();

        $studentScores = [];
        foreach ($results as $row) {
            $regNo = $row['reg_no'];

            if (!isset($studentScores[$regNo])) {
                $studentScores[$regNo] = ['reg_no' => $regNo, 'sum' => 0, 'count' => 0];
            }

            if ($row['total'] !== null) {
                $studentScores[$regNo]['sum'] += (int) $row['total'];
                $studentScores[$regNo]['count']++;
            }
        }

        foreach ($studentScores as &$student) {
            $student['average'] = $student['count'] > 0
                ? round($student['sum'] / $student['count'], 2)
                : 0;
        }

        $ranked = $this->rankStudents(array_values($studentScores));

        foreach ($ranked as $student) {
            if ($student['reg_no'] == $filters['student_id']) {
                return [
                    'position' => $student['position'],
                    'average' => $student['average'],
                    'total_students' => count($studentScores)
                ];
            }
        }

        return [
            'position' => 0,
            'average' => 0,
            'total_students' => count($studentScores)
        ];
    }

    /**
     * Sorts students by average score and assigns class position.
     */
    private function rankStudents(array $students): array
    {
        usort($students, fn($a, $b) => $b['average'] <=> $a['average']);

        $position = 1;
        $sameRankCount = 0;
        $lastAverage = null;

        foreach ($students as &$student) {
            if ($student['average'] === $lastAverage) {
                $student['position'] = $position;
                $sameRankCount++;
            } else {
                $position += $sameRankCount;
                $student['position'] = $position;
                $sameRankCount = 1;
            }

            $lastAverage = $student['average'];
            unset($student['sum'], $student['count']);
        }

        return $students;
    }

    /**
     * Compiles all student academic details for a result term.
     */
    public function getStudentTermResult(array $filters): array
    {
        $assessments = $this->getAssessmentsByLevel($filters['level_id']);
        $results = $this->getRawStudentResults($filters);
        $formatted = $this->formatCourseResults($results, $assessments);
        $positionData = $this->getStudentClassAverageAndPosition($filters);

        return ['subjects' => $formatted] + $positionData;
    }
}
