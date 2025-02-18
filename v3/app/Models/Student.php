<?php

namespace V3\App\Models;

use PDO;
use V3\App\Utilities\QueryExecutor;

class Student extends QueryExecutor {
    private $table;

    public function __construct(PDO $pdo){
        parent::__construct($pdo);
        $this->table = 'students_record';
    }

    public function insertStudent(array $data){
        return parent::insert($this->table, $data);
    }

    public function updateStudent(array $data, array $conditions){
        return parent::update($this->table, $data, $conditions);
    }
}