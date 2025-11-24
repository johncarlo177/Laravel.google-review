<?php

namespace App\Support;

use Illuminate\Support\Str;

class StringHelper
{
    public static function random($length = 10, $seed = null)
    {
        if (!$seed) {
            $seed = array_merge(
                range('a', 'z'),
                range('A', 'Z'),
                range('0', '9')
            );
        }

        if (is_string($seed)) {
            $seed = str_split($seed);
        }

        $result = [];

        while (count($result) < $length) {
            $result[] = $seed[random_int(0, count($seed) - 1)];
        }

        return implode($result);
    }

    public static function title($str)
    {
        return Str::title(str_replace('-', ' ', $str));
    }
}
