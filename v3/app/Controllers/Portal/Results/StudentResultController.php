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
use V3\App\Services\Portal\Results\StudentResultService;

class StudentResultController extends BaseController
{
    private StudentResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentResultService($this->pdo);
    }

    public function getStudentTermResult(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'student_id', 'term', 'year', 'level_id']
        );

        try {
            return $this->respond([
                'success' => true,
                'response' => $this->service->getStudentTermResult($data)
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getResultTerms(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);

        try {
            $terms = $this->service->getYearlyTermAverages($data);
            return $this->respond(['success' => true, 'result_terms' => $terms]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
