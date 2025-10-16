<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\ELearning\ContentManagerService;

#[Group('/portal')]
class ContentManagerController extends BaseController
{
    private ContentManagerService $contentManagerService;

    public function __construct()
    {
        parent::__construct();
        $this->contentManagerService = new ContentManagerService(pdo: $this->pdo);
    }

    #[Route(
        '/elearning/overview',
        'GET',
        ['auth', 'role:admin']
    )]
    public function getDashboard(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: [
                'term' => 'required|integer|in:1,2,3',
            ]
        );

        $dashboardData = $this->contentManagerService
            ->getDashboard([
                ...$filteredVars,
                'role' => 'admin'
            ]);

        return $this->respond(
            data: [
                'success' => true,
                'response' => $dashboardData,
            ]
        );
    }

    #[Route(
        '/elearning/staff/{teacher_id:\d+}/overview',
        'GET',
        ['auth', 'role:staff']
    )]
    public function staffDashboardSummary(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: [
                'teacher_id' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer',
            ]
        );

        $summaryData = $this->contentManagerService
            ->getDashboard([...$filteredVars, 'role' => 'staff']);

        return $this->respond(
            data: [
                'success' => true,
                'data' => $summaryData,
            ]
        );
    }

    #[Route(
        '/elearning/syllabus/{syllabus_id:\d+}/contents',
        'GET',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function getAllContents(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: ['syllabus_id' => 'required|integer|min:1',]
        );

        $this->respond(
            data: [
                'success' => true,
                'response' => $this->contentManagerService
                    ->getContents(syllabusId: $filteredVars['syllabus_id']),
            ],
        );
    }

    #[Route(
        '/elearning/contents/{id:\d+}',
        'GET',
        ['auth']
    )]
    public function getContentById(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: ['id' => 'required|integer|min:1',]
        );

        $this->respond(
            data: [
                'success' => true,
                'response' => $this->contentManagerService
                    ->getContent($filteredVars['id']),
            ],
        );
    }

    #[Route(
        '/elearning/contents/{content_id:\d+}',
        'DELETE',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function delete(array $vars)
    {
        $data = $this->validate(data: $vars, rules: ['content_id' => 'required|integer']);

        $id = $this->contentManagerService->deleteContent($data['content_id']);

        if ($id > 0) {
            return $this->respond(
                data: [
                    'success' => true,
                    'message' => 'Content deleted successfully.',
                ]
            );
        }

        return $this->respondError(
            'Content not found or already deleted.',
            HttpStatus::BAD_REQUEST
        );
    }
}
