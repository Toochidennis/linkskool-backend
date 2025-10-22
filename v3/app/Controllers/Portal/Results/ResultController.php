<?php

namespace V3\App\Controllers\Portal\Results;

use V3\App\Controllers\BaseController;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Results\ResultService;

#[Group('/portal')]
class ResultController extends BaseController
{
    private ResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ResultService($this->pdo);
    }

    #[Route(
        '/result/class-result',
        'PUT',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function updateResult()
    {
        $data = $this->validate(
            $this->post,
            [
                'course_results' => 'required|array|min:1',
                'course_results.*.result_id' => 'required|integer',
                'course_results.*.staff_id' => 'required|integer',
                'course_results.*.total_score' => 'required|numeric',
                'course_results.*.assessments' => 'required|array',
            ]
        );

        $updated = $this->service->updateRecord($data['course_results']);
        if ($updated) {
            return $this->respond([
                'success' => true,
                'message' => 'Course results added successfully.'
            ]);
        }

        return $this->respondError(
            'Failed to add course results.',
            HttpStatus::BAD_REQUEST
        );
    }
}
