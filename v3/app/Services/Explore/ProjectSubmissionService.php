<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\ProjectSubmission;

class ProjectSubmissionService
{
    private ProjectSubmission $projectSubmissionModel;
    private FileHandler $fileHandler;

    public function __construct(\PDO $pdo)
    {
        $this->projectSubmissionModel = new ProjectSubmission($pdo);
    }


    public function submitProject(array $data): bool|int
    {
        $assignment = $this->fileHandler->handleFiles($data['assignment']);

        $insertData = [
            'project_file' => json_encode($assignment),
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'quiz_score' => $data['quiz_score'],
            'comments' => $data['comments'] ?? null,
        ];

        return $this->projectSubmissionModel->insert($insertData);
    }
}
