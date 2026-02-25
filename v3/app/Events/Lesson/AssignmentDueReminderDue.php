<?php

namespace V3\App\Events\Lesson;

class AssignmentDueReminderDue
{
    public function __construct(
        public int $lessonId
    ) {
    }
}
