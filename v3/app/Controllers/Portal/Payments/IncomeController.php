<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Payments\IncomeService;

#[Group('/portal/payments')]
class IncomeController extends BaseController
{
    private IncomeService $incomeService;

    public function __construct()
    {
        parent::__construct();
        $this->incomeService = new IncomeService($this->pdo);
    }

    #[Route(
        '/income/report/generate',
        'GET',
        ['auth', 'role:admin']
    )]
    public function generateReport()
    {
        $filteredVars = $this->validate(
            data: $this->post,
            rules: [
                'report_type' => 'required|string|in:termly,session,monthly,custom',
                'custom_type' => 'nullable|string|in:range,today,yesterday,this_week,last_week,last_30_days,this_month,last_month',
                'start_date' => 'required_if:custom_type,range|date',
                'end_date' => 'required_if:custom_type,range|date|after_or_equal:start_date',
                'group_by' => 'nullable|string|in:level,class,month',
                'filters.terms' => 'nullable|array',
                'filters.terms.*' => 'integer|in:1,2,3',
                'filters.sessions' => 'nullable|array',
                'filters.sessions.*' => 'integer',
                'filters.levels' => 'nullable|array',
                'filters.levels.*' => 'integer',
                'filters.classes' => 'nullable|array',
                'filters.classes.*' => 'integer',
            ]
        );

        $reports = $this->incomeService->report($filteredVars);

        return $this->respond([
            'success' => true,
            'data' => $reports
        ]);
    }
}
