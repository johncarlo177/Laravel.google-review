<?php

namespace App\Support;

class ConfigHelper
{
    public static function isEnabled($key)
    {
        return config($key) === 'enabled';
    }

    public static function isNotEnabled($key)
    {
        return config($key) !== 'enabled';
    }

    public static function isDisabled($key)
    {
        return config($key) === 'disabled';
    }

    public static function isNotDisabled($key)
    {
        return config($key) !== 'disabled';
    }

    public static function shouldSavePngFile()
    {
        return static::isEnabled('qrcode.generate_simple_png_file');
    }
}
