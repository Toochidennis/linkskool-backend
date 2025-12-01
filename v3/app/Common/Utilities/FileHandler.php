<?php

namespace V3\App\Common\Utilities;

use V3\App\Common\Enums\ContentType;

class FileHandler
{
    private string $relativePath;
    private string $contentPath;
    private array $allowedExtensions;

    public function __construct(int $contentType = 0)
    {
        $this->allowedExtensions = match ($contentType) {
            ContentType::QUIZ->value => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            default => ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'mp4', 'mov', 'avi', 'pptx'],
        };

        $paths = PathResolver::getContentPaths();
        $this->contentPath = $paths['absolute'];
        $this->relativePath = $paths['relative'];
    }

    public function handleFiles(array $files, bool $isUpdate = false): array
    {
        $processed = [];

        if (!empty($files) && array_keys($files) !== range(0, count($files) - 1)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ($file['type'] === 'url') {
                $processed[] = $file;
                continue;
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
                throw new \Exception("You uploaded a new file but didn't provide its name.");
            }

            if (
                $isUpdate &&
                !empty($file['file_name'] ?? '') &&
                ($file['file_name'] ?? '') !== $file['old_file_name']
            ) {
                if (!empty($file['old_file_name'] ?? '')) {
                    $this->deleteOldFile($file['old_file_name']);
                }
                $processed[] = $this->processFile($file);
                continue;
            }
            $processed[] = !$isUpdate ? $this->processFile($file) : $file;
            $processed = array_map(function ($f) {
                $f['file'] = '';
                return $f;
            }, $processed);
        }

        return $processed;
    }

    private function deleteOldFile(string $oldFileName): void
    {
        $oldPath = $this->contentPath . basename($oldFileName);
        if (file_exists($oldPath) && !unlink($oldPath)) {
            throw new \Exception("Failed to delete old file: $oldPath");
        }
    }

    private function processFile(array $file): array
    {
        $cleanName = basename($file['file_name']);
        $ext = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION));

        if (!\in_array($ext, $this->allowedExtensions, true)) {
            throw new \Exception("File type not allowed: .$ext");
        }

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

    /**
     * Deletes a list of files from the filesystem
     */
    public function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            $filePath = $file['file_name'] ?? $file['old_file_name'] ?? null;

            if ($filePath) {
                $absolute = $this->contentPath . basename($filePath);
                if (file_exists($absolute)) {
                    @unlink($absolute); // Suppress warning, continue even if file fails
                }
            }
        }
    }
}
