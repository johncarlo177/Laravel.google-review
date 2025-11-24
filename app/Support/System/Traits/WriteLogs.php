<?php

namespace App\Support\System\Traits;

use App\Support\System\Logger;

trait WriteLogs
{

    protected static function logger()
    {
        return Logger::forClass(static::class);
    }

    protected static function logInfo($message, ...$params)
    {
        static::logger()->logInfo($message, ...$params);
    }

    protected static function logDebugf($message, ...$params)
    {
        static::logger()->logDebug($message, ...$params);
    }

    protected static function logDebug($message, ...$params)
    {
        static::logger()->logDebug($message, ...$params);
    }

    protected static function logError($message, ...$params)
    {
        static::logger()->logError($message, ...$params);
    }

    protected static function logErrorf($message, ...$params)
    {
        static::logger()->logError($message, ...$params);
    }

    protected static function logWarning($message, ...$params)
    {
        static::logger()->logWarning($message, ...$params);
    }

    protected static function logWarningf($message, ...$params)
    {
        static::logger()->logWarning($message, ...$params);
    }
}
