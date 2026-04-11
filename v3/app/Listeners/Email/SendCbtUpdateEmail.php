<?php

namespace V3\App\Listeners\Email;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\CbtUpdate\CbtUpdatePublished;
use V3\App\Services\Explore\CbtUpdateNotificationTargetService;

class SendCbtUpdateEmail
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
                || !$service->shouldSendEmail($update)
            ) {
                return;
            }

            $subject = trim((string) ($update['title'] ?? 'New update')) ?: 'New update';
            $eventKey = \sprintf('cbt_update:email:update:%d', $event->cbtUpdateId);

            $recipients = $service->getRecipients();
            $service->sendEmailInBatches($recipients, $update, $subject, $eventKey);
        } catch (\Throwable) {
            return;
        }
    }
}
