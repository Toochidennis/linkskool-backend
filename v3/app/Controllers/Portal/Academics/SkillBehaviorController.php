<?php

namespace V3\App\Controllers\Portal\Academics;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Academics\SkillBehaviorService;

#[Group('/portal')]
class SkillBehaviorController extends BaseController
{
    private SkillBehaviorService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SkillBehaviorService($this->pdo);
    }

    #[Route('/skill-behavior', 'POST', ['auth', 'role:admin'])]
    public function store()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'skill_name' => 'required|string|filled',
                'level_id' => 'required|integer',
                'type' => 'required|integer'
            ]
        );

        $newId = $this->service->insertSkill($data);

        if ($newId) {
            return $this->respond([
                'success' => true,
                'message' => 'Skill added successfully.',
                'id' => $newId
            ], HttpStatus::CREATED);
        }

        return $this->respondError(
            'Failed to add skill',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('/skill-behavior/{id:\d+}', 'PUT', ['auth', 'role:admin'])]
    public function update(array $vars)
    {
        $data = $this->validate(
            array_merge($this->post, $vars),
            rules: [
                'id' => 'required|integer',
                'skill_name' => 'required|string',
                'level_id' => 'required|integer',
                'type' => 'required|integer'
            ]
        );

        $updated = $this->service->insertSkill($data);

        if ($updated) {
            return $this->respond([
                'success' => true,
                'message' => 'Skill updated successfully.'
            ], HttpStatus::CREATED);
        }

        return $this->respondError(
            'Failed to update skill',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('/skill-behavior', 'GET', ['auth', 'role:admin'])]
    public function get()
    {
        return $this->respond([
            'success' => true,
            'response' => $this->service->getSkills()
        ]);
    }

    #[Route('/skill-behavior{id\d+}', 'DELETE', ['auth', 'role:admin'])]
    public function delete(array $vars)
    {
        $data = $this->validate($vars, ['id' => 'required|integer']);

        $delete = $this->service->deleteSkill($data['id']);

        if ($delete) {
            return $this->respond([
                'success' => true,
                'message' => 'Skill deleted successfully.'
            ]);
        }

        return $this->respondError(
            'Failed to delete skill',
            HttpStatus::BAD_REQUEST
        );
    }
}
