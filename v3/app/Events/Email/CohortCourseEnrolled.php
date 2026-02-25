<?php

namespace V3\App\Events\Email;

class CohortCourseEnrolled
{
    public function __construct(
        public int $profileId,
        public int $programId,
        public int $courseId,
        public int $cohortId,
        public ?string $courseName = null,
        public ?string $cohortName = null
    ) {
    }
}
