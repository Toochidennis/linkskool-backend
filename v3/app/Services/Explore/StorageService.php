<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;

class StorageService
{
    public static function saveFile(array $file, ?string $groupPath = null): string
    {
        $fileHandler = new FileHandler();

        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception("Invalid image upload.");
        }

        if ($file && $file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
            $tmpName = $file['tmp_name'];
            $fileName = strtolower(trim($file['name']));
            $fileContent = file_get_contents($tmpName);
            $base64Content = base64_encode($fileContent);

            $imageMap[] = [
                'file_name' => $fileName,
                'old_file_name' => '',
                'type' => 'image',
                'file' => $base64Content,
            ];
        }

        if (empty($imageMap)) {
            throw new \Exception("No valid image provided.");
        }

        $processedFiles = $fileHandler->handleFiles($imageMap, false, $groupPath);
        return $processedFiles[0]['file_name'];
    }

    public static function deleteFile(string $oldImageUrl): void
    {
        $fileHandler = new FileHandler();
        $fileHandler->deleteOldFile($oldImageUrl);
    }
}
