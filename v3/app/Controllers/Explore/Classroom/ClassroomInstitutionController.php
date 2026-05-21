<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomInstitutionService;

#[Group('/public/classroom/institutions')]
class ClassroomInstitutionController extends ExploreBaseController
{
    private ClassroomInstitutionService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomInstitutionService($this->pdo);
    }

    #[Route('', 'POST', ['api'])]
    public function createInstitution(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|string',
                'name' => 'required|string',
                'type' => 'required|string',
                'email' => 'required|email',
                'access_code'  => 'required|string',
                'phone' => 'nullable|string',
                'website' => 'nullable|string',
                'address' => 'nullable|string',
                'user_id' => 'required|integer',
            ],
        );

        $created = $this->service->createClassroomInstitution($validated);

        if (!$created) {
            $this->respondError(
                'Institution creation failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Institution created successfully.',
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{institution_id}/password', 'POST', ['api'])]
    public function savedPassword(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'institution_id' => 'required|integer',
                'password' => 'required|string',
            ],
        );

        $saved = $this->service->savePassword((int) $validated['institution_id'], $validated['password']);

        if (!$saved) {
            $this->respondError(
                'Failed to save password.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Password saved successfully.',
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{user_id}', 'GET', ['api'])]
    public function getProfile(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'user_id' => 'required|integer',
            ],
        );

        $profile = $this->service->getInstitutionByUserId((int) $validated['user_id']);

        if (empty($profile)) {
            $this->respondError(
                'Institution not found.',
                HttpStatus::NOT_FOUND
            );
        }

        $this->respond(
            [
                'status' => true,
                'data' => $profile,
            ],
            HttpStatus::OK
        );
    }
}
