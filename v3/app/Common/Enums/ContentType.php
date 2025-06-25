<?php

namespace V3\App\Common\Enums;

enum ContentType: int
{
    case SYLLABUS = 100;
    case TOPIC = 4;
    case MATERIAL = 1;
    case ASSIGNMENT = 6;
    case QUIZ = 7;

    public function label(): string
    {
        return match ($this) {
            self::SYLLABUS => 'syllabus',
            self::TOPIC => 'topic',
            self::MATERIAL => 'material',
            self::ASSIGNMENT => 'assignment',
            self::QUIZ => 'quiz',
        };
    }
}
