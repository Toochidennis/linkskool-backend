<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Models\Portal\ELearning\Content;

class AssignmentService
{
    private Content $content;
    private string $contentPath;
    private string $relativePath;

    private const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'jpg', 'png', 'mp4', 'jpeg'];

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
        $v3root = realpath(__DIR__ . '/../../../../');

        if (!$v3root) {
            throw new \RuntimeException("Could not resolve v3 root path.");
        }

        $publicPath = "$v3root/public";
        $dbName = $_SESSION['_db'] ?? 'default_db';
        $this->relativePath = "assets/elearning/$dbName/";
        $this->contentPath = $publicPath . DIRECTORY_SEPARATOR . $this->relativePath;
        if (
            !is_dir($this->contentPath) &&
            !mkdir($this->contentPath, 0755, true)
        ) {
            throw new \RuntimeException("Failed to create directory: {$this->contentPath}");
        }
    }

    public function addAssignment(array $data): int|bool
    {
        $payload = $this->buildAddPayload($data);
        $payload['url'] = $this->handleFiles($data['files']);
        return $this->content->insert($payload);
    }

    public function updateAssignment(array $data): bool|int
    {
        $payload = $this->buildUpdatePayload($data);
        $payload['url'] = $this->handleFiles($data['files'], true);

        return $this->content
            ->where('id', '=', $data['id'])
            ->update($payload);
    }

    /**
     * Build payload base for insert.
     */
    private function buildAddPayload(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['topic'],
            'parent' => $data['topic_id'],
            'outline' => $data['syllabus_id'],
            'author_name' => $data['creator_name'],
            'author_id' => $data['creator_id'],
            'upload_date' => date('Y-m-d H:i:s'),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'type' => ContentType::ASSIGNMENT->value,
            'path_label' => json_encode($data['classes']),
            'body' => $data['grade'],
        ];
    }

    /**
     * Build payload base for update.
     */
    private function buildUpdatePayload(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['topic'],
            'parent' => $data['topic_id'],
            'path_label' => json_encode($data['classes']),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'body' => $data['grade'],
        ];
    }

    /**
     * Handle file upload and optional deletion for update.
     */
    private function handleFiles(array $files, bool $isUpdate = false): string
    {
        $processed = [];

        foreach ($files as $index => $file) {
            if ($file['type'] === 'url') {
                $processed[] = $file;
                continue;
            }

            if ($isUpdate && empty($file['old_file_name'] ?? '')) {
                throw new \Exception("Missing old_file_name for file at index {$index}");
            }

            if (
                $isUpdate &&
                !empty($file['file_name'] ?? '') &&
                ($file['file_name'] ?? '') !== $file['old_file_name'] &&
                empty($file['file'] ?? '')
            ) {
                throw new \Exception('Missing new file data for changed file.');
            }

            if (
                $isUpdate &&
                empty($file['file_name'] ?? '') &&
                ($file['file_name'] ?? '') !== $file['old_file_name'] &&
                !empty($file['file'] ?? '')
            ) {
                throw new \Exception(
                    "You uploaded a new file but didn't provide its name."
                );
            }

            if (
                $isUpdate &&
                !empty($file['file_name'] ?? '') &&
                ($file['file_name'] ?? '') !== $file['old_file_name']
            ) {
                // Delete old file
                $oldPath = $this->contentPath . basename($file['old_file_name']);
                if (file_exists($oldPath) && !unlink($oldPath)) {
                    throw new \Exception("Failed to delete old file: $oldPath");
                }

                // Save new file
                $processed[] = $this->processFile($file);
                continue;
            }

            $processed[] = (!$isUpdate) ? $this->processFile($file) : $file;
        }

        return json_encode($processed);
    }

    /**
     * Validate and store a single file.
     */
    private function processFile(array $file): array
    {
        $cleanName = basename($file['file_name']);
        $ext = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new \Exception("File type not allowed: $ext");
        }

        // Generate a unique prefix
        $uniquePrefix = uniqid('', true);
        $newFileName = "{$uniquePrefix}_$cleanName";

        $filePath = "{$this->contentPath}{$newFileName}";
        $content = base64_decode($file['file']);

        if (file_put_contents($filePath, $content) === false) {
            throw new \Exception("Failed to save file: $newFileName");
        }

        $file['old_file_name'] = "{$this->relativePath}{$newFileName}";
        $file['file_name'] = "{$this->relativePath}{$newFileName}";
        $file['file'] = '';

        return $file;
    }

    public function deleteAssignment(int $id): bool
    {
        $material = $this->content
            ->select(['url'])
            ->where('id', '=', $id)
            ->first();

        if (!$material) {
            return false;
        }

        $files = json_decode($material['url'] ?? '[]', true);

        // Loop and delete each local file if it’s not a URL type
        foreach ($files as $file) {
            if (($file['type']) === 'url') {
                continue;
            }

            if (!empty($file['old_file_name'])) {
                $filePath = $this->contentPath . basename($file['old_file_name']);
                if (file_exists($filePath) && !unlink($filePath)) {
                    throw new \RuntimeException("Failed to delete file: $filePath");
                }
            }
        }

        return $this->content
            ->where('id', '=', $id)
            ->delete();
    }
}
