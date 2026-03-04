<?php

namespace V3\App\Events\News;

class NewsPosted
{
    public function __construct(
        public int $newsId
    ) {
    }
}
