<?php

namespace V3\App\Common\Utilities;

class QuestionImportParser
{
    public static function parse(array $file): array
    {
        $parsedZip = self::parseZip(
            filePath: $file['tmp_name'],
            extractTo: sys_get_temp_dir() . '/' . uniqid('upload_', true)
        );

        $files = $parsedZip['files'];
        $images  = self::extractImagesFromFolder($parsedZip['images'] ?? '');
        $parsedData = [];

        foreach ($files as $file) {
            $fileFormat = self::getFileFormat($file);

            switch ($fileFormat) {
                case 'csv':
                    $parsedData[] = [
                        'type' => 'csv',
                        'file' => $file,
                        'content' => array_map('str_getcsv', file($file))
                    ];
                    break;
                case 'json':
                    $parsedData[] = [
                        'type' => 'json',
                        'file' => $file,
                        'content' => json_decode(file_get_contents($file), true)
                    ];
                    break;
                case 'html':
                case 'htm':
                    $htmlContent = file_get_contents($file);
                    $parsedData[] = [
                        'type' => 'html',
                        'file' => $file,
                        'content' => self::parseHtmlContent($htmlContent, $images)
                    ];
                    break;
                case 'docx':
                    $parsedData[] = [
                        'type' => $fileFormat,
                        'file' => $file,
                        'content' => null
                    ];
                    break;
                default:
                    throw new \Exception("Unsupported file format: $file");
            }
        }

        $images = self::extractImagesFromFolder($parsedZip['images'] ?? '');

        return [
            'data' => $parsedData,
            'images' => $images
        ];
    }


    private static function parseHtmlContent(string $htmlContent, array $images): array
    {
        // Build image lookup map for O(1) access
        $imageMap = [];
        foreach ($images as $imgData) {
            $imageMap[$imgData['name']] = $imgData['data'];
        }

        // Load HTML using DOMDocument
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Keys where <img> should be converted to base64
        $convertKeys = [
            'instruction' => true,
            'question' => true,
            'passage' => true,
            'question_text' => true,
            'option_1_text' => true,
            'option_2_text' => true,
            'option_3_text' => true,
            'option_4_text' => true,
            'option_5_text' => true
        ];

        // Map table rows into key => HTML
        $rows = $xpath->query('//table//tr');
        $data = [];

        foreach ($rows as $row) {
            if (!$row instanceof \DOMElement) {
                continue;
            }
            $cells = $row->getElementsByTagName('td');
            if ($cells->length < 2) {
                continue;
            }

            $key = trim($cells->item(0)->textContent);
            $valueCell = $cells->item(1);

            // Get inner HTML
            $innerHTML = '';
            foreach ($valueCell->childNodes as $child) {
                $innerHTML .= $dom->saveHTML($child);
            }

            // Process <img> tags if key requires conversion
            if (isset($convertKeys[$key]) && !empty($innerHTML)) {
                $innerDom = new \DOMDocument();
                libxml_use_internal_errors(true);
                $innerDom->loadHTML(mb_convert_encoding($innerHTML, 'HTML-ENTITIES', 'UTF-8'));
                libxml_clear_errors();

                $imgs = $innerDom->getElementsByTagName('img');
                foreach ($imgs as $img) {
                    if (!$img instanceof \DOMElement) {
                        continue;
                    }
                    $src = $img->getAttribute('src');
                    $imageName = basename($src);

                    // O(1) lookup in image map
                    if (isset($imageMap[$imageName])) {
                        $img->setAttribute('src', $imageMap[$imageName]);
                    }
                }

                // Save processed innerHTML
                $innerHTML = '';
                foreach ($innerDom->getElementsByTagName('body')->item(0)->childNodes as $child) {
                    $innerHTML .= $innerDom->saveHTML($child);
                }
            }

            $data[$key] = $innerHTML;
        }

        // Ensure some other keys exist
        $defaultKeys = [
            'id',
            'year',
            'question_image',
            'option_1_image',
            'option_2_image',
            'option_3_image',
            'option_4_image',
            'option_5_image',
            'answer',
            'explanation'
        ];

        foreach ($defaultKeys as $k) {
            if (!isset($data[$k])) {
                $data[$k] = '';
            }
        }

        return $data;
    }


    public static function extractImagesFromFolder(string $imagesFolder): array
    {
        if (!is_dir($imagesFolder)) {
            return [];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $results = [];

        $files = scandir($imagesFolder);

        foreach ($files as $file) {
            // Skip dots
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $imagesFolder . DIRECTORY_SEPARATOR . $file;

            // Skip directories
            if (is_dir($path)) {
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions, true)) {
                continue;
            }

            $mime = $ext === 'jpg' ? 'image/jpeg' : "image/$ext";

            $fileContent = file_get_contents($path);
            if ($fileContent === false) {
                throw new \Exception("Failed to read image: $path");
            }

            // Convert to base64 data URL (same as the TS code returns)
            $base64 = 'data:' . $mime . ';base64,' . base64_encode($fileContent);

            $results[] = [
                'name' => $file,
                'data' => $base64,
                'type' => $mime,
            ];
        }

        return $results;
    }

    private static function parseZip(string $filePath, string $extractTo): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($filePath) === true) {
            // Extract ZIP
            $zip->extractTo($extractTo);
            $zip->close();
        } else {
            throw new \Exception('Failed to open ZIP file.');
        }

        // Collect all extracted files (excluding images folder)
        $allFiles = [];
        $imagesFolder = null;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractTo, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isDir() && strtolower($file->getFilename()) === 'images') {
                $imagesFolder = $file->getPathname();
            } elseif ($file->isFile()) {
                $allFiles[] = $file->getPathname();
            }
        }

        return [
            'files' => $allFiles,      // csv, json, html, docx
            'images' => $imagesFolder, // path to images folder
        ];
    }

    private static function getFileFormat(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}
