<?php

namespace V3\App\Models\Portal;

use V3\App\Utilities\QueryExecutor;

class Result extends QueryExecutor
{
    private string $table;

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = 'result_table';
    }

    public function registerCourse(array $data)
    {
        return parent::insert(table: $this->table, data: $data);
    }

    public function countCourseRegistrations(array $conditions)
    {
        return parent::countByCondition($this->table, $conditions);
    }

    public function insertResult(array $data, array $conditions)
    {
        return parent::update(table: $this->table, data: $data, conditions: $conditions);
    }

    public function getRegisteredCourses(array $columns, array $conditions)
    {
        return parent::findBy(table: $this->table, columns: $columns, conditions: $conditions);
    }

    public function deleteRegisteredCourses(array $conditions, string $notInColumn = '', array $notInValues = [])
    {
        return parent::deleteRecords(
            table: $this->table,
            conditions: $conditions,
            notInColumn: $notInColumn,
            notInValues: $notInValues
        );
    }
}
