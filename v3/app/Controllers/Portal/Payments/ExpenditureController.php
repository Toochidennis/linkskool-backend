<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Payments\ExpenditureService;

class ExpenditureController extends BaseController
{
    private ExpenditureService $expenditureService;

    public function __construct()
    {
        parent::__construct();
        $this->expenditureService = new ExpenditureService($this->pdo);
    }

    
}
