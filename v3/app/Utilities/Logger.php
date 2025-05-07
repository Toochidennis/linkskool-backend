<?php

namespace V3\App\Utilities;

class Logger
{
    private static $logFile = __DIR__ . '/../../public/logs/error.log';

    public static function init(): void
    {
        // Exception Handler
        set_exception_handler(function ($e) {
            $message = self::buildMessage(
                "EXCEPTION",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
            file_put_contents(self::$logFile, $message, FILE_APPEND);
        });

        // Error Handler
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $message = self::buildMessage(
                "ERROR [$errno]",
                $errstr,
                $errfile,
                $errline
            );
            file_put_contents(self::$logFile, $message, FILE_APPEND);
            return true; // suppress default error output
        });

        // Fatal Error Catcher
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null) {
                $message = self::buildMessage(
                    "FATAL ERROR",
                    $error['message'],
                    $error['file'],
                    $error['line']
                );
                file_put_contents(self::$logFile, $message, FILE_APPEND);
            }
        });
    }

    private static function buildMessage(
        string $type,
        string $message,
        string $file,
        int $line
    ): string {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
        $uri = $_SERVER['REQUEST_URI'] ?? 'N/A';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        return "[$timestamp] $type: $message in $file on line $line\n" .
            "Request: $method $uri?$query from $ip\n\n";
    }
}
