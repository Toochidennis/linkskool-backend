<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\Advertisement;

class AdvertisementService
{
    protected Advertisement $advertisementModel;
    private FileHandler $fileHandler;

    public function __construct(\PDO $pdo)
    {
        $this->advertisementModel = new Advertisement($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function createAdvertisement(array $data): int
    {
        $imageMap = [];

        $image = $_FILES['image'] ?? null;

        if (!$image || $image['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($image['tmp_name'])) {
            throw new \Exception('Image upload failed or no valid image provided. ' . json_encode($image));
        }

        $tmpName = $image['tmp_name'];
        $fileName = strtolower(trim(basename($image['name'])));
        $fileContent = file_get_contents($tmpName);
        $base64 = base64_encode($fileContent);
        $imageMap = [
            [
                'file_name' => $fileName,
                'old_file_name' => '',
                'type' => 'image',
                'file' => $base64
            ]
        ];

        $processedImages = $this->fileHandler->handleFiles($imageMap);

        $payload = [
            'title' => $data['title'],
            'content' => $data['content'],
            'action_url' => $data['action_url'],
            'action_text' => $data['action_text'],
            'display_position' => $data['display_position'] ?? 'sidebar',
            'status' => $data['status'],
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'is_sponsored' => $data['is_sponsored'],
            'image' => json_encode($processedImages[0]),
        ];

        return $this->advertisementModel->insert($payload);
    }

    public function updateAdvertisement(int $id, array $data): bool
    {
        $payload = [
            'title' => $data['title'],
            'content' => $data['content'],
            'action_url' => $data['action_url'],
            'action_text' => $data['action_text'],
            'display_position' => $data['display_position'] ?? 'sidebar',
            'status' => $data['status'],
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'is_sponsored' => $data['is_sponsored'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Handle image upload if present
        if ($data['image']['error'] === 0 && is_uploaded_file($data['image']['tmp_name'])) {
            $tmpName = $data['image']['tmp_name'];
            $fileName = strtolower(trim(basename($data['image']['name'])));
            $fileContent = file_get_contents($tmpName);
            $base64 = base64_encode($fileContent);
            $imageMap = [
                [
                    'file_name' => $fileName,
                    'old_file_name' => $data['old_file_name'] ?? '',
                    'type' => 'image',
                    'file' => $base64
                ]
            ];

            $processedImages = $this->fileHandler->handleFiles($imageMap, true);
            $payload['image'] = json_encode($processedImages[0]);
        }

        return $this->advertisementModel
            ->where('id', '=', $id)
            ->update($payload);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->advertisementModel
            ->where('id', '=', $id)
            ->update(['status' => $status]);
    }

    public function getAllAdvertisements(): array
    {
        $ads =  $this->advertisementModel->get();

        return array_map(function ($ad) {
            if (!empty($ad['image'])) {
                $ad['image'] = json_decode($ad['image'], true);
            }
            return $ad;
        }, $ads);
    }

    public function getPublishedAdvertisements(): array
    {
        $ads = $this->advertisementModel
            ->where('status', '=', 'published')
            ->get();

        return array_map(function ($ad) {
            if (!empty($ad['image'])) {
                $ad['image'] = json_decode($ad['image'], true);
            }
            return $ad;
        }, $ads);
    }

    public function deleteAdvertisement(int $id): bool
    {
        $oldNews = $this->advertisementModel->where('id', '=', $id)->first();
        if ($oldNews && !empty($oldNews['image'])) {
            $imageData = json_decode($oldNews['image'], true);
            if (!empty($imageData['file_name'])) {
                $this->fileHandler->deleteOldFile($imageData['file_name']);
            }
        }
        return $this->advertisementModel
            ->where('id', '=', $id)
            ->delete();
    }
}
