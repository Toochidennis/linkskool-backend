<?php

/**
 * This class helps handles student's course result
 *
 * PHP version 8.0+
 *
 * @category Service
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Services\Portal;

use V3\App\Models\Portal\Assessment;
use V3\App\Models\Portal\Student;

class CourseResultService
{
    private Assessment $assessment;
    private Student $student;

    public function __construct(\PDO $pdo)
    {
        $this->assessment = new Assessment($pdo);
        $this->student = new Student($pdo);
    }

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

    public function getCourseResults(array $filters)
    {
        return $this->student
            ->select(columns: [
                "CONCAT(surname, ' ', first_name, ' ', middle) as student_name",
                'students_record.registration_no AS reg_no',
                'result_table.id AS result_id',
                'result_table.result',
                'result_table.new_result',
                'result_table.comment'
            ])
            ->join('result_table', 'students_record.id = result_table.reg_no')
            ->where('result_table.class', '=', $filters['class_id'])
            ->where('result_table.year', '=', $filters['year'])
            ->where('result_table.term', '=', $filters['term'])
            ->where('result_table.course', '=', $filters['course_id'])
            ->orderBy('surname')
            ->get();
    }

    public function transformResults(array $studentResults, array $assessments)
    {
        return array_map(function ($row) use ($assessments) {
            $structured = [];

            if (!empty($row['result']) && empty($row['new_result'])) {
                $splitScores = explode(':', $row['result']);
                foreach ($assessments as $index => $assess) {
                    $structured[] = [
                        'assessment_name' => $assess['assessment_name'],
                        'score' => $splitScores[$index] ?? '',
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

            return [
                'student_name' => $row['student_name'],
                'reg_no' => $row['reg_no'],
                'result_id' => $row['result_id'],
                'assessments' => $structured
            ];
        }, $studentResults);
    }
}
