<?php

namespace V3\App\Controllers\Portal\Academics;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\SkillBehaviorService;

class SkillBehaviorController extends BaseController
{
    private SkillBehaviorService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SkillBehaviorService($this->pdo);
    }

    public function store()
    {
        $data = $this->validateData(
            $this->post,
            ['skill_name', 'level_id', 'type']
        );

        try {
            $newId = $this->service->insertSkill($data);

            if ($newId) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Skill added successfully.',
                    'id' => $newId
                ], HttpStatus::CREATED);
            }

            return $this->respondError('Failed to add skill');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        $data = $this->validateData(
            $this->post + $vars,
            ['id', 'skill_name', 'level_id', 'type']
        );

        try {
            $updated = $this->service->insertSkill($data);

            if ($updated) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Skill updated successfully.'
                ]);
            }

            return $this->respondError('Failed to update skill');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function get()
    {
        try {
            return $this->respond([
                'success' => true,
                'response' => $this->service->getSkills()
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
