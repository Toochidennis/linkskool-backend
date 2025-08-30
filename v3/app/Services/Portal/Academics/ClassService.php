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
        $payload = array_map(function ($class) {
            $class['level'] = $class['level_id'];
            unset($class['level_id']);
            return $class;
        }, $data);

        return $this->classModel->insert($payload);
    }

    public function fetchClasses()
    {
        $results = $this->classModel->get();

        foreach ($results as &$result) {
            $result['level_id'] = $result['level'];
            unset($result['level']);
        }

        return $results;
    }
}
