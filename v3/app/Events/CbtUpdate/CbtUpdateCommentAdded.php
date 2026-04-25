<?php

namespace V3\App\Events\CbtUpdate;

class CbtUpdateCommentAdded
{
    public function __construct(
        public int $commentId
    ) {
    }
}
