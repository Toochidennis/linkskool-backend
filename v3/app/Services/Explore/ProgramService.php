<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\Program;

class ProgramService
{
    protected Program $programModel;
    private FileHandler $fileHandler;


    public function __construct(\PDO $pdo)
    {
        $this->programModel = new Program($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function createProgram(array $data)
    {
        $data['banner_image'] = $this->processBannerImage();

        $payload = [
            'name' => $data['name'],
            'description' => $data['description'],
            'banner_image' => $data['banner_image'],
            'created_by' => $data['created_by'],
            'shortname' => $data['shortname'],
            'status' => $data['status'],
            'sponsor' => $data['sponsor'] ?? null,
            'is_free' => $data['is_free'],
            'trial_type' => $data['trial_type'],
            'trial_value' => $data['trial_value'],
            'age_groups' => json_encode($data['age_groups']),
            'cost' => $data['cost'] ?? 0,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ];

        return $this->programModel->insert($payload);
    }

    public function updateProgram(array $data)
    {
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $data['banner_image'] = $this->processBannerImage();
        }

        $payload = [
            'name' => $data['name'],
            'description' => $data['description'],
            'banner_image' => $data['banner_image'] ?? null,
            'updated_by' => $data['updated_by'],
            'shortname' => $data['shortname'],
            'status' => $data['status'],
            'sponsor' => $data['sponsor'] ?? null,
            'is_free' => $data['is_free'],
            'trial_type' => $data['trial_type'],
            'trial_value' => $data['trial_value'],
            'age_groups' => json_encode($data['age_groups']),
            'cost' => $data['cost'] ?? 0,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ];

        $id = $this->programModel
            ->where('id', $data['id'])
            ->update(
                array_filter(
                    $payload,
                    fn($value) => $value !== null
                )
            );

        if (!empty($data['old_banner_image']) && isset($data['banner_image'])) {
            $this->fileHandler->deleteOldFile($data['old_banner_image']);
        }

        return $id;
    }

    public function updateStatus(int $id, string $status)
    {
        return $this->programModel
            ->where('id', $id)
            ->update(['status' => $status]);
    }

    public function getPublishedPrograms(): array
    {
        return $this->programModel
            ->where('status', 'published')
            ->get();
    }

    public function getAllPrograms(): array
    {
        $results = $this->programModel
            ->select([
                'id',
                'name',
                'description',
                'banner_image',
                'shortname',
                'status',
                'sponsor',
                'is_free',
                'trial_type',
                'trial_value',
                'age_groups',
                'cost',
                'start_date',
                'end_date',
                'created_at',
                'created_by',
                'COUNT(program_courses.id) AS course_count'
            ])
            ->join('program_courses', 'programs.id = program_courses.program_id', 'LEFT')
            ->groupBy('programs.id')
            ->orderBy('programs.created_at', 'DESC')
            ->get();

        return array_map(function ($program) {
            $program['age_groups'] = json_decode($program['age_groups'], true);
            return $program;
        }, $results);
    }

    public function deleteProgram(int $id)
    {
        $program = $this->programModel
            ->where('id', $id)
            ->first();

        if ($program && !empty($program['banner_image'])) {
            $this->fileHandler->deleteOldFile($program['banner_image']);
        }

        return $this->programModel
            ->where('id', $id)
            ->delete();
    }

    private function processBannerImage(): mixed
    {
        $banner = $_FILES['banner_image'] ?? null;
        $bannerMap = [];

        if (!$banner || $banner['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($banner['tmp_name'])) {
            throw new \Exception("Invalid banner image upload.");
        }

        if ($banner && $banner['error'] === UPLOAD_ERR_OK && is_uploaded_file($banner['tmp_name'])) {
            $tmpName = $banner['tmp_name'];
            $fileName = strtolower(trim($banner['name']));
            $fileContent = file_get_contents($tmpName);
            $base64Content = base64_encode($fileContent);

            $bannerMap[] = [
                'file_name' => $fileName,
                'old_file_name' => '',
                'type' => 'image',
                'file' => $base64Content,
            ];
        }

        if (empty($bannerMap)) {
            throw new \Exception("No valid banner image provided.");
        }

        $processedFiles = $this->fileHandler->handleFiles($bannerMap);
        return $processedFiles[0]['file_name'];
    }
}
