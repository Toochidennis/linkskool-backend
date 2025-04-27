<?php

namespace V3\App\Models\Portal;

use PDO;
use V3\App\Models\BaseModel;

class Student extends BaseModel
{
    protected string $table = 'students_record';

    /**
     * Constructor for the Student model.
     *
     * @param PDO $pdo A PDO instance connected to the desired database.
     *                 This connection will be passed to the parent BaseModel
     *                 to facilitate database interactions.
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
