<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\CohortCourseEnrolled;
use V3\App\Models\Explore\CbtUser;
use V3\App\Models\Explore\Program;
use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Models\Explore\ProgramProfile;
use V3\App\Models\Explore\LearningCourse;
use V3\App\Services\Common\MailService;

class SendCohortCourseEnrolledEmail
{
    public function __invoke(CohortCourseEnrolled $event): void
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

            $program = (new Program($pdo))
                ->where('id', $event->programId)
                ->first();
            $course = (new LearningCourse($pdo))
                ->where('id', $event->courseId)
                ->first();
            $cohort = (new ProgramCourseCohort($pdo))
                ->where('id', $event->cohortId)
                ->first();

            $studentName = trim(
                (string) ($profile['first_name'] ?? '') . ' ' .
                    (string) ($profile['last_name'] ?? '')
            );
            $studentName = $studentName !== '' ? $studentName : 'Student';
            $programName = (string) ($program['name'] ?? 'Program');
            $courseName = (string) ($event->courseName ?? $course['title']);

            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/cohort_course_enrolled.php',
                [
                    'student_name' => $studentName,
                    'program_name' => $programName,
                    'course_name' => $courseName,
                    'cohort_description' => (string) ($cohort['description'] ?? ''),
                    'cohort_benefits' => (string) ($cohort['benefits'] ?? ''),
                    'cohort_image_url' => (string) ($cohort['image_url'] ?? ''),
                    'cohort_start_date' => (string) ($cohort['start_date'] ?? ''),
                    'cohort_end_date' => (string) ($cohort['end_date'] ?? ''),
                    'instructor_name' => (string) ($cohort['instructor_name'] ?? ''),
                ]
            );

            (new MailService($pdo))->send(
                "{$studentName} <{$student['email']}>",
                'You are enrolled: ' . $courseName,
                $html
            );
        } catch (\Throwable) {
            return;
        }
    }
}
