<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class VideoLibrary extends BaseModel
{
    protected string $table = 'video_libraries';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
