<?php

namespace V3\App\Common\Events\Registrars\PushNotification;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Email\SubmissionGraded;
use V3\App\Listeners\PushNotification\SendSubmissionGradedPushNotification;

class RegisterPushNotificationEventListeners
{
    public static function register(): void
    {
        EventDispatcher::listen(
            SubmissionGraded::class,
            new SendSubmissionGradedPushNotification()
        );
    }
}
