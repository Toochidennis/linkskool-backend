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
use V3\App\Services\Portal\StudentResultService;

class StudentResultController extends BaseController
{
    use ValidationTrait;

    private StudentResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentResultService($this->pdo);
    }

    public function getStudentResult(array $vars)
    {
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
