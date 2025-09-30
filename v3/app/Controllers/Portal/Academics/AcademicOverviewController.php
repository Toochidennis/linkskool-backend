<?php

namespace V3\App\Controllers\Portal\Academics;

use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\AcademicOverviewService;

class AcademicOverviewController extends BaseController
{
    private AcademicOverviewService $academicOverviewService;

    public function __construct()
    {
        parent::__construct();
        $this->academicOverviewService = new AcademicOverviewService($this->pdo);
    }

    public function adminOverview(array $vars)
    {
        $filters = $this->validate(
            $vars,
            ['term' => 'required|integer'],
        );

        try {
            $overview = $this->academicOverviewService->getAdminOverview($filters['term']);
            $this->respond(
                [
                    'success' => true,
                    'data'    => $overview,
                ]
            );
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function teacherOverview(array $vars)
    {
        $filters = $this->validate(
            $vars,
            [
                'term' => 'required|integer',
                'year' => 'required|integer',
                'teacher_id' => 'required|integer',
            ],
        );

        try {
            $overview = $this->academicOverviewService->getStaffOverview($filters);
            $this->respond(
                [
                    'success' => true,
                    'data'    => $overview,
                ]
            );
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function studentOverview(array $vars)
    {
        $filters = $this->validate(
            $vars,
            [
                'term' => 'required|integer',
                'level_id' => 'required|integer',
                'class_id' => 'required|integer',
            ],
        );

        try {
            $overview = $this->academicOverviewService->getStudentOverview($filters);
            $this->respond(
                [
                    'success' => true,
                    'data'    => $overview,
                ]
            );
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
