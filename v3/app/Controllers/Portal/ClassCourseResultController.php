<?php

/**
 * This class helps handles student's course result
 *
 * PHP version 8.0+
 *
 * @category Controller
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Assessment;
use V3\App\Models\Portal\Student;
use V3\App\Traits\ValidationTrait;

class ClassCourseResultController extends BaseController
{
    use ValidationTrait;

    private Assessment $assessment;
    private Student $student;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    /**
     * Initializes the controller by extracting POST/GET
     * data and connecting to the database.
     */
    public function initialize()
    {
        $this->assessment = new Assessment($this->pdo);
        $this->student = new Student($this->pdo);
    }

    public function getCourseResultsForClass(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'course_id', 'term', 'year', 'level_id']
        );

        try {
            $assessments = $this->assessment
                ->select(columns: ['assesment_name AS assessment_name', 'max_score'])
                ->where('type', '<>', 1)
                ->where('level', '=', $data['level_id'])
                ->orderBy('id')
                ->get();

            if (!$assessments) {
                $assessments = $this->assessment
                    ->select(columns: ['assesment_name AS assessment_name', 'max_score'])
                    ->where('type', '<>', 1)
                    ->where('level', '=', "'' OR NULL")
                    ->orderBy('id')
                    ->get();
            }

            $studentResult = $this->student
                ->select(columns: [
                    "CONCAT(surname, ' ', first_name, ' ', middle) as student_name",
                    'students_record.registration_no AS reg_no',
                    'result_table.result',
                    'result_table.new_result',
                    'result_table.comment',
                    'result_table.id AS result_id'
                ])
                ->join('result_table', 'students_record.id = result_table.reg_no')
                ->where('result_table.class', '=', $data['class_id'])
                ->where('result_table.year', '=', $data['year'])
                ->where('result_table.term', '=', $data['term'])
                ->where('result_table.course', '=', $data['course_id'])
                ->orderBy('surname')
                ->get();

            return $this->respond(['success' => true, 'result' => $assessments]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
