<?php

namespace V3\App\Events\Lesson;

class LessonPublished
{
    public function __construct(
        public int $lessonId
    ) {
    }
}
