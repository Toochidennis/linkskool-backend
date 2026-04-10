<?php

namespace V3\App\Services\Explore;

use DateTimeImmutable;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class NotificationAudienceResolverService
{
    private BillingService $billingService;
    private CourseCohortEnrollmentService $courseCohortEnrollmentService;
    private ProgramCourseCohortLesson $lessonModel;

    public function __construct(private \PDO $pdo)
    {
        $this->billingService = new BillingService($pdo);
        $this->courseCohortEnrollmentService = new CourseCohortEnrollmentService($pdo);
        $this->lessonModel = new ProgramCourseCohortLesson($pdo);
    }

    public function getOptions(): array
    {
        return [
            'modes' => [
                [
                    'value' => 'reminder',
                    'label' => 'Reminder',
                    'enabled' => true,
                ],
                [
                    'value' => 'custom',
                    'label' => 'Custom',
                    'enabled' => false,
                ],
            ],
            'audiences' => [
                [
                    'value' => 'dropped_subscription_cart_users',
                    'label' => 'Dropped subscription cart users',
                    'supported_modes' => ['reminder'],
                    'supported_reminder_types' => ['abandoned_cart'],
                    'filters' => [],
                ],
                [
                    'value' => 'dropped_course_cohort_cart_users',
                    'label' => 'Dropped course cohort cart users',
                    'supported_modes' => ['reminder'],
                    'supported_reminder_types' => ['abandoned_course_cohort_cart'],
                    'filters' => [
                        [
                            'key' => 'cadence',
                            'label' => 'Cadence',
                            'required' => false,
                            'options' => ['generic', 'one_hour'],
                        ],
                    ],
                ],
                [
                    'value' => 'all_enrolled_users',
                    'label' => 'All enrolled users',
                    'supported_modes' => ['reminder'],
                    'supported_reminder_types' => [
                        'class_reminder',
                        'live_class_reminder',
                        'assignment_due_reminder',
                    ],
                    'filters' => [],
                ],
                [
                    'value' => 'program_enrolled_users',
                    'label' => 'Users enrolled to a program',
                    'supported_modes' => ['reminder'],
                    'supported_reminder_types' => [
                        'class_reminder',
                        'live_class_reminder',
                        'assignment_due_reminder',
                    ],
                    'filters' => [
                        [
                            'key' => 'program_id',
                            'label' => 'Program',
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'value' => 'cohort_enrolled_users',
                    'label' => 'Users enrolled to a cohort',
                    'supported_modes' => ['reminder'],
                    'supported_reminder_types' => [
                        'class_reminder',
                        'live_class_reminder',
                        'assignment_due_reminder',
                    ],
                    'filters' => [
                        [
                            'key' => 'cohort_id',
                            'label' => 'Cohort',
                            'required' => true,
                        ],
                    ],
                ],
            ],
            'reminder_types' => [
                [
                    'value' => 'abandoned_cart',
                    'label' => 'Abandoned subscription cart',
                ],
                [
                    'value' => 'abandoned_course_cohort_cart',
                    'label' => 'Abandoned course cohort cart',
                ],
                [
                    'value' => 'class_reminder',
                    'label' => 'Class reminder',
                ],
                [
                    'value' => 'live_class_reminder',
                    'label' => 'Live class reminder',
                ],
                [
                    'value' => 'assignment_due_reminder',
                    'label' => 'Assignment due reminder',
                ],
            ],
        ];
    }

    public function resolveReminderTargets(array $data): array
    {
        $reminderType = (string) $data['reminder_type'];
        $limit = $this->resolveLimit($data);

        return match ($reminderType) {
            'abandoned_cart' => [
                'targets' => $this->billingService->getAbandonedCartReminderCandidatesAfterMinutes(
                    max(0, (int) ($data['minutes_since_abandoned'] ?? 0)),
                    $limit
                ),
            ],
            'abandoned_course_cohort_cart' => [
                'targets' => $this->courseCohortEnrollmentService->getAbandonedCartReminderCandidatesAfterMinutes(
                    max(0, (int) ($data['minutes_since_abandoned'] ?? 0)),
                    $limit
                ),
                'cadence' => (string) ($data['cadence'] ?? 'generic'),
            ],
            'class_reminder', 'live_class_reminder', 'assignment_due_reminder' => [
                'targets' => $this->resolveLessonReminderTargets($data, $reminderType, $limit),
            ],
            default => throw new \InvalidArgumentException('Unsupported reminder type.'),
        };
    }

    private function resolveLessonReminderTargets(array $data, string $reminderType, int $limit): array
    {
        $bindings = [];
        $filtersSql = $this->buildLessonAudienceFilterSql($data, $bindings);

        if ($reminderType === 'class_reminder') {
            return $this->lessonModel->rawQuery(
                sprintf(
                    "
                    SELECT l.id
                    FROM program_course_cohort_lessons l
                    WHERE l.status = 'published'
                      AND l.lesson_date IS NOT NULL
                      AND DATE(l.lesson_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                      %s
                    ORDER BY l.lesson_date ASC
                    LIMIT %d
                    ",
                    $filtersSql,
                    $limit
                ),
                $bindings
            );
        }

        if ($reminderType === 'assignment_due_reminder') {
            return $this->lessonModel->rawQuery(
                sprintf(
                    "
                    SELECT l.id
                    FROM program_course_cohort_lessons l
                    WHERE l.status = 'published'
                      AND l.assignment_due_date IS NOT NULL
                      AND DATE(l.assignment_due_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                      %s
                    ORDER BY l.assignment_due_date ASC
                    LIMIT %d
                    ",
                    $filtersSql,
                    $limit
                ),
                $bindings
            );
        }

        $rows = $this->lessonModel->rawQuery(
            sprintf(
                "
                SELECT l.id, l.zoom_info
                FROM program_course_cohort_lessons l
                WHERE l.status = 'published'
                  AND l.zoom_info IS NOT NULL
                  AND TRIM(l.zoom_info) <> ''
                  AND TRIM(l.zoom_info) <> '{}'
                  %s
                ORDER BY l.id ASC
                LIMIT %d
                ",
                $filtersSql,
                $limit
            ),
            $bindings
        );

        $now = new DateTimeImmutable();
        $next30Minutes = $now->modify('+30 minutes');
        $eligibleRows = [];

        foreach ($rows as $row) {
            $zoomInfo = json_decode((string) ($row['zoom_info'] ?? ''), true);
            if (!\is_array($zoomInfo)) {
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
            } catch (\Throwable) {
                continue;
            }

            if ($endTime <= $now) {
                continue;
            }

            if ($startTime > $now && $startTime <= $next30Minutes) {
                $eligibleRows[] = ['id' => (int) $row['id']];
            }
        }

        return $eligibleRows;
    }

    private function buildLessonAudienceFilterSql(array $data, array &$bindings): string
    {
        return match ((string) $data['audience']) {
            'program_enrolled_users' => $this->bindProgramFilter($data, $bindings),
            'cohort_enrolled_users' => $this->bindCohortFilter($data, $bindings),
            default => '',
        };
    }

    private function bindProgramFilter(array $data, array &$bindings): string
    {
        $bindings['program_id'] = (int) $data['program_id'];
        return ' AND l.program_id = :program_id';
    }

    private function bindCohortFilter(array $data, array &$bindings): string
    {
        $bindings['cohort_id'] = (int) $data['cohort_id'];
        return ' AND l.cohort_id = :cohort_id';
    }

    private function resolveLimit(array $data): int
    {
        return max(1, (int) ($data['limit'] ?? 200));
    }
}
