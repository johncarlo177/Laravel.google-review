<?php

namespace App\Support\System;

class System
{
    private static $_composer;

    private static function composer($key = null)
    {
        if (empty(static::$_composer)) {
            static::$_composer = json_decode(
                file_get_contents(
                    base_path('composer.json')
                ),
                true
            );
        }

        if (!$key) {
            return static::$_composer;
        }

        return @static::$_composer[$key];
    }

    public static function version()
    {
        return static::composer('version');
    }
}
