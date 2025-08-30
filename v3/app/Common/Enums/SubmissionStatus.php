<?php

namespace V3\App\Common\Enums;

enum SubmissionStatus: int
{
    case UNMARKED = 1;
    case MARKED = 0;
    public function label(): string
    {
        return match ($this) {
            self::UNMARKED => 'unmarked',
            self::MARKED => 'marked'
        };
    }
}
