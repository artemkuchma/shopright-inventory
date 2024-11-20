<?php

namespace helpers;

use models\Logs;

class LogHelper
{
    /**
     * Logs the last PHP error to the system by creating a new log entry.
     * The method retrieves the last error using `error_get_last()`, and if an error is found,
     * it logs the error details (timestamp, type, message, file, and line) into the logs model.
     *
     * If no error is found, the method returns false. If the error is successfully logged,
     * it returns true.
     *
     * @return bool Returns true if the error is logged successfully, false if no error is found.
     */
    public static function logError(): bool
    {
        $lastError = error_get_last();

        if (!$lastError) {
            return false;
        }

        $log = new Logs();
        $log->load(['timestamp' => time(),
                'type' => $lastError['type'],
                'message' => $lastError['message'],
                'file' => $lastError['file'] ?? 'unknown',
                'line' => $lastError['line'] ?? 0,]);
        return $log->add();
    }
}
