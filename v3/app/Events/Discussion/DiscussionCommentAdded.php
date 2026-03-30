<?php

namespace V3\App\Events\Discussion;

class DiscussionCommentAdded
{
    public function __construct(
        public int $postId
    ) {
    }
}
