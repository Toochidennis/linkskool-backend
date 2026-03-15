<?php

namespace V3\App\Events\Email;

class CohortCoursesEnrolled
{
    public function __construct(
        public int $profileId,
        public array $items
    ) {
    }
}
