<?php

namespace V3\App\Models\Portal;

use PDO;
use V3\App\Models\BaseModel;

class RegistrationTracker extends BaseModel
{
    protected string $table = 'registration_tracker';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }


    public function getStaffLastRegNumber()
    {
        return parent::findBy(table: $this->table, columns: ['id, staff_reg_number'], limit: 1);
    }
}
