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

    public function upsertSkills()
    {
        $data = $this->validate(
            $this->post,
            [
                'skills' => 'required|array|min:1',
                'skills.*.student_id' => 'required|integer',
                'skills.*.student_skills' => 'required|array',
                'skills.*.student_skills.*.skill_id'  => 'required|integer',
                'skills.*.student_skills.*.label'  => 'required|string',
                'skills.*.student_skills.*.value'  => 'required|integer',
                'term'  => 'required|integer',
                'year'  => 'required|integer',
            ]
        );

        try {
            $inserted = $this->service->upsertSkills($data);

            if ($inserted) {
                return $this->respond(
                    [
                        'success' => true,
                        'message' => 'Student skills added successfully.'
                    ],
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
        $data = $this->validate(
            $vars,
            [
                'class_id' => 'required|integer',
                'level_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer'
            ]
        );

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
