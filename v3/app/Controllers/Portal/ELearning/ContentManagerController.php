<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\ContentManagerService;

class ContentManagerController extends BaseController
{
    private ContentManagerService $contentManagerService;

    public function __construct()
    {
        parent::__construct();
        $this->contentManagerService = new ContentManagerService(pdo: $this->pdo);
    }

    public function getAllContents(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: ['syllabus_id' => 'required|integer',]
        );

        try {
            $this->respond(
                data: [
                    'success' => true,
                    'response' => $this->contentManagerService
                        ->getContents(syllabusId: $filteredVars['syllabus_id']),
                ],
            );
        } catch (\Exception $e) {
            $this->respondError(message: $e->getMessage());
        }
    }
}
