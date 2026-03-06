<?php

namespace V3\App\Listeners\Email;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\News\NewsPosted;
use V3\App\Services\Explore\NewsNotificationTargetService;

class SendNewsPostedEmail
{
    public function __invoke(NewsPosted $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new NewsNotificationTargetService($pdo);

            $news = $service->getNews($event->newsId);
            if (empty($news) || ($news['status'] ?? null) !== 'published') {
                return;
            }

            $subject = 'News Update: ' . htmlspecialchars((string) ($news['title'] ?? 'New post'), ENT_QUOTES, 'UTF-8');

            $recipients = $service->getRecipients();
            $service->sendEmailInBatches($recipients, $news, $subject);
        } catch (\Throwable) {
            return;
        }
    }
}
