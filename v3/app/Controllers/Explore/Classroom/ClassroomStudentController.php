<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomStudentService;

#[Group('/public/classroom/students')]
class ClassroomStudentController extends ExploreBaseController
{
    private ClassroomStudentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomStudentService($this->pdo);
    }

    #[Route('', 'POST', ['api'])]
    public function createStudents(array $vars): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'institution_id' => 'required|integer',
                'level_id' => 'required|integer',
                'students' => 'required|array|min:1',
                'students.*.first_name' => 'required|string',
                'students.*.last_name' => 'required|string',
                'students.*.reg_number' => 'required|string',
                'students.*.middle_name' => 'nullable|string',
                'students.*.phone' => 'nullable|string',
            ],
        );

        $count = $this->service->createStudents($data['students']);

        if (!$count) {
            $this->respondError(
                'Failed to create students.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status'  => true,
                'message' => "$count student(s) created successfully.",
            ],
            HttpStatus::CREATED
        );
    }
}
