<?php

namespace V3\App\Common\Enums;

enum SchoolType: int
{
    case NURSERY = 1;
    case PRIMARY = 2;
    case SECONDARY = 3;

    public function label(): string
    {
        return match ($this) {
            self::NURSERY => 'nursery',
            self::PRIMARY => 'primary',
            self::SECONDARY => 'secondary',
        };
    }
}
