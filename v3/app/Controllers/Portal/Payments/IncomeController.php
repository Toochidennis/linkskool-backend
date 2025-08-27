<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Payments\IncomeService;

class IncomeController extends BaseController
{
    private IncomeService $incomeService;

    public function __construct()
    {
        parent::__construct();
        $this->incomeService = new IncomeService($this->pdo);
    }
}
