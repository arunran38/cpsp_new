<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use PDOException;
use Throwable;

class SafeExceptionHandler
{
    /**
     * Process an exception by securely logging its full technical details
     * and returning a user-safe message devoid of raw SQL, database credentials, or server paths.
     */
    public static function handle(Throwable $e, string $fallbackMessage = 'An unexpected error occurred. Please try again.'): string
    {
        self::log($e, $fallbackMessage);

        return self::getSafeMessage($e, $fallbackMessage);
    }

    /**
     * Log full technical details to storage/logs/laravel.log for developer diagnosis.
     */
    public static function log(Throwable $e, string $context = 'Application Exception'): void
    {
        $logPayload = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($e instanceof QueryException) {
            $logPayload['sql'] = $e->getSql();
            $logPayload['bindings'] = $e->getBindings();
        }

        $logPayload['trace'] = $e->getTraceAsString();

        Log::error("[{$context}] " . $e->getMessage(), $logPayload);
    }

    /**
     * Convert technical exceptions into user-friendly notifications.
     */
    public static function getSafeMessage(Throwable $e, string $fallbackMessage = 'An unexpected error occurred. Please try again.'): string
    {
        $rawMessage = $e->getMessage();

        // 1. Database Query / PDO Exceptions
        if ($e instanceof QueryException || $e instanceof PDOException || str_contains($rawMessage, 'SQLSTATE')) {
            // Friendly handling for common unique constraint / duplicate violations
            if (str_contains($rawMessage, '1062 Duplicate entry') || str_contains($rawMessage, 'Integrity constraint violation: 1062')) {
                if (str_contains($rawMessage, 'receipt_no') || str_contains($rawMessage, 'petition_no')) {
                    return 'A petition with this receipt number already exists in the system.';
                }
                if (str_contains($rawMessage, 'file_no')) {
                    return 'A petition with this file number already exists.';
                }
                if (str_contains($rawMessage, 'pen')) {
                    return 'A user or officer with this PEN number already exists.';
                }
                if (str_contains($rawMessage, 'email')) {
                    return 'A user with this email address already exists.';
                }
                return 'A record with duplicate key details already exists in the system.';
            }

            // Foreign key constraints
            if (str_contains($rawMessage, '1451') || str_contains($rawMessage, '1452') || str_contains($rawMessage, 'foreign key constraint')) {
                return 'This operation cannot be completed because related records depend on it or an invalid reference was provided.';
            }

            // Generic clean database error
            return 'A database error occurred while processing your request. The technical details have been logged for administrator review.';
        }

        // 2. Sanitize general exceptions: never leak system file paths, SQL keywords, or vendor stack details
        $sensitiveTokens = ['SQLSTATE', 'Connection', 'insert into', 'update `', 'select `', 'delete from', 'Unknown column', 'Table \'', '.php on line', 'vendor\\', 'vendor/'];
        foreach ($sensitiveTokens as $token) {
            if (stripos($rawMessage, $token) !== false) {
                return $fallbackMessage ?: 'A system error occurred while processing your request. Please try again later.';
            }
        }

        // 3. If message is user-friendly text without sensitive tokens, return it safely
        return $rawMessage ?: $fallbackMessage;
    }
}

