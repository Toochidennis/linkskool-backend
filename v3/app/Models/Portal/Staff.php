<?php

namespace V3\App\Models\Portal;

use PDO;
use V3\App\Utilities\QueryExecutor;

class Staff extends QueryExecutor
{

    private string $table;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = 'staff_record';
    }

    public function insertStaff(array $data)
    {
        return parent::insert($this->table, $data);
    }

    public function updateStaff(array $data, array $conditions)
    {
        return parent::update($this->table, $data, $conditions);
    }

    public function getStaff(array $columns = [], array $conditions = [], int $limit = 0)
    {
        return parent::findBy($this->table, columns: $columns, conditions: $conditions, limit: $limit);
    }
}
