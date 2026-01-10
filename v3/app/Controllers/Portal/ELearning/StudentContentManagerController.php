<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\ELearning\StudentContentManagerService;

#[Group('/portal')]
class StudentContentManagerController extends BaseController
{
    private StudentContentManagerService $studentContent;

    public function __construct()
    {
        parent::__construct();
        $this->studentContent = new StudentContentManagerService($this->pdo);
    }

    #[Route(
        '/students/{id:\d+}/elearning/dashboard',
        'GET',
        ['auth', 'role:student']
    )]
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

        $this->respond([
            'success' => true,
            'data' => $this->studentContent->getStudentDashboardData($data)
        ]);
    }
}
