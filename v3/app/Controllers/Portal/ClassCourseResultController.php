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
use V3\App\Traits\ValidationTrait;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\CourseResultService;

class ClassCourseResultController extends BaseController
{
    use ValidationTrait;

    private CourseResultService $courseResult;

    public function __construct()
    {
        parent::__construct();
    }

    public function getCourseResultsForClass(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'course_id', 'term', 'year', 'level_id']
        );

        try {
            $service = new CourseResultService($this->pdo);
            $assessments = $service->getAssessments($data['level_id']);
            $rawResults = $service->getCourseResults($data);
            $grades = $service->getGrades();
            $transformed = $service->transformResults($rawResults, $assessments);

            return $this->respond([
                'success' => true,
                'response' => ['course_results' => $transformed, 'grades' => $grades]
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
