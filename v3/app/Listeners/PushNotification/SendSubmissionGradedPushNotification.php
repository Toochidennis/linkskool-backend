<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\SubmissionGraded;
use V3\App\Models\Common\UserDeviceToken;
use V3\App\Models\Explore\CbtUser;
use V3\App\Models\Explore\ProgramCourseCohortLesson;
use V3\App\Models\Explore\ProgramProfile;
use V3\App\Services\Common\NotificationService;

class SendSubmissionGradedPushNotification
{
    public function __invoke(SubmissionGraded $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();

            $profile = (new ProgramProfile($pdo))->where('id', $event->profileId)->first();
            if (empty($profile)) {
                return;
            }

            $student = (new CbtUser($pdo))->where('id', $profile['user_id'])->first();
            if (empty($student)) {
                return;
            }

            $lesson = (new ProgramCourseCohortLesson($pdo))->where('id', $event->lessonId)->first();

            $tokens = (new UserDeviceToken($pdo))
                ->select(['fcm_token'])
                ->where('user_id', $student['id'])
                ->get();

            $tokens = array_values(array_filter(array_unique(array_column($tokens, 'fcm_token'))));

            if (empty($tokens)) {
                return;
            }

            $lessonTitle = (string) ($lesson['title'] ?? 'your lesson');
            $title = 'Assignment Graded';
            $body = sprintf(
                'Your submission for %s has been graded. Score: %s%%',
                $lessonTitle,
                (string) $event->assignedScore
            );

            $data = [
                'lesson_id' => (string) $event->lessonId,
                'cohort_id' => (string)  $lesson['cohort_id'],
                'profile_id' => (string) $event->profileId,
                'course_id' => (string) $lesson['course_id'],
                'program_id' => (string) $lesson['program_id'],
            ];

            $notificationService = new NotificationService($pdo);
            foreach ($tokens as $token) {
                $notificationService->send($token, $title, $body, $data);
            }
        } catch (\Throwable) {
            return;
        }
    }
}
