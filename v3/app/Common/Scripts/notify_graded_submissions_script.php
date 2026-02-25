<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Events\Registrars\RegisterEventListeners;
use V3\App\Common\Utilities\EnvLoader;
use V3\App\Database\DatabaseConnector;
use V3\App\Models\Explore\CohortTasksSubmission;
use V3\App\Services\Explore\GradeLessonAssignmentService;

EnvLoader::load();
RegisterEventListeners::register();

$pdo = DatabaseConnector::connect();
$submissionModel = new CohortTasksSubmission($pdo);
$gradingService = new GradeLessonAssignmentService($pdo);

$batchSize = 200;
$totalProcessed = 0;
$systemNotifiedBy = 0;

while (true) {
    $limit = max(1, (int) $batchSize);
    $rows = $submissionModel->rawQuery(
        sprintf(
            "
        SELECT id
        FROM cohort_tasks_submissions
        WHERE graded_at IS NOT NULL
          AND assigned_score IS NOT NULL
          AND notified_at IS NULL
        ORDER BY graded_at ASC, id ASC
        LIMIT %d
        ",
            $limit
        )
    );

    if (empty($rows)) {
        break;
    }

    $submissionIds = array_map(
        static fn(array $row): int => (int) $row['id'],
        $rows
    );

    $gradingService->notifyStudents($submissionIds, $systemNotifiedBy);
    $totalProcessed += count($submissionIds);

    if (count($rows) < $batchSize) {
        break;
    }
}

// echo sprintf("Processed graded notifications: %d\n", $totalProcessed);
