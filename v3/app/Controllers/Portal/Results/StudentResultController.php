<?php

/**
 * This class helps handles student's course result
 *
 * PHP version 8.2+
 *
 * @category Controller
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Controllers\Portal\Results;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\StudentResultService;

class StudentResultController extends BaseController
{
    private StudentResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentResultService($this->pdo);
    }

    public function getStudentResult(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'student_id', 'term', 'year', 'level_id']
        );

        try {
            $studentResult = $this->service->getStudentResult($data);
            $assessments = $this->service->getAssessments($data['level_id']);
            $transformedResult = $this->service->transformResult($studentResult, $assessments);
            $studentAverage = $this->service->calculateStudentAverage($studentResult);
            $positionData = $this->service->getClassAverageAndPosition($data, $data['student_id']);

            return $this->respond([
                'success' => true,
                'student_result' =>  [
                    'courses' => $transformedResult,
                    'average' => $studentAverage,
                    'position' => $positionData['position'],
                    'total_students' => $positionData['total_students']
                ]
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getResultTerms(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);

        try {
            $terms = $this->service->getResultTerms($data);
            return $this->respond(['success' => true, 'result_terms' => $terms]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
