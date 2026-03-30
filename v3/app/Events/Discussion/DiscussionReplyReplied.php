<?php

namespace V3\App\Events\Discussion;

class DiscussionReplyReplied
{
    public function __construct(
        public int $postId
    ) {
    }
}
