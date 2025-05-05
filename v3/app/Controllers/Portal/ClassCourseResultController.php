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

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Traits\ValidationTrait;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\CourseResultService;

class ClassCourseResultController extends BaseController
{
    use ValidationTrait;

    private CourseResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CourseResultService($this->pdo);
    }

    public function getCourseResultsForClass(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'course_id', 'term', 'year', 'level_id']
        );

        try {
            $assessments = $this->service->getAssessments($data['level_id']);
            $rawResults = $this->service->getCourseResults($data);
            $grades = $this->service->getGrades();
            $transformed = $this->service->transformResults($rawResults, $assessments);

            return $this->respond([
                'success' => true,
                'response' => ['course_results' => $transformed, 'grades' => $grades]
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getClassCourseRegistrationStatus(array $vars)
    {
        $data = $this->validateData($vars, ['id', 'term', 'year']);
        try {
            $result = $this->service->getRegistrationStatus($data);
            $this->respond(['success' => true, 'registered_students' => $result]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
