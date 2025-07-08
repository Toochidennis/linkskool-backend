<?php

namespace V3\App\Common\Enums;

enum ContentType: int
{
    case MATERIAL = 1;
    case QUIZ = 2;
    case ASSIGNMENT = 3;
    case TOPIC = 4;
    case SYLLABUS = 100;

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
