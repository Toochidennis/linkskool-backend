<?php

namespace V3\App\Common\Events\Registrars\Email;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Auth\UserRegisteredFirstTime;
use V3\App\Events\Email\CohortCourseEnrolled;
use V3\App\Events\Email\CohortCoursesEnrolled;
use V3\App\Events\Email\SubmissionGraded;
use V3\App\Events\Lesson\AssignmentDueReminderDue;
use V3\App\Events\Lesson\ClassReminderDue;
use V3\App\Events\Lesson\LessonPublished;
use V3\App\Events\Lesson\LiveClassReminderDue;
use V3\App\Events\News\NewsPosted;
use V3\App\Listeners\Email\SendAssignmentDueReminderEmail;
use V3\App\Listeners\Email\SendClassReminderEmail;
use V3\App\Listeners\Email\SendCohortCourseEnrolledEmail;
use V3\App\Listeners\Email\SendCohortCoursesEnrolledEmail;
use V3\App\Listeners\Email\SendLessonPublishedEmail;
use V3\App\Listeners\Email\SendNewsPostedEmail;
use V3\App\Listeners\Email\SendSubmissionGradedEmail;
use V3\App\Listeners\Email\SendLiveClassReminderEmail;
use V3\App\Listeners\Email\SendWelcomeEmail;

class RegisterEmailEventListeners
{
    public static function register(): void
    {
        EventDispatcher::listen(
            SubmissionGraded::class,
            new SendSubmissionGradedEmail()
        );

        EventDispatcher::listen(
            CohortCourseEnrolled::class,
            new SendCohortCourseEnrolledEmail()
        );

        EventDispatcher::listen(
            CohortCoursesEnrolled::class,
            new SendCohortCoursesEnrolledEmail()
        );

        EventDispatcher::listen(
            LessonPublished::class,
            new SendLessonPublishedEmail()
        );

        EventDispatcher::listen(
            NewsPosted::class,
            new SendNewsPostedEmail()
        );

        EventDispatcher::listen(
            ClassReminderDue::class,
            new SendClassReminderEmail()
        );

        EventDispatcher::listen(
            LiveClassReminderDue::class,
            new SendLiveClassReminderEmail()
        );

        EventDispatcher::listen(
            AssignmentDueReminderDue::class,
            new SendAssignmentDueReminderEmail()
        );

        EventDispatcher::listen(
            UserRegisteredFirstTime::class,
            new SendWelcomeEmail()
        );
    }
}
