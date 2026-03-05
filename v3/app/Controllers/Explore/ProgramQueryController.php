<?php

namespace V3\App\Controllers\Explore;

use V3\App\Services\Explore\ProgramQueryService;
use V3\App\Common\Routing\Route;
use V3\App\Common\Routing\Group;

#[Group('/public/programs')]
class ProgramQueryController extends ExploreBaseController
{
    private ProgramQueryService $programQueryService;

    public function __construct()
    {
        parent::__construct();
        $this->programQueryService = new ProgramQueryService($this->pdo);
    }

    #[Route('', 'GET', ['api'])]
    public function getPrograms(): void
    {
        $data = $this->programQueryService->getPrograms();

        $this->respond(
            [
                'success' => true,
                'message' => 'Programs retrieved successfully.',
                'data' => $data,
            ]
        );
    }

    #[Route('/{slug}/courses', 'GET', ['api'])]
    public function getProgramCourseCohorts(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'slug' => 'required|string',
            ]
        );

        $data = $this->programQueryService->getProgramCourseCohorts($validated['slug']);

        $this->respond(
            [
                'success' => true,
                'message' => 'Program courses and cohorts retrieved successfully.',
                'data' => $data,
            ]
        );
    }

    #[Route('/cohorts/{slug}', 'GET', ['api'])]
    public function getCohortDetails(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'slug' => 'required|string',
            ]
        );

        $data = $this->programQueryService->getCohortDetails($validated['slug']);

        if (!$data) {
            $this->respondError(
                'Cohort not found.',
                404
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Cohort details retrieved successfully.',
                'data' => $data,
            ]
        );
    }
}
