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
        $exists = $this->cohortSubmissionModel
            ->where('profile_id', $data['profile_id'])
            ->where('lesson_id', $data['lesson_id'])
            ->exists();

        if ($exists) {
            $updateData = [
                'quiz_score' => $data['quiz_score'],
            ];
            if (!empty($data['assignment'])) {
                $updateData['assignment'] = json_encode($this->fileHandler->handleFiles($data['assignment']));
            }
            return $this->cohortSubmissionModel
                ->where('profile_id', $data['profile_id'])
                ->where('lesson_id', $data['lesson_id'])
                ->where('cohort_id', $data['cohort_id'])
                ->update($updateData);
        }

        $insertData = [
            'profile_id' => $data['profile_id'],
            'cohort_id' => $data['cohort_id'],
            'lesson_id' => $data['lesson_id'],
            'submission_type' => $data['submission_type'],
            'quiz_score' => $data['quiz_score'],
        ];

        if (!empty($data['text_content'])) {
            $insertData['text_content'] = $data['text_content'];
        }

        if (!empty($data['link_url'])) {
            $insertData['link_url'] = $data['link_url'];
        }

        if (!empty($data['assignment'])) {
            $assignment = $this->fileHandler->handleFiles($data['assignment']);
            $insertData['files'] = json_encode($assignment);
        }

        return $this->cohortSubmissionModel->insert($insertData);
    }

    public function gradeSubmission(array $data): bool
    {
        return $this->cohortSubmissionModel
            ->where('id', $data['submission_id'])
            ->update([
                'assigned_score' => $data['assigned_score'],
                'remark' => $data['remark'] ?? null,
                'comment' => $data['comment'] ?? null,
                'graded_by' => $data['graded_by'],
                'graded_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
