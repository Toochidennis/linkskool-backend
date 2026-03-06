<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Services\Explore\DownloadExamsService;

#[Group('/public/cbt')]
class DownloadExamsController extends ExploreBaseController
{
    private DownloadExamsService $downloadExamsService;

    public function __construct()
    {
        parent::__construct();
        $this->downloadExamsService = new DownloadExamsService($this->pdo);
    }

    #[\V3\App\Common\Routing\Route('/exams/{exam_type}/download', 'GET', ['api'])]
    public function downloadExamType(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
<<<<<<< HEAD
                'exam_type' => 'required|integer|min:1',
                'course_id' => 'sometimes|integer|min:0'
=======
                'exam_type' => 'required|integer|min:1'
>>>>>>> refs/remotes/origin/main
            ]
        );

        $examType = (int)$validated['exam_type'];
<<<<<<< HEAD
        $courseId = isset($validated['course_id']) ? (int)$validated['course_id'] : 0;
        $zipPath = $this->downloadExamsService->buildZip($examType, $courseId);
=======
        $zipPath = $this->downloadExamsService->buildZip($examType);
>>>>>>> refs/remotes/origin/main

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
        header('Expires: 0');
        header('Content-Length: ' . filesize($zipPath));

        if (ob_get_length()) {
            ob_end_clean();
        }

        readfile($zipPath);
        unlink($zipPath);
        exit;
    }
}
