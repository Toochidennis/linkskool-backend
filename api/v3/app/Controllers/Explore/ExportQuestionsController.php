<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\ExportQuestionService;

#[Group('/public')]
class ExportQuestionsController
{
    private ExportQuestionService $exportQuestionService;

    public function __construct()
    {
        $pdo = DatabaseConnector::connect();
        $this->exportQuestionService = new ExportQuestionService($pdo);
    }

    #[Route('/export-questions', 'GET')]
    public function exportQuestions()
    {
        $this->exportQuestionService->export();
    }
}
