<?php

namespace V3\App\Models\Portal;

use V3\App\Utilities\QueryExecutor;

class Attendance extends QueryExecutor
{
    private string $table;

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = 'attendance';
    }

    public function insertAttendance(array $data)
    {
        return parent::insert(table: $this->table, data: $data);
    }

    public function updateAttendance(array $data, array $conditions)
    {
        return parent::update(table: $this->table, data: $data, conditions: $conditions);
    }
}
