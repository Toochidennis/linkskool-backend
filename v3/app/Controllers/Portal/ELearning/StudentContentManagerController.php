<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\StudentContentManagerService;

class StudentContentManagerController extends BaseController
{
    private StudentContentManagerService $studentContent;

    public function __construct()
    {
        parent::__construct();
        $this->studentContent = new StudentContentManagerService($this->pdo);
    }

    public function dashboard(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'term' => 'required|integer',
                'level_id' => 'required|integer',
                'year' => 'required|integer',
                'id' => 'required|integer',
            ]
        );

        try {
            $this->respond([
                'success' => true,
                'data' => $this->studentContent->getStudentDashboardData($data)
            ]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
