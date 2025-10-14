<?php

namespace V3\App\Common\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET',
        public array $middleware = [],
        public ?string $type = null,
    ) {
    }
}
