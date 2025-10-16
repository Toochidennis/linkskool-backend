<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Video extends BaseModel
{
    protected string $table = "videosTable";

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
