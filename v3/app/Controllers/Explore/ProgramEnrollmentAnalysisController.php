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

    #[Route('/learn/programs/{program_id}/enrollment-analysis', 'GET', ['api', 'auth'])]
    public function getProgramEnrollmentAnalysis(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'program_id' => 'required|integer',
            ]
        );

        $data = $this->analysisService->getProgramEnrollmentAnalysis((int) $validated['program_id']);

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
                'message' => 'Program enrollment analysis retrieved successfully.',
                'data' => $data,
            ],
            HttpStatus::OK
        );
    }
}
