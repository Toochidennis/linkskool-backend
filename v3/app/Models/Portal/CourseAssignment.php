<?php

namespace V3\App\Models\Portal;

use V3\App\Utilities\QueryExecutor;

class CourseAssignment extends QueryExecutor
{
    private string $table;

    public const INSERT_REQUIRED = [
        'year',
        'term',
        'ref_no',
        'class',
        'course'
    ];

    public const GET_REQUIRED = ['year', 'term', 'ref_no'];

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = 'staff_course_table';
    }

    public function assignCourse(array $data)
    {
        return parent::insert(table: $this->table, data: $data);
    }

    public function getAssignedCourses(array $columns = [], array $joins, array $conditions = [])
    {
        return parent::queryWithJoins($this->table, columns: $columns, joins: $joins, conditions: $conditions);
    }

    public function deleteAssignedCourses(array $conditions, string $notInColumn = '', array $notInValues = [])
    {
        return parent::deleteRecords(
            table: $this->table,
            conditions: $conditions,
            notInColumn: $notInColumn,
            notInValues: $notInValues
        );
    }
}
