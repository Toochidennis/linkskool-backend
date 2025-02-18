<?php

namespace V3\App\Models\Portal;

use V3\App\Utilities\QueryExecutor;

class RegistrationTracker extends QueryExecutor
{
    private $table;

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = 'registration_tracker';
    }

    public function insertRegNumber(array $data)
    {
        return parent::insert(table:$this->table, data:$data);
    }

    public function updateRegNumber(array $data, array $conditions)
    {
        return parent::update(table:$this->table, data:$data, conditions:$conditions);
    }

    public function getStudentLastRegNumber()
    {
        return parent::findBy(table: $this->table, columns: ['id, student_reg_number'], limit: 1);
    }

    public function getStaffLastRegNumber()
    {
        return parent::findBy(table: $this->table, columns: ['id, staff_reg_number'], limit: 1);
    }
}
