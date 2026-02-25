<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Events\EventDispatcher;
use V3\App\Common\Events\Registrars\RegisterEventListeners;
use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Utilities\Logger;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\AssignmentDueReminderDue;
use V3\App\Events\Lesson\ClassReminderDue;
use V3\App\Events\Lesson\LiveClassReminderDue;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

EnvLoader::load();
Logger::init();
RegisterEventListeners::register();

$pdo = DatabaseConnector::connect();
$lessonModel = new ProgramCourseCohortLesson($pdo);

$now = new DateTimeImmutable();
$next30Minutes = $now->modify('+30 minutes')->format('Y-m-d H:i:s');

$classReminderLessons = $lessonModel->rawQuery(
    "
    SELECT id
    FROM program_course_cohort_lessons
    WHERE status = 'published'
      AND lesson_date IS NOT NULL
      AND DATE(lesson_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    "
);

foreach ($classReminderLessons as $row) {
    EventDispatcher::dispatch(new ClassReminderDue((int) $row['id']));
}

$liveLessons = $lessonModel->rawQuery(
    "
    SELECT id, zoom_info
    FROM program_course_cohort_lessons
    WHERE status = 'published'
      AND zoom_info IS NOT NULL
      AND TRIM(zoom_info) <> ''
      AND TRIM(zoom_info) <> '{}'
    "
);

$liveReminderCount = 0;

foreach ($liveLessons as $row) {
    $zoomInfo = json_decode((string) ($row['zoom_info'] ?? ''), true);
    if (!is_array($zoomInfo)) {
        continue;
    }

    $start = $zoomInfo['start_time'] ?? null;
    $end = $zoomInfo['end_time'] ?? null;

    if (empty($start) || empty($end)) {
        continue;
    }

    try {
        $startTime = new DateTimeImmutable((string) $start);
        $endTime = new DateTimeImmutable((string) $end);
    } catch (Throwable) {
        continue;
    }

    if ($endTime <= $now) {
        continue;
    }

    if ($startTime > $now && $startTime->format('Y-m-d H:i:s') <= $next30Minutes) {
        EventDispatcher::dispatch(new LiveClassReminderDue((int) $row['id']));
        $liveReminderCount++;
    }
}

$assignmentDueLessons = $lessonModel->rawQuery(
    "
    SELECT id
    FROM program_course_cohort_lessons
    WHERE status = 'published'
      AND assignment_due_date IS NOT NULL
      AND DATE(assignment_due_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    "
);

foreach ($assignmentDueLessons as $row) {
    EventDispatcher::dispatch(new AssignmentDueReminderDue((int) $row['id']));
}

echo sprintf(
    "Class reminders: %d | Live reminders: %d | Assignment reminders: %d\n",
    count($classReminderLessons),
    $liveReminderCount,
    count($assignmentDueLessons)
);
