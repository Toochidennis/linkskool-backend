<?php

namespace V3\App\Common\Events\Registrars\PushNotification;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Discussion\DiscussionCommentAdded;
use V3\App\Events\Discussion\DiscussionPostReplied;
use V3\App\Events\Discussion\DiscussionReplyReplied;
use V3\App\Events\Discussion\DiscussionStarted;
use V3\App\Events\Email\SubmissionGraded;
use V3\App\Events\Lesson\AssignmentDueReminderDue;
use V3\App\Events\Lesson\ClassReminderDue;
use V3\App\Events\Lesson\LessonPublished;
use V3\App\Events\Lesson\LiveClassReminderDue;
use V3\App\Events\News\NewsPosted;
use V3\App\Listeners\PushNotification\SendDiscussionCommentAddedPushNotification;
use V3\App\Listeners\PushNotification\SendDiscussionPostRepliedPushNotification;
use V3\App\Listeners\PushNotification\SendDiscussionReplyRepliedPushNotification;
use V3\App\Listeners\PushNotification\SendDiscussionStartedPushNotification;
use V3\App\Listeners\PushNotification\SendAssignmentDueReminderPushNotification;
use V3\App\Listeners\PushNotification\SendClassReminderPushNotification;
use V3\App\Listeners\PushNotification\SendLessonPublishedPushNotification;
use V3\App\Listeners\PushNotification\SendLiveClassReminderPushNotification;
use V3\App\Listeners\PushNotification\SendNewsPostedPushNotification;
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
            DiscussionStarted::class,
            new SendDiscussionStartedPushNotification()
        );

        EventDispatcher::listen(
            DiscussionCommentAdded::class,
            new SendDiscussionCommentAddedPushNotification()
        );

        EventDispatcher::listen(
            DiscussionPostReplied::class,
            new SendDiscussionPostRepliedPushNotification()
        );

        EventDispatcher::listen(
            DiscussionReplyReplied::class,
            new SendDiscussionReplyRepliedPushNotification()
        );

        EventDispatcher::listen(
            LessonPublished::class,
            new SendLessonPublishedPushNotification()
        );

        EventDispatcher::listen(
            NewsPosted::class,
            new SendNewsPostedPushNotification()
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
