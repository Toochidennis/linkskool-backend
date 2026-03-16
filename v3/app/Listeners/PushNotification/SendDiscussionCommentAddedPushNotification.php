<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Discussion\DiscussionCommentAdded;
use V3\App\Services\Explore\DiscussionNotificationTargetService;

class SendDiscussionCommentAddedPushNotification
{
    public function __invoke(DiscussionCommentAdded $event): void
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

            if ($actorName === null) {
                return;
            }

            $data = $service->buildPushData(
                'discussion_comment_added',
                [
                    'discussion_id' => $post['discussion_id'] ?? null,
                    'post_id' => $event->postId,
                    'cohort_id' => $post['cohort_id'] ?? null,
                    'course_id' => $post['course_id'] ?? null,
                    'program_id' => $post['program_id'] ?? null,
                    'profile_id' => $post['author_id'] ?? null,
                    'event_key' => sprintf('discussion_comment_added:post:%d', $event->postId),
                ]
            );

            $discussionAuthorRecipient = $service->getDiscussionAuthorRecipientForPost($event->postId);
            if (!empty($discussionAuthorRecipient)) {
                $service->sendPushToRecipient(
                    $discussionAuthorRecipient,
                    'New Discussion Comment',
                    sprintf('%s replied to your discussion', $actorName),
                    $data
                );
            }

            $participantRecipients = $service->getParticipantRecipientsForDiscussion($event->postId);
            foreach ($participantRecipients as $recipient) {
                $service->sendPushToRecipient(
                    $recipient,
                    'New Discussion Comment',
                    sprintf('%s also replied in a discussion you participated in', $actorName),
                    $data
                );
            }
        } catch (\Throwable) {
            return;
        }
    }
}
