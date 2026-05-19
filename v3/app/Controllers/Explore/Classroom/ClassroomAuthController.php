<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomAuthService;

#[Group('/public/classroom/auth')]
class ClassroomAuthController extends ExploreBaseController
{
    private ClassroomAuthService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomAuthService($this->pdo);
    }

    #[Route('/student', 'POST', ['api'])]
    public function authenticateStudent(): void
    {
        $validated = $this->validate(
            $this->getRequestData(),
            [
                'reg_number' => 'required|string',
                'join_code'  => 'required|string',
            ],
        );

        $result = $this->service->authenticateStudent(
            $validated['reg_number'],
            $validated['join_code']
        );

        if ($result['status'] === 'error') {
            $this->respondError($result['message'], HttpStatus::UNAUTHORIZED);
        }

        $this->respond(
            [
                'status' => true,
                'message' => $result['message'],
                'data' => [
                    'student' => $result['student'],
                    'institution' => $result['institution'],
                ],
            ],
            HttpStatus::OK
        );
    }
}
