<?php

namespace App\Support\System;

class MemoryCache
{
    protected static $cache = [];

    protected static $rememberedKeys = [];

    public static function remember($key, $maker)
    {
        if (!isset(static::$rememberedKeys[$key])) {

            static::$cache[$key] = call_user_func($maker);

            static::$rememberedKeys[$key] = true;
            // 
        }

        return @static::$cache[$key];
    }
}
