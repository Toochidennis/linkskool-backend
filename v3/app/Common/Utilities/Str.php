<?php

namespace V3\App\Common\Utilities;

class Str
{
    public static function slug(string $text): string
    {
        return strtolower(trim((string) preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }
}
