<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CohortEnrollmentProfileService;

#[Group('/public/cohorts/{cohortId}/profiles')]
class CohortEnrollmentProfileController extends ExploreBaseController
{
    private CohortEnrollmentProfileService $profileService;

    public function __construct()
    {
        parent::__construct();
        $this->profileService = new CohortEnrollmentProfileService($this->pdo);
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
                'email' => 'required|email',
                'birth_year' => 'required|integer',
                'phone' => 'required|string',
            ],
        );

        $profileId = $this->profileService->createProfile($validated);

        if (!$profileId) {
            $this->respondError(
                'Profile creation failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'Cohort enrollment profile created successfully.',
                'data' => $profileId
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('', 'PUT', ['api'])]
    public function updateProfile(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'first_name' => 'string',
                'last_name' => 'string',
                'email' => 'email',
                'birth_year' => 'integer',
                'phone' => 'string',
                'certificate_name' => 'string',
            ],
        );

        $success = $this->profileService->updateProfile($validated);

        if (!$success) {
            $this->respondError(
                'Profile update failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'Cohort enrollment profile updated successfully.',
            ],
            HttpStatus::OK
        );
    }

    public function getProfilesByUserId(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
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
