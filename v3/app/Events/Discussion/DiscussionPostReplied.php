<?php

namespace V3\App\Events\Discussion;

class DiscussionPostReplied
{
    public function __construct(
        public int $postId
    ) {
    }
}
