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
use V3\App\Services\Portal\Results\ClassCourseResultService;

class ClassCourseResultController extends BaseController
{
    private ClassCourseResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassCourseResultService($this->pdo);
    }

    public function getCourseResultsForClass(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'course_id', 'term', 'year', 'level_id']
        );

        try {
            $assessments = $this->service->fetchLevelAssessments($data['level_id']);
            $rawResults = $this->service->fetchRawCourseResults($data);
            $grades = $this->service->fetchGradingScale();
            $transformed = $this->service->formatCourseAssessments($rawResults, $assessments);

            return $this->respond([
                'success' => true,
                'response' => ['course_results' => $transformed, 'grades' => $grades]
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getStudentsResultForClass(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'term', 'year', 'level_id']
        );

        try {
            $assessments = $this->service->fetchLevelAssessments($data['level_id']);
            $rawResults = $this->service->fetchAllStudentResults($data);
            $comments = $this->service->fetchResultComments($data);
            $transformed = $this->service
                ->formatCompositeScores($rawResults, $assessments, $comments);

            return $this->respond([
                'success' => true,
                'response' => $transformed
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getClassCompositeResult(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'term', 'year', 'level_id']
        );

        try {
            $results = $this->service->fetchSubjectWiseScores($data);
            $registeredCourses = $this->service->getFormattedRegisteredCourses($data);
            $transformed = $this->service->transformCompositeResult($results, $registeredCourses);
            return $this->respond(['success' => true, 'response' => $transformed]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
