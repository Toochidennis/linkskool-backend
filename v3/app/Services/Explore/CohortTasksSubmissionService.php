<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\CohortTasksSubmission;

class CohortTasksSubmissionService
{
    private CohortTasksSubmission $cohortSubmissionModel;
    private FileHandler $fileHandler;

    public function __construct(\PDO $pdo)
    {
        $this->cohortSubmissionModel = new CohortTasksSubmission($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function submitProject(array $data): bool|int
    {
        $assignment = $this->fileHandler->handleFiles($data['assignment']);

        $insertData = [
            'assignment' => json_encode($assignment),
            'profile_id' => $data['profile_id'],
            'cohort_id' => $data['cohort_id'],
            'lesson_id' => $data['lesson_id'],
            'quiz_score' => $data['quiz_score'],
        ];

        return $this->cohortSubmissionModel->insert($insertData);
    }

    public function getSubmissionByUserAndLesson(int $userId, int $lessonId): array
    {
        return $this->cohortSubmissionModel
            ->where('profile_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
    }
}
