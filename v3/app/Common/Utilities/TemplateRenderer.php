<?php

namespace V3\App\Common\Utilities;

class TemplateRenderer
{
    public static function render(string $path, array $data = []): string
    {
        ob_start();
        include $path;
        return ob_get_clean();
    }
}
