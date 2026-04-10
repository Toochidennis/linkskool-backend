<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\News\NewsPosted;
use V3\App\Services\Explore\NewsNotificationTargetService;

class SendNewsPostedPushNotification
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

            $newsTitle = trim((string) ($news['title'] ?? 'New post'));
            $title = 'News Update';
            $body = $newsTitle !== '' ? \sprintf('%s is now available.', $newsTitle)
                : 'A new news post is now available.';

            $data = [
                'type' => 'news_posted',
                'lesson_id' => '',
                'cohort_id' => '',
                'profile_id' => '',
                'course_id' => '',
                'program_id' => '',
                'news_id' => $event->newsId,
            ];

            $recipients = $service->getRecipients();
            $service->sendPushInBatches($recipients, $title, $body, $data);
        } catch (\Throwable) {
            return;
        }
    }
}
