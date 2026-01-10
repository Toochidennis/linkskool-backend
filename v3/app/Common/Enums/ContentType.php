<?php

namespace V3\App\Common\Enums;

enum ContentType: int
{
    case MATERIAL = 1;
    case QUIZ = 2;
    case ASSIGNMENT = 3;
    case TOPIC = 4;
    case NEWS = 9;
    case QUESTION = 20;
    case REPLY = 21;
    case COMMENT = 50;
    case SYLLABUS = 100;

    public function label(): string
    {
        return match ($this) {
            self::SYLLABUS => 'syllabus',
            self::TOPIC => 'topic',
            self::MATERIAL => 'material',
            self::ASSIGNMENT => 'assignment',
            self::QUIZ => 'quiz',
            self::COMMENT => 'comment',
            self::NEWS => 'news',
            self::QUESTION => 'question',
            self::REPLY => 'reply',
        };
    }
}
