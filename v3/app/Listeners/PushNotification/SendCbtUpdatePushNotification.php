<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\CbtUpdate\CbtUpdatePublished;
use V3\App\Services\Explore\CbtUpdateNotificationTargetService;

class SendCbtUpdatePushNotification
{
    public function __invoke(CbtUpdatePublished $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new CbtUpdateNotificationTargetService($pdo);

            $update = $service->getUpdate($event->cbtUpdateId);
            if (
                empty($update)
                || !$service->isPublished($update)
                || !$service->shouldSendPush($update)
            ) {
                return;
            }

            $title = trim((string) ($update['title'] ?? 'CBT Update')) ?: 'CBT Update';
            $body = trim((string) ($update['notification_body'] ?? 'A new CBT update is available.'));

            $data = [
                'type' => 'cbt_update',
                'update_id' => (string) $event->cbtUpdateId,
                'lesson_id' => '',
                'cohort_id' => '',
                'profile_id' => '',
                'course_id' => '',
                'program_id' => '',
                'event_key' => sprintf('cbt_update:push:update:%d', $event->cbtUpdateId),
            ];

            $recipients = $service->getRecipients();
            $service->sendPushInBatches($recipients, $title, $body, $data);
        } catch (\Throwable) {
            return;
        }
    }
}
