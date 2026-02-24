<?php

namespace V3\App\Common\Events\Registrars;

use V3\App\Common\Events\Registrars\Email\RegisterEmailEventListeners;
use V3\App\Common\Events\Registrars\PushNotification\RegisterPushNotificationEventListeners;

class RegisterEventListeners
{
    public static function register(): void
    {
        RegisterEmailEventListeners::register();
        RegisterPushNotificationEventListeners::register();
    }
}
