<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Discussion\DiscussionStarted;
use V3\App\Services\Explore\DiscussionNotificationTargetService;

class SendDiscussionStartedPushNotification
{
    public function __invoke(DiscussionStarted $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new DiscussionNotificationTargetService($pdo);

            $discussion = $service->getDiscussionContext($event->discussionId);
            if (empty($discussion)) {
                return;
            }

            $authorName = $service->buildFullName(
                $discussion['author_first_name'] ?? null,
                $discussion['author_last_name'] ?? null
            );
            $cohortTitle = $discussion['cohort_title'] ?? null;

            if ($authorName === null || $cohortTitle === null) {
                return;
            }

            $recipients = $service->getRecipientsForDiscussionStarted($event->discussionId);
            if (empty($recipients)) {
                return;
            }

            $title = 'New Discussion Started';
            $body = sprintf('%s started a discussion in "%s"', $authorName, $cohortTitle);
            $data = $service->buildPushData(
                'discussion_started',
                [
                    'discussion_id' => $event->discussionId,
                    'cohort_id' => $discussion['cohort_id'] ?? null,
                    'course_id' => $discussion['course_id'] ?? null,
                    'program_id' => $discussion['program_id'] ?? null,
                    'profile_id' => $discussion['author_id'] ?? null,
                    'event_key' => sprintf('discussion_started:discussion:%d', $event->discussionId),
                ]
            );

            $service->sendPushInBatches($recipients, $title, $body, $data);
        } catch (\Throwable) {
            return;
        }
    }
}
