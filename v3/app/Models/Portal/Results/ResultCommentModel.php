<?php

namespace V3\App\Models\Portal\Results;

use PDO;
use V3\App\Models\BaseModel;

class ResultCommentModel extends BaseModel
{
    protected string $table = 'comment_table';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
