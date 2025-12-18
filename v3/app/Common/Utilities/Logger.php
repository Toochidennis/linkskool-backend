<?php

namespace V3\App\Common\Utilities;

use PDOException;

class Logger
{
    private static string $logFile = __DIR__ . '/../../../public/logs/error.log';

    public static function init(): void
    {
        // Exception handler – catches every uncaught throwable
        set_exception_handler(function ($e) {
            self::write($e); // log full details for you

            $isSQL = $e instanceof PDOException;
            $isUserErr = self::isUserError($e);

            $status = $isSQL || !$isUserErr ? 500 : 400;
            $msg = $isSQL || !$isUserErr
                ? 'Something went wrong. Please try again later.'
                : $e->getMessage();

            http_response_code($status);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $msg
            ]);
            exit;
        });

        // Non-fatal PHP errors (warnings, notices, etc.)
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $message = self::buildMessage(
                "ERROR [$errno]",
                $errstr,
                $errfile,
                $errline
            );
            file_put_contents(self::$logFile, $message, FILE_APPEND);
            return true; // suppress default PHP output
        });

        // Fatal error catcher (shutdown)
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

                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.'
                ]);
            }
        });
    }

    /** Write full exception details to log file */
    public static function write(\Throwable $e): void
    {
        $type = $e instanceof PDOException ? 'SQL EXCEPTION' : 'EXCEPTION';
        $message = self::buildMessage(
            $type,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        file_put_contents(self::$logFile, $message, FILE_APPEND);
    }

    /** Simple classification for user-caused errors (extend if needed) */
    private static function isUserError(\Throwable $e): bool
    {
        $msg = strtolower($e->getMessage());
        return str_contains($msg, 'required') ||
            str_contains($msg, 'invalid')  ||
            str_contains($msg, 'missing')  ||
            str_contains($msg, 'not found');
    }

    private static function buildMessage(
        string $type,
        string $message,
        string $file,
        int $line
    ): string {
        $timestamp = date('Y-m-d H:i:s');
        $ip     = $_SERVER['REMOTE_ADDR']   ?? 'CLI';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
        $uri    = $_SERVER['REQUEST_URI']    ?? 'N/A';
        $query  = $_SERVER['QUERY_STRING']   ?? '';

        return "[$timestamp] $type: $message in $file on line $line\n" .
            "Request: $method $uri?$query from $ip\n\n";
    }
}
