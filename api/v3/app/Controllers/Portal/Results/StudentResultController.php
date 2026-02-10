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

use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Results\StudentResultService;

#[Group('/portal')]
class StudentResultController extends BaseController
{
    private StudentResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentResultService($this->pdo);
    }

    #[Route(
        '/students/{student_id:\d+}/result/{term:\d+}',
        'GET',
        ['auth']
    )]
    public function getStudentTermResult(array $vars)
    {
        $data = $this->validate(
            $vars,
            [
                'class_id' => 'required|integer',
                'student_id' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer',
                'level_id' => 'required|integer',
            ]
        );

        return $this->respond([
            'success' => true,
            'response' => $this->service->getStudentTermResult($data)
        ]);
    }

    #[Route(
        '/students/{student_id:\d+}/result/annual',
        'GET',
        ['auth']
    )]
    public function getStudentAnnualResult(array $vars)
    {
        $data = $this->validate(
            $vars,
            [
                'class_id' => 'required|integer',
                'student_id' => 'required|integer',
                'year' => 'required|integer',
                'level_id' => 'required|integer',
            ]
        );

        return $this->respond([
            'success' => true,
            'response' => $this->service->fetchStudentAnnualResult($data)
        ]);
    }

    #[Route(
        '/students/{id:\d+}/result-terms',
        'GET',
        ['auth']
    )]
    public function getResultTerms(array $vars)
    {
        $data = $this->validate($vars, ['id' => 'required|integer']);

        return $this->respond([
            'success' => true,
            'result_terms' =>  $this->service->getYearlyTermAverages($data)
        ]);
    }
}
