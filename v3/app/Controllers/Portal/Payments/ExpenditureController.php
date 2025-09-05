<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Common\Utilities\HttpStatus;
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

    public function store()
    {
        $cleanedData = $this->validate(
            $this->post,
            rules: [
                'description' => 'required|string',
                'customer_id' => 'required|integer',
                'customer_reference' => 'required|string',
                'customer_name' => 'required|string',
                'amount' => 'required|numeric',
                'date' => 'required|date',
                'account_number' => 'required|string',
                'account_name' => 'required|string',
                'year' => 'required|integer',
                'term' => 'required|integer|in:1,2,3'
            ]
        );

        try {
            $newId = $this->expenditureService->addExpenditure($cleanedData);

            if ($newId) {
                $this->respond([
                    'success' => true,
                    'message' => 'Expenditure added successfully.'
                ], HttpStatus::CREATED);
            }
            $this->respondError('Failed to add expenditure.', HttpStatus::BAD_REQUEST);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        $cleanedData = $this->validate(
            [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'description' => 'required|string',
                'customer_id' => 'required|integer',
                'customer_reference' => 'required|string',
                'customer_name' => 'required|string',
                'amount' => 'required|numeric',
                'account_number' => 'required|string',
                'account_name' => 'required|string',
                'year' => 'required|integer',
                'term' => 'required|integer'
            ]
        );

        try {
            $updated = $this->expenditureService->updateExpenditure($cleanedData);

            if ($updated) {
                $this->respond([
                    'success' => true,
                    'message' => 'Expenditure updated successfully.'
                ]);
            }

            $this->respondError('Failed to update expenditure.', HttpStatus::BAD_REQUEST);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function generateReport()
    {
        $filteredVars = $this->validate(
            data: $this->post,
            rules: [
                'report_type' => 'required|string|in:termly,session,monthly,custom',
                'custom_type' => 'nullable|string|in:range,today,yesterday,this_week,last_week,last_30_days,this_month,last_month',
                'start_date' => 'required_if:custom_type,range|date',
                'end_date' => 'required_if:custom_type,range|date|after_or_equal:start_date',
                'group_by' => 'nullable|string|in:vendor,month',
                'filters.terms' => 'nullable|array',
                'filters.terms.*' => 'integer|in:1,2,3',
                'filters.sessions' => 'nullable|array',
                'filters.sessions.*' => 'integer',
                'filters.vendors' => 'nullable|array',
                'filters.vendors.*' => 'integer',
            ]
        );

        try {
            $reports = $this->expenditureService->report($filteredVars);

            return $this->respond([
                'success' => true,
                'data' => $reports
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
