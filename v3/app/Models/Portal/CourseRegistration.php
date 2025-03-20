<?php

namespace V3\App\Models\Portal;

use V3\App\Utilities\QueryExecutor;

class CourseRegistration extends QueryExecutor
{
    private string $table;

    public const INSERT_REQUIRED = [
        'type' => 'Type of registration is required',
        'courses' => 'courses is required',
        'year' => 'year is required',
        'term' => 'term is required',
        'class_id' => 'class_id is required'
    ];

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

    public function getRecords(array $columns = [], array $conditions = [])
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
