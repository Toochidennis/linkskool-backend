<?php

namespace V3\App\Common\Events;

class EventDispatcher
{
    private static array $listeners = [];

    public static function listen(string $event, callable $listener): void
    {
        self::$listeners[$event][] = $listener;
    }

    public static function dispatch(object $event): void
    {
        $eventName = get_class($event);

        if (!empty(self::$listeners[$eventName])) {
            foreach (self::$listeners[$eventName] as $listener) {
                $listener($event);
            }
        }
    }
}
