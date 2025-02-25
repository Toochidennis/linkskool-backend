<?php

namespace V3\App\Models\Portal;

use V3\App\Utilities\QueryExecutor;

class Attendance extends QueryExecutor
{
    private string $table;

    public const INSERT_REQUIRED = [
        'year',
        'term',
        'staff_id',
        'count',
        'class',
        'course',
        'register',
        'date'
    ];

    public const GET_FULL_REQUIRED = ['course', 'class', 'date'];
    public const GET_SUMMARY_REQUIRED = ['course', 'class', 'term', 'year'];

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
    public function getAttendance(array $columns = [], array $conditions = [], int $limit = 0)
    {
        return parent::findBy($this->table, columns: $columns, conditions: $conditions, limit: $limit);
    }
}
