<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProgramProfileService;

#[Group('/public/learning/profiles')]
class ProgramProfileController extends ExploreBaseController
{
    private ProgramProfileService $profileService;

    public function __construct()
    {
        parent::__construct();
        $this->profileService = new ProgramProfileService($this->pdo);
    }

    #[Route('', 'POST', ['api'])]
    public function createProfile(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'user_id' => 'required|integer',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'birth_date' => 'required|date',
                'gender' => 'required|string|in:male,female,other',
                'certificate_name' => 'nullable|string',
            ],
        );

        $profile = $this->profileService->createProfile($validated);

        if (empty($profile)) {
            $this->respondError(
                'Profile creation failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'Cohort enrollment profile created successfully.',
                'data' => $profile
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{id}', 'PUT', ['api'])]
    public function updateProfile(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'first_name' => 'nullable|string',
                'last_name' => 'nullable|string',
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|string|in:male,female,other',
                'certificate_name' => 'nullable|string',
                'user_id' => 'required|integer',
            ],
        );

        $profiles = $this->profileService->updateProfile($validated);

        if (empty($profiles)) {
            $this->respondError(
                'Profile update failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'Cohort enrollment profile updated successfully.',
                'data' => $profiles
            ],
            HttpStatus::OK
        );
    }

    #[Route('', 'GET', ['api'])]
    public function getProfilesByUserId(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'user_id' => 'required|integer',
            ],
        );

        $profiles = $this->profileService->getProfilesByUserId($validated['user_id']);

        $this->respond(
            [
                'status' => true,
                'data' => $profiles,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}', 'DELETE', ['api'])]
    public function deleteProfile(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
            ],
        );

        $success = $this->profileService->deleteProfile($validated['id']);

        if (!$success) {
            $this->respondError(
                'Profile deletion failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'Cohort enrollment profile deleted successfully.',
            ],
            HttpStatus::OK
        );
    }
}
