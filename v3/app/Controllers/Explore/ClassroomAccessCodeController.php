<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ClassroomAccessCodeService;

#[Group('/public/classroom')]
class ClassroomAccessCodeController extends ExploreBaseController
{
    private ClassroomAccessCodeService $service;

    public function __construct()
    {
        $this->service = new ClassroomAccessCodeService($this->pdo);
    }

    #[Route('access-code/seed', 'POST', ['api'])]
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

    #[Route('access-code/validate', 'POST', ['api'])]
    public function validateAccessCode(): void
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
                'message' => 'Access code validated successfully.'
            ]
        );
    }
}
