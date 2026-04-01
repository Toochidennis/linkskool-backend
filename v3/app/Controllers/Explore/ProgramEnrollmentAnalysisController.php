<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProgramEnrollmentAnalysisService;

#[Group('/public')]
class ProgramEnrollmentAnalysisController extends ExploreBaseController
{
    private ProgramEnrollmentAnalysisService $analysisService;

    public function __construct()
    {
        parent::__construct();
        $this->analysisService = new ProgramEnrollmentAnalysisService($this->pdo);
    }

    #[Route('/learn/programs/{program_id}/enrollment-analysis/profiles', 'GET', ['api', 'auth'])]
    public function getProgramProfilesAnalysis(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'program_id' => 'required|integer',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:100',
                'course_id' => 'nullable|integer|min:1',
                'cohort_id' => 'nullable|integer|min:1',
                'payment_status' => 'nullable|string',
                'enrollment_status' => 'nullable|string',
                'enrollment_type' => 'nullable|string',
            ]
        );

        $data = $this->analysisService->getProgramProfilesAnalysis((int) $validated['program_id'], $validated);

        if (empty($data)) {
            $this->respondError(
                'Program not found.',
                HttpStatus::NOT_FOUND
            );
            return;
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program profiles analysis retrieved successfully.',
                'data' => $data,
            ],
            HttpStatus::OK
        );
    }

    #[
        Route(
            '/learn/programs/{program_id}/enrollment-analysis/profiles/{profile_id}/enrollments',
            'GET',
            ['api', 'auth']
        )
    ]
    public function getProfileEnrollments(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'program_id' => 'required|integer',
                'profile_id' => 'required|integer',
            ]
        );

        $data = $this->analysisService->getProfileEnrollments(
            (int) $validated['program_id'],
            (int) $validated['profile_id']
        );

        if (empty($data)) {
            $this->respondError(
                'Program not found.',
                HttpStatus::NOT_FOUND
            );
            return;
        }

        if (empty($data['profile'])) {
            $this->respondError(
                'Profile not found for this program.',
                HttpStatus::NOT_FOUND
            );
            return;
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Profile enrollments retrieved successfully.',
                'data' => $data,
            ],
            HttpStatus::OK
        );
    }
}
