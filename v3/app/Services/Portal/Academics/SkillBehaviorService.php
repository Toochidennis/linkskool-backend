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

    /**
     * Inserts a new skill behavior record.
     *
     * Converts 'level_id' to 'level', and removes it if level_id is 0.
     *
     * @param array $data Associative array containing skill behavior data.
     * @return bool True if insert succeeds, false otherwise.
     */
    public function insertSkill(array $data)
    {
        $data['level'] = (int)$data['level_id'] !== 0 ? $data['level_id'] : null;
        unset($data['level_id']);

        return $this->skillBehavior->insert($data);
    }

    /**
     * Updates an existing skill behavior record.
     *
     * Requires 'id' to identify the record. Converts 'level_id' to 'level'.
     *
     * @param array $data Associative array with updated data, including 'id'.
     * @return bool True if update succeeds, false otherwise.
     */
    public function updateSkill(array $data): bool
    {
        $data['level'] = (int)$data['level_id'] !== 0 ? $data['level_id'] : null;
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
