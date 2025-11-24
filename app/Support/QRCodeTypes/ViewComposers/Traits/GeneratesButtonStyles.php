<?php

namespace App\Support\QRCodeTypes\ViewComposers\Traits;

use App\Support\Color;

trait GeneratesButtonStyles
{
    private function bindButtonStyles($bgColorKey, $textColorKey, $buttonSelector)
    {
        $colors = [
            'bg' => $this->designValue($bgColorKey),
            'color' => $this->designValue($textColorKey)
        ];

        $rules = [
            'bg' => 'background-color: %s;',
            'color' => 'color: %s;'
        ];

        $css = array_reduce(array_keys($colors), function ($result, $color) use ($rules, $colors, $buttonSelector) {
            if (empty($colors[$color])) {
                return;
            }

            $rule = sprintf($rules[$color], $colors[$color]);

            $result[] = sprintf('%s { %s }', $buttonSelector, $rule);

            return $result;
        }, []);

        if (empty($css)) return '';

        return implode('', $css);
    }
}
