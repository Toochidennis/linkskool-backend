<?php

namespace V3\App\Common\Enums;

enum QuestionType: string
{
    case MULTIPLE_CHOICE = 'qo';
    case SHORT_ANSWER = 'qs';
    case SECTION = 'section';

    public function label(): string
    {
        return match ($this) {
            self::MULTIPLE_CHOICE => 'multiple_choice',
            self::SHORT_ANSWER => 'short_answer',
            self::SECTION => 'section'
        };
    }
}
