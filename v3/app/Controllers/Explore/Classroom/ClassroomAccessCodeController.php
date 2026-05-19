<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomAccessCodeService;

#[Group('/public/classroom')]
class ClassroomAccessCodeController extends ExploreBaseController
{
    private ClassroomAccessCodeService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomAccessCodeService($this->pdo);
    }

    #[Route('/access-codes/seed', 'GET')]
    public function seedCodes(): void
    {
        $seeded = $this->service->seedCodes();
        $this->respond(
            [
                'success' => true,
                'message' => "Seeded $seeded access codes."
            ]
        );
    }

    #[Route('/access-codes/verify', 'POST', ['api'])]
    public function verifyAccessCode(): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'code' => 'required|string'
            ]
        );

        $result = $this->service->validateCode($data['code']);
        if ($result['status'] === 'error') {
            $this->respondError($result['message'], HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Access code validated successfully.',
                'data' => [
                    'valid' => true,
                ]
            ]
        );
    }
}
