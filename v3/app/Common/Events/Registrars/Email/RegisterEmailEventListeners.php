<?php

namespace V3\App\Common\Events\Registrars\Email;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Email\SubmissionGraded;
use V3\App\Listeners\Email\SendSubmissionGradedEmail;

class RegisterEmailEventListeners
{
    public static function register(): void
    {
        EventDispatcher::listen(
            SubmissionGraded::class,
            new SendSubmissionGradedEmail()
        );
    }
}
