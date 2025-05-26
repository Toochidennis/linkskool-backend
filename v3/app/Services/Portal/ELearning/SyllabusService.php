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
            'level_id' => $data['level_id'],
            'class_ids' => json_encode($data['class_ids']),
            'creator_id' => $data['creator_id'],
            'creator_role' => $data['creator_role'],
            'term' => $data['term'],
            'year' => $data['year'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($data['teacher_id'])) {
            $payload['teacher_id'] = $data['teacher_id'];
        }

        if (!empty($data['image_base64'])) {
            // Extract the mime/type and the data
            if (preg_match('/^data:(image\/\w+);base64,(.+)$/', $data['image_base64'], $matches)) {
                [$full, $mime, $b64data] = $matches;
                $ext = explode('/', $mime)[1];     // e.g. "png" from "image/png"
                $binary = base64_decode($b64data);

                // Prepare paths
                $uploadDir = __DIR__ . '/../../public/assets/syllabus_images/';
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
                $payload['image']      = '/assets/syllabus_images/' . $filename;
                $payload['image_name'] = $filename;
            } else {
                throw new Exception("Invalid image_base64 format.");
            }
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
