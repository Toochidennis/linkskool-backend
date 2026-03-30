<?php

namespace V3\App\Events\Discussion;

class DiscussionStarted
{
    public function __construct(
        public int $discussionId
    ) {
    }
}
