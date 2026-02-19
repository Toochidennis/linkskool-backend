<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\CohortTasksSubmission;

class CohortTasksSubmissionService
{
    private CohortTasksSubmission $cohortSubmissionModel;
    private BulkAutoGradeService $bulkAutoGradeService;
    private FileHandler $fileHandler;

    public function __construct(\PDO $pdo)
    {
        $this->cohortSubmissionModel = new CohortTasksSubmission($pdo);
        $this->bulkAutoGradeService = new BulkAutoGradeService($pdo, $this->cohortSubmissionModel);
        $this->fileHandler = new FileHandler();
    }

    public function submitProject(array $data): bool|int
    {
        $conditions = [
            ['profile_id', '=', $data['profile_id']],
            ['cohort_id', '=', $data['cohort_id']],
            ['lesson_id', '=', $data['lesson_id']],
        ];

        $query = $this->cohortSubmissionModel
            ->whereGroup($conditions);

        if ($query->exists()) {
            $existingSubmission = $this->cohortSubmissionModel
                ->select(['id'])
                ->whereGroup($conditions)
                ->first();
            $updateData = $this->buildSubmissionPayload($data, false);

            if (empty($updateData) || empty($existingSubmission['id'])) {
                return false;
            }

            $updated = $this->cohortSubmissionModel
                ->whereGroup($conditions)
                ->update($updateData);

            if (!$updated) {
                return false;
            }

            return (int) $existingSubmission['id'];
        }

        $insertData = $this->buildSubmissionPayload($data, true);
        $submissionId = $this->cohortSubmissionModel->insert($insertData);

        if (!$submissionId) {
            return false;
        }

        return (int) $submissionId;
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
            $groupPath = sprintf(
                'explore/submissions/cohorts/%d/lessons/%d/profiles/%d',
                (int)$data['cohort_id'],
                (int)$data['lesson_id'],
                (int)$data['profile_id']
            );
            $assignment = $this->fileHandler->handleFiles($data['assignment'], false, $groupPath);
            $payload['files'] = json_encode($assignment);
        }

        $payload['notified_at'] = null;
        $payload['graded_at'] = null;

        return $payload;
    }

    public function autoGradeSubmission(int $submissionId, int $gradedBy): void
    {
        try {
            $this->bulkAutoGradeService->autoGradeSubmissions([
                'submission_ids' => [$submissionId],
                'graded_by' => $gradedBy,
                'persist_review' => true,
                'apply_submission_state' => true,
            ]);
        } catch (\Throwable) {
            // Best-effort background grading. Submission success should not fail if grading fails.
        }
    }
}
