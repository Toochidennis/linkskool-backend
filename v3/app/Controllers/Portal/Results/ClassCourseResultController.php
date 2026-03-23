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
use V3\App\Services\Portal\Results\ClassCourseResultService;

#[Group('/portal')]
class ClassCourseResultController extends BaseController
{
    private ClassCourseResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassCourseResultService($this->pdo);
    }

    #[Route(
        '/classes/{class_id:\d+}/courses/{course_id}/results',
        'GET',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function getCourseResultsForClass(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'term' => 'required|integer',
                'year' => 'required|integer',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer'
            ]
        );

        $this->respond([
            'success' => true,
            'response' => $this->service->fetchTermCourseResult($data)
        ]);
    }

    #[Route(
        '/classes/{class_id:\d+}/students-result',
        'GET',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function getStudentsResultForClass(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'term' => 'required|integer',
                'year' => 'required|integer',
                'level_id' => 'required|integer'
            ]
        );

        $this->respond([
            'success' => true,
            'response' => $this->service->fetchStudentsTermResultWithComments($data)
        ]);
    }

    #[Route(
        '/classes/{class_id:\d+}/composite-result',
        'GET',
        ['auth', 'role:admin']
    )]
    public function getClassCompositeResult(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'term' => 'required|integer',
                'year' => 'required|integer',
                'level_id' => 'required|integer'
            ]
        );

        $this->respond([
            'success' => true,
            'response' => $this->service->fetchTermCompositeResult($data)
        ]);
    }

    #[Route(
        '/levels/result/performance',
        'GET',
        ['auth', 'role:admin']
    )]
    public function getAllLevelsPerformance(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'term' => 'required|integer',
                'year' => 'required|integer'
            ]
        );

        $this->respond([
            'success' => true,
            'response' => $this->service->fetchLevelsPerformance($data)
        ]);
    }
}
