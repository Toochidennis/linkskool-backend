<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\StudyContentGenerationService;
use V3\App\Services\Explore\StudyContentSeedService;

#[Group('/public/cbt/study')]
class StudyContentGenerationController extends ExploreBaseController
{
    private StudyContentGenerationService $service;
    private StudyContentSeedService $studyContentSeedService;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudyContentGenerationService($this->pdo);
        $this->studyContentSeedService = new StudyContentSeedService($this->pdo);
    }

    #[Route('/generate-content', 'GET')]
    public function generate(): void
    {
        $script = realpath(__DIR__ . '/../../Common/Scripts/process_study_content.php');

        if ($script === false) {
            $this->respond(['message' => 'Study content generation script not found'], 500);
            return;
        }

        $limit = 4;
        exec(\sprintf('php %s %d > /dev/null 2>&1 &', escapeshellarg($script), $limit));

        $this->respond(['message' => 'Study content generation initiated']);
    }

    #[Route('/seed', 'POST')]
    public function seed(): void
    {
        $this->studyContentSeedService->seedStudyContent();
        $this->respond(['message' => 'Study content seeding completed']);
    }

    #[Route('/contents', 'GET')]
    public function get()
    {
        $this->respond([
            'message' => 'Study content generation endpoint',
            'data' => $this->service->getGeneratedContent()
        ]);
    }
}
