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
            return $this->respond([
                'success' => true,
                'response' => $this->service->fetchTermCourseResult($data)
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
            return $this->respond([
                'success' => true,
                'response' => $this->service->fetchStudentsTermResultWithComments($data)
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
            return $this->respond([
                'success' => true,
                'response' => $this->service->fetchTermCompositeResult($data)
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getAllLevelsPerformance(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['term', 'year']);

        try {
            return $this->respond([
                'success' => true,
                'response' => $this->service->fetchLevelsPerformance($data)
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
