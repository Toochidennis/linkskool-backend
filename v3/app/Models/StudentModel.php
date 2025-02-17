<?php

namespace V3\App\Models;

use V3\App\Utilities\QueryExecutor;

class StudentModel extends QueryExecutor {
    private $table;

    public function __construct(\PDO $pdo){
        parent::__construct($pdo);
        $this->table = 'students_record';
    }

    public function insertStudent(array $data){
        return parent::insert($this->table, $data);
    }

}