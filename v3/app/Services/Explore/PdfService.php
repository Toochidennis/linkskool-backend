<?php

namespace V3\App\Services\Explore;

use Smalot\PdfParser\Parser;

class PdfService
{
    private Parser $parser;

    public function __construct(?Parser $parser = null)
    {
        $this->parser = $parser ?? new Parser();
    }

    public function extract(string $pdfPath): string
    {
        $pdfPath = trim($pdfPath);

        if ($pdfPath === '') {
            return '';
        }

        try {
            $resolvedPath = $this->resolvePath($pdfPath);

            if ($resolvedPath === null || !is_readable($resolvedPath)) {
                return '';
            }

            $pdf = $this->parser->parseFile($resolvedPath);
            $text = $pdf->getText();

            return $this->normalizeWhitespace($text);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function resolvePath(string $path): ?string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'pdf_');

            if ($tmpFile === false) {
                return null;
            }

            $context = stream_context_create([
                'http' => ['timeout' => 20],
                'https' => ['timeout' => 20],
            ]);

            $content = @file_get_contents($path, false, $context);

            if ($content === false) {
                @unlink($tmpFile);
                return null;
            }

            file_put_contents($tmpFile, $content);
            return $tmpFile;
        }

        return $path;
    }

    private function normalizeWhitespace(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', $text) ?? '';
        return trim($text);
    }
}
