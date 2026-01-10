<?php

namespace V3\App\Services\Portal\Academics;

use V3\App\Models\Portal\Academics\SkillBehavior;

class SkillBehaviorService
{
    private SkillBehavior $skillBehavior;

    /**
     * SkillBehaviorService constructor.
     *
     * @param \PDO $pdo PDO instance for database interaction.
     */
    public function __construct(\PDO $pdo)
    {
        $this->skillBehavior = new SkillBehavior($pdo);
    }

    public function insertSkill(array $data): bool|int
    {
        $payload = [
            'skill_name' => $data['skill_name'],
            'level' => $data['level_id'],
            'type' => $data['type']
        ];

        return $this->skillBehavior->insert($payload);
    }

    public function updateSkill(array $data): bool
    {
        $payload = [
            'skill_name' => $data['skill_name'],
            'level' => $data['level_id'],
            'type' => $data['type']
        ];

        return $this->skillBehavior
            ->where('id', '=', $data['id'])
            ->update($payload);
    }

    /**
     * Retrieves all skill behavior records.
     *
     * @return array List of all skill behaviors from the database.
     */
    public function getSkills(): array
    {
        return $this->skillBehavior->get();
    }

    public function deleteSkill(int $id): bool
    {
        return $this->skillBehavior
            ->where('id', $id)
            ->delete();
    }
}
