<?php

namespace V3\App\Models\Portal;

use V3\App\Utilities\QueryExecutor;

class Grade extends QueryExecutor{
    private string $table;

    public function __construct(\PDO $pdo){
        parent::__construct($pdo);
        $this->table = 'score_grade_table';
    }

    public function insertGrade(array $data)
    {
        return parent::insert(table: $this->table, data: $data);
    }

    public function updateGrade(array $data, array $conditions)
    {
        return parent::update(table: $this->table, data: $data, conditions: $conditions);
    }

    /**
     * Retrieves grades records from the database.
     *
     * @param array $columns    (Optional) An array of column names to retrieve.
     *                          If empty, all columns will be retrieved.
     * @param array $conditions (Optional) An associative array specifying the conditions
     *                          to filter the results. The keys are column names, and
     *                          the values are the values those columns must match.
     * @param int   $limit      (Optional) The maximum number of records to retrieve.
     *                          Defaults to 0, which means no limit.
     *
     * @return array|false Returns an array of matching records, or false on failure.
     */
    public function getGrades(array $columns = [], array $conditions = [], int $limit = 0)
    {
        return parent::findBy($this->table, columns: $columns, conditions: $conditions, limit: $limit);
    }
}