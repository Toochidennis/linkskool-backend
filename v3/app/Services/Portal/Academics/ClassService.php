<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Models\Portal\Academics\ClassModel;

class ClassService
{
    private ClassModel $classModel;

    public function __construct(PDO $pdo)
    {
        $this->classModel = new ClassModel($pdo);
    }

    public function insertClass(array $data)
    {
        $form_teacher_ids = implode(',', $data['form_teacher_ids'] ?? []);
        $payload = [
            'class_name' => $data['class_name'],
            'level' => $data['level_id'],
            'result_template' => $data['result_template'] ?? '',
            'form_teacher' => $form_teacher_ids
        ];

        return $this->classModel->insert($payload);
    }

    public function updateClass(array $data)
    {
        $form_teacher_ids = implode(',', $data['form_teacher_ids'] ?? []);
        $payload = [
            'class_name' => $data['class_name'],
            'level' => $data['level_id'],
            'result_template' => $data['result_template'] ?? '',
            'form_teacher' => $form_teacher_ids
        ];

        return $this->classModel
            ->where('id', $data['id'])
            ->update($payload);
    }

    public function fetchClasses()
    {
        $classes = $this->classModel
            ->select([
                'class_name',
                'level AS level_id',
                'result_template',
                'form_teacher AS form_teacher_ids'
            ])
            ->orderBy('class_name', 'ASC')
            ->get();

        return array_map(function ($class) {
            $class['form_teacher_ids'] = array_filter(explode(',', $class['form_teacher_ids']));
            return $class;
        }, $classes);
    }

    public function deleteClass(int $id): bool|int
    {
        return $this->classModel
            ->where('id', $id)
            ->delete();
    }
}
