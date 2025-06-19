<?php

namespace V3\App\Controllers\Portal\Results;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Results\StudentSkillBehaviorService;

class StudentSkillBehaviorController extends BaseController
{
    private StudentSkillBehaviorService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentSkillBehaviorService($this->pdo);
    }

    public function store(array $vars)
    {
        $data = $this->validateData(
            $this->post + $vars,
            ['year', 'student_id', 'term', 'skills']
        );

        try {
            $inserted = $this->service->insertSkills($data);

            if ($inserted) {
                return $this->respond(
                    ['success' => true, 'message' => 'Student skills added successfully.'],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to add student skills');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getStudentsSkillBehavior(array $vars)
    {
        $data = $this->validateData($vars, ['class_id', 'level_id', 'year', 'term']);

        try {
            return $this->respond([
                'success' => true,
                'response' => $this->service->getStudentsSkillBehavior($data)
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
