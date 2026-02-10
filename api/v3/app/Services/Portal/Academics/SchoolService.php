<?php

namespace V3\App\Services\Portal\Academics;

use V3\App\Models\Portal\Academics\School;

class SchoolService
{
    private School $school;

    public function __construct(\PDO $pdo)
    {
        $this->school = new School($pdo);
    }

    public function getSchools(): array
    {
        $result = $this->school
            ->select([
                'id',
                'logo',
                'school_name',
                'token AS school_code',
                'address',
                'email',
                'website'
            ])
            ->where('school_name', '<>', '')
            ->orderBy('school_name', 'ASC')
            ->get();

        return array_map(function ($school) {
            $school['school_name'] = ucwords(strtolower($school['school_name']), " \t\r\n\f\v'-");
            return $school;
        }, $result);
    }
}
