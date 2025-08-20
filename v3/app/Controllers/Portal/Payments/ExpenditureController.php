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
                'class_id' => 'required|integer',
                'level_id' => 'required|integer',
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
                'class_id' => 'required|integer',
                'level_id' => 'required|integer',
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
}
