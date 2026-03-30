<?php

namespace V3\App\Controllers\Portal\Results;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Results\StudentSkillBehaviorService;

#[Group('/portal')]
class StudentSkillBehaviorController extends BaseController
{
    private StudentSkillBehaviorService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentSkillBehaviorService($this->pdo);
    }

    #[Route(
        '/students/skill-behavior',
        'POST',
        ['auth', 'role:admin', 'role:staff']
    )]
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
                'type' => 'nullable|integer|in:0,1'
            ]
        );

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

        return $this->respondError(
            'Failed to add student skills',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/classes/{class_id:\d+}/skill-behavior',
        'GET',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function getStudentsSkillsBehaviors(array $vars)
    {
        $data = $this->validate(
            $vars,
            [
                'class_id' => 'required|integer',
                'level_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer',
                'type' => 'nullable|integer|in:0,1'
            ]
        );

        return $this->respond([
            'success' => true,
            'response' => $this->service->getStudentsSkillsAndBehaviors($data)
        ]);
    }
}
