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
                        'content' => self::parseCsvContent($file)
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
        // Build image map for O(1) access
        $imageMap = [];
        foreach ($images as $img) {
            $imageMap[strtolower($img['name'])] = $img['data'];
        }

        // Keys where <img> should be converted to base64 (O(1) lookup)
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

        // Keys where HTML tags should be stripped (plain text only)
        $stripHtmlKeys = [
            'id' => true,
            'year' => true,
            'answer' => true,
            'question_image' => true,
            'option_1_image' => true,
            'option_2_image' => true,
            'option_3_image' => true,
            'option_4_image' => true,
            'option_5_image' => true,
            'explanation' => true
        ];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Get all tables - each table represents one question
        $tables = $xpath->query('//table');
        $allQuestions = [];

        foreach ($tables as $table) {
            if (!$table instanceof \DOMElement) {
                continue;
            }

            $rows = $table->getElementsByTagName('tr');
            $questionData = [];

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

                // Build inner HTML for the cell
                $innerHTML = '';
                foreach ($valueCell->childNodes as $child) {
                    $innerHTML .= $dom->saveHTML($child);
                }

                // Clean empty spans or p tags
                $innerHTML = preg_replace('/<p[^>]*>\s*<span[^>]*>\s*<\/span>\s*<\/p>/', '', $innerHTML);

                // Remove class attributes (c1, c2, c3, etc.)
                $innerHTML = preg_replace('/\s+class="[^"]*"/', '', $innerHTML);

                // Replace images if needed (O(1) lookup)
                if (isset($convertKeys[$key]) && !empty($innerHTML)) {
                    $innerDom = new \DOMDocument();
                    libxml_use_internal_errors(true);
                    $innerDom->loadHTML(mb_convert_encoding($innerHTML, 'HTML-ENTITIES', 'UTF-8'));
                    libxml_clear_errors();

                    foreach ($innerDom->getElementsByTagName('img') as $img) {
                        if (!$img instanceof \DOMElement) {
                            continue;
                        }
                        $src = $img->getAttribute('src');
                        $imgName = strtolower(basename($src));
                        if (isset($imageMap[$imgName])) {
                            $img->setAttribute('src', $imageMap[$imgName]);
                        }
                    }

                    // Save processed HTML
                    $innerHTML = '';
                    $bodyNode = $innerDom->getElementsByTagName('body')->item(0);
                    if ($bodyNode) {
                        foreach ($bodyNode->childNodes as $child) {
                            $innerHTML .= $innerDom->saveHTML($child);
                        }
                    }

                    // For option texts, simplify HTML if no images present
                    if (str_starts_with($key, 'option_') && str_ends_with($key, '_text')) {
                        // Check if there are images in the content
                        if (!preg_match('/<img[^>]*>/i', $innerHTML)) {
                            // No images, just return plain text
                            $innerHTML = trim(strip_tags($innerHTML));
                        }
                        // Otherwise keep HTML with images
                    }
                }

                // Handle image keys - extract filename from <img> tag if present
                if (str_ends_with($key, '_image')) {
                    // Check if innerHTML contains an <img> tag
                    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $innerHTML, $matches)) {
                        // Extract the filename from src attribute
                        $questionData[$key] = basename($matches[1]);
                    } else {
                        // No <img> tag, just strip HTML
                        $questionData[$key] = trim(strip_tags($innerHTML));
                    }
                } elseif (isset($stripHtmlKeys[$key])) {
                    // Strip HTML tags for other plain text keys
                    $questionData[$key] = trim(strip_tags($innerHTML));
                } else {
                    // Keep HTML for text content keys
                    $questionData[$key] = trim($innerHTML);
                }
            }

            // Ensure default keys exist
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
                if (!isset($questionData[$k])) {
                    $questionData[$k] = '';
                }
            }

            // Only add if it has meaningful data (at least a year)
            if (!empty($questionData['year']) || !empty($questionData['id'])) {
                $allQuestions[] = $questionData;
            }
        }

        return $allQuestions;
    }


    public static function extractImagesFromFolder(array $imageFiles): array
    {
        if (empty($imageFiles)) {
            return [];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $results = [];

        foreach ($imageFiles as $filePath) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions, true)) {
                continue;
            }

            $mime = $ext === 'jpg' ? 'image/jpeg' : "image/$ext";

            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                throw new \Exception("Failed to read image: $filePath");
            }

            $results[] = [
                'name' => basename($filePath),
                'data' => 'data:' . $mime . ';base64,' . base64_encode($fileContent),
                'type' => $mime,
            ];
        }

        return $results;
    }

    private static function parseZip(string $filePath, string $extractTo): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($filePath) === true) {
            $zip->extractTo($extractTo);
            $zip->close();
        } else {
            throw new \Exception('Failed to open ZIP file.');
        }

        $allFiles = [];
        $images = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractTo, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isFile()) {
                $path = $file->getPathname();
                // Check if file is inside an "images" folder
                if (stripos($file->getPath(), DIRECTORY_SEPARATOR . 'images') !== false) {
                    $images[] = $path;
                } else {
                    $allFiles[] = $path;
                }
            }
        }

        // Validate: only one main file is allowed
        if (\count($allFiles) > 1) {
            throw new \Exception('ZIP file must contain only one main file and an optional images folder.');
        }

        if (\count($allFiles) === 0) {
            throw new \Exception('ZIP file must contain at least one main file (CSV, JSON, HTML, or DOCX).');
        }

        return [
            'files' => $allFiles,   // all non-image files
            'images' => $images      // all image files
        ];
    }

    private static function parseCsvContent(string $filePath): array
    {
        $rows = array_map('str_getcsv', file($filePath));

        if (empty($rows)) {
            return [];
        }

        // First row is the header
        $headers = array_shift($rows);

        // Map each row to associative array using headers as keys
        $mappedData = [];
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $rowData = [];
            foreach ($headers as $index => $header) {
                $key = trim($header);
                $value = $row[$index] ?? '';
                $rowData[$key] = $value;
            }

            $mappedData[] = $rowData;
        }

        return $mappedData;
    }

    private static function getFileFormat(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}
