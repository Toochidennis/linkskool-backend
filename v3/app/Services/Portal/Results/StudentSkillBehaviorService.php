<?php

namespace V3\App\Services\Portal\Results;

use PDO;
use V3\App\Models\Portal\Academics\SkillBehavior;
use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\Results\StudentSkillBehavior;

class StudentSkillBehaviorService
{
    private StudentSkillBehavior $studentSkillBehavior;
    private Student $student;
    private SkillBehavior $skillBehavior;

    public function __construct(PDO $pdo)
    {
        $this->studentSkillBehavior = new StudentSkillBehavior(pdo: $pdo);
        $this->student = new Student(pdo: $pdo);
        $this->skillBehavior = new SkillBehavior(pdo: $pdo);
    }

    /**
     * Inserts skill records for multiple students for a given year and term.
     *
     * @param array $data Includes 'year', 'term', and 'skills' (array of student_id and skill data).
     * @return bool True if all inserts succeed, false if any fail.
     */
    public function upsertSkills(array $data): bool
    {
        $count = 0;

        foreach ($data['skills'] as $skill) {
            $payload = [
                'year'  => $data['year'],
                'term'  => $data['term'],
                'reg_no' => $skill['student_id'],
                'skill' => json_encode($skill['student_skills']),
                'type' => $data['type'] ?? 0
            ];

            // Check if record exists for this student in the same year/term
            $existing = $this->studentSkillBehavior
                ->select(['id'])
                ->where('reg_no', $skill['student_id'])
                ->where('year', $data['year'])
                ->where('term', $data['term'])
                ->where('type', $data['type'] ?? 0)
                ->first();

            if ($existing) {
                $updated = $this->studentSkillBehavior
                    ->where('id', $existing['id'])
                    ->update($payload);
                if ($updated) {
                    $count++;
                }
            } else {
                $inserted = $this->studentSkillBehavior->insert($payload);
                if ($inserted) {
                    $count++;
                }
            }
        }

        return $count === \count($data['skills']);
    }

    private function getStudents(array $filters)
    {
        return $this->student
            ->select(columns: [
                'students_record.id as student_id',
                "concat(surname,' ', first_name,' ', middle) AS student_name"
            ])
            ->join(
                table: 'result_table',
                condition: function ($join) use ($filters) {
                    $join->on('students_record.id', '=', 'result_table.reg_no')
                        ->on('result_table.term', '=', $filters['term'])
                        ->on('result_table.year', '=', $filters['year']);
                },
                type: 'INNER'
            )
            ->where('students_record.student_class', '=', $filters['class_id'])
            ->groupBy(['students_record.id', 'student_name'])
            ->get();
    }

    private function getSkillsAndBehaviors(int $levelId, ?int $type = 0): array
    {
        $rows = $this->skillBehavior
            ->select(columns: ['id', 'skill_name', 'type'])
            ->where('level', '=', $levelId)
            ->where('type', '=', $type)
            ->get();

        if (empty($rows)) {
            $rows = $this->skillBehavior
                ->select(columns: ['id', 'skill_name', 'type'])
                ->where('level', 'IS', null)
                ->where('type', '=', $type)
                ->get();
        }

        return $rows;
    }

    /**
     * Retrieves skill behavior records for students in a given class, term, and year.
     *
     * @param array $filters Must include 'year', 'term', and 'level_id'.
     * @return array Contains 'skills' (defined skill behaviors) and 'students' (with attached skill data).
     */
    public function getStudentsSkillsAndBehaviors(array $filters)
    {
        $students = $this->getStudents($filters);
        $skills = $this->getSkillsAndBehaviors($filters['level_id'], $filters['type'] ?? 0);

        foreach ($students as &$student) {
            $studentSkills = $this->studentSkillBehavior
                ->select(columns: ['skill'])
                ->where('year', '=', $filters['year'])
                ->where('term', '=', $filters['term'])
                ->where('reg_no', '=', $student['student_id'])
                ->where('type', '=', $filters['type'] ?? 0)
                ->get();

            if (!empty($studentSkills)) {
                $rawSkillData = json_decode($studentSkills[0]['skill'], true);

                // Detect if it's the old format (associative with skill_id as key)
                if (!isset($rawSkillData[0]) && \is_array($rawSkillData)) {
                    // Convert to new format
                    $converted = [];
                    foreach ($rawSkillData as $skillId => $data) {
                        $converted[] = [
                            'skill_id' => (int) $skillId,
                            'value' => $data['value'],
                            'label' => $data['label']
                        ];
                    }
                    $student['student_skills'] = $converted;
                } else {
                    // Already in new format
                    $student['student_skills'] = $rawSkillData;
                }
            } else {
                $student['student_skills'] = null;
            }
        }

        return [
            'skills' => $skills,
            'students' => $students
        ];
    }
}
