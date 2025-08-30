<?php

namespace V3\App\Models;

use PDO;
use V3\App\Database\Query\QueryBuilder;

abstract class BaseModel extends QueryBuilder
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }
}
