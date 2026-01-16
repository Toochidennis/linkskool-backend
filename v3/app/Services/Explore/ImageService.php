<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;

class ImageService
{
    public static function processImage(array $image): string
    {
        $fileHandler = new FileHandler();

        if ($image['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($image['tmp_name'])) {
            throw new \Exception("Invalid image upload.");
        }

        if ($image && $image['error'] === UPLOAD_ERR_OK && is_uploaded_file($image['tmp_name'])) {
            $tmpName = $image['tmp_name'];
            $fileName = strtolower(trim($image['name']));
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

        $processedFiles = $fileHandler->handleFiles($imageMap);
        return $processedFiles[0]['file_name'];
    }

    public static function deleteOldImage(string $oldImageUrl): void
    {
        $fileHandler = new FileHandler();
        $fileHandler->deleteOldFile($oldImageUrl);
    }
}
