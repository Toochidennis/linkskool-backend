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

    public function isCourseIsRegistered(array $conditions)
    {
        return parent::findBy(table: $this->table, conditions: $conditions);
    }

    public function insertResult(array $data, array $conditions)
    {
        return parent::update(table: $this->table, data: $data, conditions: $conditions);
    }

    public function unregisterCourse(array $conditions): bool
    {
        return parent::delete($this->table, conditions: $conditions);
    }
}
