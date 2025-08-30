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

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\GradeService;

class GradeController extends BaseController
{
    private GradeService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new GradeService($this->pdo);
    }

    public function addGrades()
    {
        $requiredFields = [
            'grades',
            'grades.*.symbol',
            'grades.*.range',
            'grades.*.remark'
        ];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $inserted = $this->service->add($data['grades']);

            if ($inserted) {
                return $this->respond(
                    ['success' => true, 'message' => 'Grade(s) added successfully.'],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to add grade');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateGrade(array $vars)
    {
        $requiredFields = ['id', 'symbol', 'range', 'remark'];
        $data = $this->validateData($this->post + $vars, $requiredFields);

        try {
            $updated = $this->service->update($data);

            if ($updated) {
                return $this->respond(
                    ['success' => true, 'message' => 'Grade updated successfully.']
                );
            }

            return $this->respondError('Failed to update grade');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getGrades()
    {
        try {
            return $this->respond([
                'success' => true,
                'grades' => $this->service->getGrades()
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function deleteGrade(array $vars)
    {
        $data = $this->validateData($vars, ['id']);

        try {
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
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
