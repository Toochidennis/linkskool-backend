<?php

namespace V3\App\Common\Utilities;

class QuestionImportFormatter
{
    public static function format(array $parsedData): array
    {
        $rows = $parsedData['data'];
        $images = $parsedData['images'];
        $hasZip = !empty($images);

        // Build image lookup map for O(1) access
        $imageMap = [];
        foreach ($images as $img) {
            $imageName = strtolower(trim(basename($img['name'])));
            $imageMap[$imageName] = $img['data'];
        }

        $yearMap = [];
        $errors = [];

        // Group by year
        foreach ($rows as $row) {
            $year = trim($row['year'] ?? '');
            if ($year === '') {
                continue;
            }

            $yearMap[$year][] = $row;
        }

        $formatted = [];

        foreach ($yearMap as $year => $questions) {
            $outputQuestions = [];

            foreach ($questions as $idx => $row) {
                $questionText = $row['question_text'] ?? $row['question'] ?? '';
                $questionImage = trim($row['question_image'] ?? '');

                $questionType = strtolower($row['question_type'] ?? 'multiple_choice');
                $isShort = $questionType === 'short_answer';

                if (trim($questionText) === '' && $questionImage === '') {
                    $errors[] = [
                        'year' => (int)$year,
                        'questionIndex' => $idx + 1,
                        'questionText' => 'N/A',
                        'error' => 'Both question text and image are empty'
                    ];
                }

                if ($isShort) {
                    $answer = trim($row['answer'] ?? '');
                    if ($answer === '') {
                        $errors[] = [
                            'year' => (int)$year,
                            'questionIndex' => $idx + 1,
                            'questionText' => $questionText,
                            'error' => 'Short answer has no answer provided'
                        ];
                    }

                    $correct = [
                        'order' => 0,
                        'text' => $answer
                    ];

                    $options = [];
                } else {
                    $options = [];
                    for ($i = 1; $i <= 6; $i++) {
                        $text = trim($row["option_{$i}_text"] ?? '');
                        $image = trim($row["option_{$i}_image"] ?? '');

                        if ($text !== '' || $image !== '') {
                            $options[] = [
                                'order' => $i,
                                'text' => $text,
                                'optionFiles' => self::processOptionFiles($row, $i, $imageMap, $hasZip)
                            ];
                        }
                    }

                    $ansIdx = (int)($row['answer'] ?? 0);
                    $ansText = $ansIdx ? ($row["option_{$ansIdx}_text"] ?? '') : '';
                    $ansImg = $ansIdx ? ($row["option_{$ansIdx}_image"] ?? '') : '';

                    if (trim($ansText) === '' && trim($ansImg) === '') {
                        $errors[] = [
                            'year' => (int)$year,
                            'questionIndex' => $idx + 1,
                            'questionText' => $questionText,
                            'error' => 'Correct answer has neither text nor image'
                        ];
                    }

                    $correct = [
                        'order' => $ansIdx,
                        'text' => $ansText ?: $ansImg
                    ];
                }

                $outputQuestions[] = [
                    'questionText' => $questionText,
                    'passage' => $row['passage'] ?? '',
                    'passageId' => (int)($row['passage_id'] ?? 0),
                    'questionType' => $isShort ? 'short_answer' : 'multiple_choice',
                    'instruction' => $row['instruction'] ?? '',
                    'topic' => $row['topic'] ?? '',
                    'topicId' => (int)($row['topic_id'] ?? 0),
                    'questionFiles' => self::processQuestionFiles($row, $imageMap, $hasZip),
                    'explanation' => $row['explanation'] ?? '',
                    'explanationId' => (int)($row['explanation_id'] ?? 0),
                    'options' => $options,
                    'correct' => $correct
                ];
            }

            $formatted[] = [
                'year' => (int)$year,
                'questions' => $outputQuestions
            ];
        }

        // Sort descending by year
        usort($formatted, fn($a, $b) => $b['year'] <=> $a['year']);

        return [
            'data' => $formatted,
            'errors' => $errors
        ];
    }

    private static function processOptionFiles(array $row, int $i, array $imageMap, bool $hasZip): array
    {
        $files = [];

        $imageKey = "option_{$i}_image";
        $imageName = trim($row[$imageKey] ?? '');

        if ($hasZip && $imageName !== '') {
            $lookupName = strtolower(trim(basename($imageName)));

            // O(1) lookup in image map
            if (isset($imageMap[$lookupName])) {
                $base64 = $imageMap[$lookupName];

                // Remove data:image/...;base64, prefix if present
                if (str_contains($base64, ',')) {
                    $base64 = explode(',', $base64)[1];
                }

                $files[] = [
                    'file_name' => $imageName,
                    'old_file_name' => '',
                    'type' => 'image',
                    'file' => $base64
                ];
            }
        }

        return $files;
    }

    private static function processQuestionFiles(array $row, array $imageMap, bool $hasZip): array
    {
        $files = [];
        $imageName = trim($row['question_image'] ?? '');

        if ($hasZip && $imageName !== '') {
            $lookupName = strtolower(trim(basename($imageName)));

            // O(1) lookup in image map
            if (isset($imageMap[$lookupName])) {
                $base64 = $imageMap[$lookupName];

                // Remove data:image/...;base64, prefix if present
                if (str_contains($base64, ',')) {
                    $base64 = explode(',', $base64)[1];
                }

                $files[] = [
                    'file_name' => $imageName,
                    'old_file_name' => '',
                    'type' => 'image',
                    'file' => $base64
                ];
            }
        }

        return $files;
    }
}
