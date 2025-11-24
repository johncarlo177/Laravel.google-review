<?php

namespace App\Support\System;

use App\Support\System\Interfaces\HasLoggerPrefix;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Throwable;

/**
 * @method void logDebug($message, ...$params)
 * @method void logInfo($message, ...$params)
 * @method void logError($message, ...$params)
 * @method void logWarning($message, ...$params)
 * @method void logAlert($message, ...$params)
 */
class Logger
{
    private $prefix = null;

    public static function forClass($class)
    {
        $prefix = class_basename($class);

        if (static::hasCustomPrefix($class)) {
            $prefix = $class::getLoggerPrefix();
        }

        $instance = new static($prefix);

        return $instance;
    }

    private static function hasCustomPrefix($class)
    {
        return (new ReflectionClass($class))->implementsInterface(HasLoggerPrefix::class);
    }

    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    private function formatLog($params)
    {
        $params = $this->prepareParams($params);

        try {
            $content = call_user_func_array('sprintf', $params);
        } catch (Throwable $th) {
            $content = var_export($params, true);
        }

        return sprintf(
            '%s: %s',
            $this->prefix,
            $content
        );
    }

    private function prepareParams($params)
    {
        return array_map(function ($param) {
            if (is_array($param) || is_object($param)) {
                return json_encode($param, JSON_PRETTY_PRINT);
            }

            return $param;
        }, $params);
    }

    public function __call($name, $arguments)
    {
        preg_match('/log(Warning|Error|Info|Debug|Alert)f?/', $name, $matches);

        $methodName = strtolower($matches[1]);

        $message = $this->formatLog($arguments);

        $this->log($methodName, $message);
    }

    private function log($methodName, $message)
    {
        call_user_func([Log::class, $methodName], $message);
    }
}
