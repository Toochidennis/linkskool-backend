<?php

namespace V3\App\Events\Email;

class SubmissionGraded
{
    public function __construct(
        public int $submissionId,
        public int $profileId,
        public int $lessonId,
        public float $assignedScore,
        public ?string $remark,
        public ?string $comment
    ) {
    }
}
