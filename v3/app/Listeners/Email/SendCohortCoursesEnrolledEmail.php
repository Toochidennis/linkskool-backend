<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\CohortCoursesEnrolled;
use V3\App\Models\Explore\CbtUser;
use V3\App\Models\Explore\ProgramProfile;
use V3\App\Services\Common\MailService;

class SendCohortCoursesEnrolledEmail
{
    public function __invoke(CohortCoursesEnrolled $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();

            $profile = (new ProgramProfile($pdo))
                ->where('id', $event->profileId)
                ->first();

            if (empty($profile)) {
                return;
            }

            $student = (new CbtUser($pdo))
                ->where('id', $profile['user_id'])
                ->first();

            if (empty($student['email'])) {
                return;
            }

            $studentName = trim(
                (string) ($profile['first_name'] ?? '') . ' ' .
                    (string) ($profile['last_name'] ?? '')
            );

            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/cohort_courses_enrolled.php',
                [
                    'student_name' => $studentName,
                    'items' => $event->items,
                ]
            );

            (new MailService($pdo))->send(
                "{$studentName} <{$student['email']}>",
                'Your course enrollments are confirmed',
                $html
            );
        } catch (\Throwable) {
            return;
        }
    }
}
