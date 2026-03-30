<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Discussion\DiscussionReplyReplied;
use V3\App\Services\Explore\DiscussionNotificationTargetService;

class SendDiscussionReplyRepliedPushNotification
{
    public function __invoke(DiscussionReplyReplied $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new DiscussionNotificationTargetService($pdo);

            $post = $service->getPostContext($event->postId);
            if (empty($post)) {
                return;
            }

            $actorName = $service->buildFullName(
                $post['author_first_name'] ?? null,
                $post['author_last_name'] ?? null
            );
            $recipient = $service->getParentAuthorRecipientForPost($event->postId);

            if ($actorName === null || empty($recipient)) {
                return;
            }

            $data = $service->buildPushData(
                'discussion_reply_replied',
                [
                    'discussion_id' => $post['discussion_id'] ?? null,
                    'post_id' => $event->postId,
                    'parent_post_id' => $post['parent_post_id'] ?? null,
                    'cohort_id' => $post['cohort_id'] ?? null,
                    'course_id' => $post['course_id'] ?? null,
                    'program_id' => $post['program_id'] ?? null,
                    'profile_id' => $post['author_id'] ?? null,
                    'event_key' => sprintf('discussion_reply_replied:post:%d', $event->postId),
                ]
            );

            $service->sendPushToRecipient(
                $recipient,
                'New Reply',
                sprintf('%s replied to your reply', $actorName),
                $data
            );
        } catch (\Throwable) {
            return;
        }
    }
}
