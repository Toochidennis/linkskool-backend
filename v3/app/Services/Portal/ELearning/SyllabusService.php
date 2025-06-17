<?php

namespace V3\App\Services\Portal\ELearning;

use Exception;
use PDO;
use V3\App\Models\Portal\ELearning\SyllabusModel;

class SyllabusService
{
    private SyllabusModel $syllabusModel;

    public function __construct(PDO $pdo)
    {
        $this->syllabusModel = new SyllabusModel($pdo);
    }

    public function create(array $data)
    {
        $payload = [
            'title' => $data['title'],
            'description' => $data['description'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'level' => $data['level_id'],
            'path_label' => json_encode($data['class_ids']),
            'author_id' => $data['creator_id'],
            'author_name' => $data['creator_role'],
            'term' => $data['term'],
            'upload_date' => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['image'])) {
            $binary = base64_decode($data['image'], true);

            if($binary === false || @imagecreatefromstring($binary) === false) {
                throw new Exception("Invalid or corrupted image data.");
            }

            // Prepare paths
            $uploadDir = __DIR__ . '/../../public/assets/e_learning/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = uniqid('syllabus_', true) . '.' . $ext;
            $filePath = $uploadDir . $filename;

            // Save the binary data to file
            if (file_put_contents($filePath, $binary) === false) {
                throw new Exception("Failed to save image.");
            }

            // Store web-accessible path and original filename
            $payload['image']      = '/assets/e_learning/' . $filename;
            $payload['image_name'] = $filename;
        }

        $newId = (int) $this->syllabusModel->insert($payload);
        if (!$newId) {
            throw new Exception("Failed to create syllabus.");
        }

        return [
            'success' => true,
            'syllabusId' => $newId,
            'message' => 'Syllabus created successfully.'
        ];
    }
}
