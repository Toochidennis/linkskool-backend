<?php

namespace V3\App\Models\Portal;

use V3\App\Utilities\QueryExecutor;

class SchoolSettings extends QueryExecutor
{
    private $table;

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = 'school_settings_table';
    }

    public function getStudentPrefix()
    {
        return parent::findBy(table: $this->table, columns: ['student_prefix'], limit: 1);
    }

    public function getStaffPrefix()
    {
        return parent::findBy(table: $this->table, columns: ['staff_prefix'], limit: 1);
    }
}
