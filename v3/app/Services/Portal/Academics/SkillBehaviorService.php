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
        $data['level'] = $data['level_id'];
        unset($data['level_id']);

        return $this->skillBehavior->insert($data);
    }

    public function updateSkill(array $data): bool
    {
        $data['level'] = $data['level_id'];
        unset($data['level_id']);

        $id = $data['id'];
        unset($data['id']);

        return $this->skillBehavior
            ->where('id', '=', $id)
            ->update($data);
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
}
