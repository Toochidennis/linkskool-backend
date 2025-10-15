<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Payments\NextTermFeeService;

#[Group('/portal/payments')]
class NextTermFeeController extends BaseController
{
    private NextTermFeeService $nextTermFeeService;

    public function __construct()
    {
        parent::__construct();
        $this->nextTermFeeService = new NextTermFeeService($this->pdo);
    }


    #[Route(
        '/invoices',
        'POST',
        ['auth', 'role:admin']
    )]
    public function upsert()
    {
        $cleanedData = $this->validate(
            data: $this->post,
            rules: [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'level_id' => 'required|integer',
                'fees' => 'required|array|min:1',
                'fees.*.fee_id' => 'required|integer',
                'fees.*.fee_name' => 'required|string|filled',
                'fees.*.amount' => 'required|numeric',
            ]
        );

        $newId = $this->nextTermFeeService->upsertFeeAmount($cleanedData);

        if ($newId > 0) {
            $this->respond([
                'success' => true,
                'message' => 'Next term fee added successfully'
            ]);
        }
        $this->respondError(
            'Failed to add fee. Are there students in this class?',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/invoices',
        'GET',
        ['auth', 'role:admin']
    )]
    public function get(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'level_id' => 'required|integer',
            ]
        );

        $this->respond([
            'success' => true,
            'response' => $this->nextTermFeeService->termFeesByLevel($cleanedData),
        ]);
    }
}
