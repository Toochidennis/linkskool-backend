<?php

/**
 * This class helps handle grades
 *
 * PHP 8.2+
 *
 * @category Controller
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://linkskool.net
 */

namespace V3\App\Controllers\Portal\Results;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Academics\GradeService;

#[Group('/portal')]
class GradeController extends BaseController
{
    private GradeService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new GradeService($this->pdo);
    }

    #[Route('/grades', 'POST', ['auth', 'role:admin'])]
    public function addGrades()
    {
        $data = $this->validate(
            $this->post,
            [
                'grades' => 'required|array|min:1',
                'grades.*.symbol' => 'required|string|filled',
                'grades.*.range' => 'required|integer',
                'grades.*.remark' => 'required|string|filled',
            ]
        );

        $inserted = $this->service->add($data['grades']);

        if ($inserted) {
            return $this->respond(
                [
                    'success' => true,
                    'message' => 'Grade(s) added successfully.'
                ],
                HttpStatus::CREATED
            );
        }

        return $this->respondError('Failed to add grade', HttpStatus::BAD_REQUEST);
    }

    #[Route('/grades/{id:\d+}', 'PUT', ['auth', 'role:admin'])]
    public function updateGrade(array $vars)
    {
        $data = $this->validate(
            array_merge($this->post, $vars),
            [
                'id' => 'required|integer',
                'symbol' => 'required|string',
                'range' => 'required|integer',
                'remark' => 'required|string',
            ]
        );

        $updated = $this->service->update($data);

        if ($updated) {
            return $this->respond(
                ['success' => true, 'message' => 'Grade updated successfully.']
            );
        }

        return $this->respondError('Failed to update grade', HttpStatus::BAD_REQUEST);
    }

    #[Route('/grades', 'GET', ['auth', 'role:admin'])]
    public function getGrades()
    {
        return $this->respond([
            'success' => true,
            'grades' => $this->service->getGrades()
        ]);
    }

    #[Route('/grades/{id:\d+}', 'DELETE', ['auth', 'role:admin'])]
    public function deleteGrade(array $vars)
    {
        $data = $this->validate($vars, ['id' => 'required|integer']);

        if ($this->service->delete($data['id'])) {
            return $this->respond(
                [
                    'success' => true,
                    'message' => 'Grade deleted successfully'
                ]
            );
        }

        return $this->respondError(
            'Failed to delete to grade',
            HttpStatus::BAD_REQUEST
        );
    }
}
