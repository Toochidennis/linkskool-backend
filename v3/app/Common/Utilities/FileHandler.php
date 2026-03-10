<?php

namespace V3\App\Common\Utilities;

use V3\App\Common\Enums\ContentType;

class FileHandler
{
    private string $relativePath;
    private string $contentPath;
    private array $allowedExtensions;
    private int $thumbMaxWidth = 300;
    private int $thumbMaxHeight = 300;
    private int $jpegQuality = 80;
    private int $maxFileSize = 10 * 1024 * 1024; // 5MB


    public function __construct(int $contentType = 0)
    {
        $this->allowedExtensions = match ($contentType) {
            ContentType::QUIZ->value => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            default => ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'mp4', 'mov', 'avi', 'pptx', 'svg'],
        };

        $paths = PathResolver::getContentPaths();
        $this->contentPath = $paths['absolute'];
        $this->relativePath = $paths['relative'];
    }

    public function handleFiles(array $files, bool $isUpdate = false, ?string $groupPath = null): array
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

            // For update operations
            if ($isUpdate) {
                // If file is provided, it means new/updated image
                if (!empty($file['file'] ?? '')) {
                    // New or updated image - delete old file if exists
                    if (!empty($file['old_file_name'] ?? '')) {
                        $this->deleteOldFile($file['old_file_name']);
                    }
                    $processed[] = $this->processFile($file, $groupPath);
                } else {
                    // No file provided - keep existing file (no update)
                    $processed[] = $file;
                }
            } else {
                // For insert operations - always process the file
                $processed[] = $this->processFile($file, $groupPath);
            }
            $processed = array_map(function ($f) {
                $f['file'] = '';
                return $f;
            }, $processed);
        }

        return $processed;
    }

    public function deleteOldFile(string $oldFileName): void
    {
        $oldPath = $this->resolveAbsolutePath($oldFileName);
        if (file_exists($oldPath)) {
            @unlink($oldPath); // Suppress warning, continue even if file fails
        }
    }

    private function processFile(array $file, ?string $groupPath = null): array
    {
        $cleanName = basename($file['file_name']);
        $ext = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION));

        if (!\in_array($ext, $this->allowedExtensions, true)) {
            throw new \Exception("File type not allowed: .$ext");
        }

        $uniquePrefix = uniqid('', true);
        $newFileName = "{$uniquePrefix}_$cleanName";
        $normalizedGroupPath = $this->normalizeGroupPath($groupPath);
        $directory = $this->contentPath . str_replace('/', DIRECTORY_SEPARATOR, $normalizedGroupPath);

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new \Exception("Failed to create directory: {$directory}");
        }

        $filePath = "{$directory}{$newFileName}";

        $binary = base64_decode($file['file'], true);
        if ($binary === false) {
            throw new \Exception("Invalid base64 file data");
        }

        $size = \strlen($binary);
        if ($size > $this->maxFileSize) {
            throw new \Exception(
                "File too large. Maximum allowed size is 5MB."
            );
        }


        // IMAGE HANDLING
        if (\in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->resizeAndSaveImage($binary, $filePath, $ext);
        } else {
            // NON-IMAGE FILES: save directly
            if (file_put_contents($filePath, $binary) === false) {
                throw new \Exception("Failed to save file: $newFileName");
            }
        }

        $storedPath = "{$this->relativePath}{$normalizedGroupPath}{$newFileName}";
        $file['old_file_name'] = $storedPath;
        $file['file_name'] = $storedPath;
        $file['file'] = '';

        return $file;
    }


    private function resizeAndSaveImage(string $binary, string $outputPath, string $ext): void
    {
        $image = imagecreatefromstring($binary);
        if (!$image) {
            throw new \Exception("Invalid image data");
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $scale = min(
            $this->thumbMaxWidth / $width,
            $this->thumbMaxHeight / $height,
            1
        );

        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);

        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG / WEBP
        if (\in_array($ext, ['png', 'webp'], true)) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled(
            $thumb,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumb, $outputPath, $this->jpegQuality);
                break;
            case 'png':
                imagepng($thumb, $outputPath, 6);
                break;
            case 'webp':
                imagewebp($thumb, $outputPath, 80);
                break;
            default:
                imagedestroy($thumb);
                imagedestroy($image);
                throw new \Exception("Unsupported image format");
        }

        imagedestroy($thumb);
        imagedestroy($image);
    }


    /**
     * Deletes a list of files from the filesystem
     */
    public function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            $filePath = $file['file_name'] ?? $file['old_file_name'] ?? null;

            if ($filePath) {
                $absolute = $this->resolveAbsolutePath($filePath);
                if (file_exists($absolute)) {
                    @unlink($absolute); // Suppress warning, continue even if file fails
                }
            }
        }
    }

    private function normalizeGroupPath(?string $groupPath): string
    {
        if (empty($groupPath)) {
            return '';
        }

        $segments = preg_split('/[\/\\\\]+/', $groupPath) ?: [];
        $safeSegments = [];

        foreach ($segments as $segment) {
            $segment = strtolower(trim($segment));
            $segment = preg_replace('/[^a-z0-9._-]+/', '-', $segment);
            $segment = trim($segment, '.-_');

            if ($segment === '' || $segment === '.' || $segment === '..') {
                continue;
            }

            $safeSegments[] = $segment;
        }

        if (empty($safeSegments)) {
            return '';
        }

        return implode('/', $safeSegments) . '/';
    }

    private function resolveAbsolutePath(string $storedPath): string
    {
        $normalized = str_replace('\\', '/', trim($storedPath));
        $relativeBase = str_replace('\\', '/', $this->relativePath);

        if (str_starts_with($normalized, $relativeBase)) {
            $relative = substr($normalized, strlen($relativeBase));
        } else {
            $relative = basename($normalized);
        }

        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        if (str_contains($relative, '..')) {
            $relative = basename($relative);
        }

        return $this->contentPath . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }
}
