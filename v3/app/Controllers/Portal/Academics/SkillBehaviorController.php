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
        $data = $this->validate(
            data: $this->post,
            rules: [
                'skill_name' => 'required|string|filled',
                'level_id' => 'required|integer',
                'type' => 'required|integer'
            ]
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

            return $this->respondError(
                'Failed to add skill',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

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

        try {
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
