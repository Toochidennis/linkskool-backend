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

    public function insertSkills($data)
    {
        $payload = [
            'year' => $data['year'],
            'term' => $data['term'],
            'reg_no' => $data['student_id'],
            'skill' => json_encode($data['skills'])
        ];

        return $this->studentSkillBehavior->insert($payload);
    }

    public function getStudents(array $filters)
    {
        return $this->student
            ->select(columns: [
                'students_record.id',
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

    public function getSkillBehavior($levelId)
    {
        $skills = $this->skillBehavior
            ->select(columns: ['id', 'skill_name'])
            ->where('level', '=', $levelId)
            ->where('type', '=', 0)
            ->get();

        if (empty($skills)) {
            $skills = $this->skillBehavior
                ->select(columns: ['id', 'skill_name'])
                ->where('level', 'IS', null)
                ->where('type', '=', 0)
                ->get();
        }

        return $skills;
    }

    public function getStudentsSkillBehavior(array $filters)
    {
        $students = $this->getStudents($filters);
        $skills = $this->getSkillBehavior($filters['level_id']);

        foreach ($students as &$student) {
            $studentSkills = $this->studentSkillBehavior
                ->select(columns: ['skill'])
                ->where('year', '=', $filters['year'])
                ->where('term', '=', $filters['term'])
                ->where('reg_no', '=', $student['id'])
                ->get();

            $student['student_skills'] = (!empty($studentSkills)) ?
                json_decode($studentSkills[0]['skill']) : null;
        }

        return [
            'skills' => $skills,
            'students' => $students
        ];
    }
}
