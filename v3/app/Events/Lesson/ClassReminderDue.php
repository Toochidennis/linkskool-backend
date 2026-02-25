<?php

namespace V3\App\Events\Lesson;

class ClassReminderDue
{
    public function __construct(
        public int $lessonId
    ) {
    }
}
