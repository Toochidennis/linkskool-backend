<?php

namespace V3\App\Events\CbtUpdate;

class CbtUpdatePublished
{
    public function __construct(
        public int $cbtUpdateId
    ) {
    }
}
