<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\CbtUpdate\CbtUpdateCommentAdded;
use V3\App\Services\Explore\CbtUpdateNotificationTargetService;

class SendCbtUpdateCommentAddedPushNotification
{
    public function __invoke(CbtUpdateCommentAdded $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new CbtUpdateNotificationTargetService($pdo);

            $comment = $service->getCommentContext($event->commentId);
            if (empty($comment)) {
                return;
            }

            $actorName = trim((string) ($comment['user_name'] ?? ''));
            if ($actorName === '') {
                $actorName = 'Someone';
            }

            $recipient = $service->getUpdateAuthorRecipientForComment($event->commentId);
            if (empty($recipient)) {
                return;
            }

            $data = $service->buildPushData(
                'cbt_update_comment',
                [
                    'cbt_update_id' => $comment['update_id'] ?? null,
                    'comment_id' => $event->commentId,
                    'profile_id' => '',
                    'lesson_id' => '',
                    'cohort_id' => '',
                    'course_id' => '',
                    'program_id' => '',
                    'event_key' => sprintf('cbt_update_comment:comment:%d', $event->commentId),
                ]
            );

            $title = 'New Comment';
            $body = sprintf('%s commented on your update', $actorName);

            $service->sendPushToRecipient($recipient, $title, $body, $data);
        } catch (\Throwable) {
            return;
        }
    }
}
