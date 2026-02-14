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
        $conditions = [
            ["profile_id", "=", $data['profile_id']],
            ["cohort_id", "=", $data['cohort_id']],
            ["lesson_id", "=", $data['lesson_id']],
        ];

        $query = $this->cohortSubmissionModel
            ->whereGroup($conditions);

        if ($query->exists()) {
            $updateData = $this->buildSubmissionPayload($data, false);

            if (empty($updateData)) {
                return false;
            }

            return $this->cohortSubmissionModel
                ->whereGroup($conditions)
                ->update($updateData);
        }

        $insertData = $this->buildSubmissionPayload($data, true);
        return $this->cohortSubmissionModel->insert($insertData);
    }

    private function buildSubmissionPayload(array $data, bool $isInsert): array
    {
        $payload = [];

        if ($isInsert) {
            $payload['profile_id'] = $data['profile_id'];
            $payload['cohort_id'] = $data['cohort_id'];
            $payload['lesson_id'] = $data['lesson_id'];
        }

        if (isset($data['submission_type'])) {
            $payload['submission_type'] = $data['submission_type'];
        }

        if (!empty($data['quiz_score']) && $data['quiz_score'] !== 0) {
            $payload['quiz_score'] = $data['quiz_score'];
        }

        if (!empty($data['text_content'])) {
            $payload['text_content'] = $data['text_content'];
        }

        if (!empty($data['link_url'])) {
            $payload['link_url'] = $data['link_url'];
        }

        if (!empty($data['assignment'])) {
            $assignment = $this->fileHandler->handleFiles($data['assignment']);
            $payload['files'] = json_encode($assignment);
        }

        return $payload;
    }
}
