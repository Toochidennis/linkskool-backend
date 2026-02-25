<?php

namespace V3\App\Events\Lesson;

class LiveClassReminderDue
{
    public function __construct(
        public int $lessonId
    ) {
    }
}
