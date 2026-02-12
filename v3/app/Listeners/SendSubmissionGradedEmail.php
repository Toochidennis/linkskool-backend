<?php

namespace V3\App\Listeners;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\SubmissionGraded;
use V3\App\Services\Common\MailService;
use V3\App\Models\Explore\ProgramProfile;
use V3\App\Models\Explore\CbtUser;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class SendSubmissionGradedEmail
{
    public function __invoke(SubmissionGraded $event): void
    {
        $pdo = DatabaseConnector::connect();

        $profile = (new ProgramProfile($pdo))->where('id', $event->profileId)->first();
        $student = (new CbtUser($pdo))->where('id', $profile['user_id'])->first();
        $lesson = (new ProgramCourseCohortLesson($pdo))->where('id', $event->lessonId)->first();

        if (!$student || empty($student['email'])) {
            return;
        }

        $name = trim($profile['first_name'] . ' ' . $profile['last_name']);
        $student['name'] = !empty($name) ? $name : 'Student';

        $html = TemplateRenderer::render(
            __DIR__ . '/../../Templates/emails/submission_graded.php',
            [
                'student_name' => $student['name'],
                'assigned_score' => $event->assignedScore,
                'remark' => $event->remark,
                'comment' => $event->comment,
                'lesson_title' => $lesson['title'],
            ]
        );

        (new MailService())->send(
            "$student[name] <{$student['email']}>",
            'Your Assignment Has Been Graded',
            $html
        );
    }
}
