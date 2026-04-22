<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\StudyContentGenerationService;

#[Group('/public/study')]
class StudyContentGenerationController extends ExploreBaseController
{
    private StudyContentGenerationService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudyContentGenerationService($this->pdo);
    }

    #[Route('/generate', 'GET')]
    public function generate(): void
    {
        $script = realpath(__DIR__ . '/../../Common/Scripts/process_study_content.php');

        if ($script === false) {
            $this->respond(['message' => 'Study content generation script not found'], 500);
            return;
        }

        $limit = 4;
        exec(sprintf('php %s %d > /dev/null 2>&1 &', escapeshellarg($script), $limit));

        $this->respond(['message' => 'Study content generation initiated']);
    }

    #[Route('/content', 'GET')]
    public function get()
    {
        $this->respond([
            'message' => 'Study content generation endpoint',
            'data' => $this->service->getGeneratedContent()
        ]);
    }
}
