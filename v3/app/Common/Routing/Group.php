<?php

namespace V3\App\Common\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Group
{
    public function __construct(public string $prefix)
    {
    }
}
