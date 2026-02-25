<?php

namespace V3\App\Common\Events\Registrars\PushNotification;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Email\SubmissionGraded;
use V3\App\Events\Lesson\AssignmentDueReminderDue;
use V3\App\Events\Lesson\ClassReminderDue;
use V3\App\Events\Lesson\LessonPublished;
use V3\App\Events\Lesson\LiveClassReminderDue;
use V3\App\Listeners\PushNotification\SendAssignmentDueReminderPushNotification;
use V3\App\Listeners\PushNotification\SendClassReminderPushNotification;
use V3\App\Listeners\PushNotification\SendLessonPublishedPushNotification;
use V3\App\Listeners\PushNotification\SendLiveClassReminderPushNotification;
use V3\App\Listeners\PushNotification\SendSubmissionGradedPushNotification;

class RegisterPushNotificationEventListeners
{
    public static function register(): void
    {
        EventDispatcher::listen(
            SubmissionGraded::class,
            new SendSubmissionGradedPushNotification()
        );

        EventDispatcher::listen(
            LessonPublished::class,
            new SendLessonPublishedPushNotification()
        );

        EventDispatcher::listen(
            ClassReminderDue::class,
            new SendClassReminderPushNotification()
        );

        EventDispatcher::listen(
            LiveClassReminderDue::class,
            new SendLiveClassReminderPushNotification()
        );

        EventDispatcher::listen(
            AssignmentDueReminderDue::class,
            new SendAssignmentDueReminderPushNotification()
        );
    }
}
