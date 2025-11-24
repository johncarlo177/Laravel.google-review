<?php

namespace App\Support;


class ArrayHelper
{
    public static function sort(&$array, $sortKey = 'sort_order', $defaultValue = 100)
    {
        if (!is_array($array)) return;

        usort($array, function ($a, $b) use ($sortKey, $defaultValue) {
            $s1 = @$a[$sortKey] != null ? @$a[$sortKey] : $defaultValue;
            $s2 = @$b[$sortKey] != null ? @$b[$sortKey] : $defaultValue;

            return $s1 - $s2;
        });
    }

    public static function asArray($data)
    {
        if (!is_array($data) && !is_object($data)) return $data;

        $result = [];

        foreach ($data as $key => $value) {
            $result[$key] = (is_array($value) || is_object($value)) ? ArrayHelper::asArray($value) : $value;
        }

        return $result;
    }
}
