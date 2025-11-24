<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style;

use App\Support\Color;

class CssGradientGenerator
{
    private $gradient;

    private $rawValue = null;

    public static function withValue($value)
    {
        $instance = new static;

        $instance->rawValue = $value;

        $instance->gradient = $instance->buildGradientObject();

        return $instance;
    }

    public static function defaultGradient()
    {
        return [
            'type' => 'LINEAR',
            'angle' => 45,
            'colors' => [
                [
                    'color' => '#000000',
                    'stop' => 0,
                    'opacity' => 1,
                ],
                [
                    'color' => '#808080',
                    'stop' => 33,
                    'opacity' => 1,
                ],
                [
                    'color' => '#000000',
                    'stop' => 70,
                    'opacity' => 1,
                ],
                [
                    'color' => '#808080',
                    'stop' => 100,
                    'opacity' => 1,
                ],
            ]
        ];
    }

    private function buildGradientObject()
    {
        if (is_string($this->rawValue)) {
            $param = json_decode($this->rawValue, true);
        } else {
            $param = $this->rawValue;
        }

        $obj = (object) array_merge(
            static::defaultGradient(),
            (array) $param
        );

        $obj = json_decode(json_encode($obj));

        foreach ($obj->colors as $color) {
            $color->stop = $color->stop ?? 0;
            $color->opacity = $color->opacity ?? 1;
            $color->color = $color->color ?? '#000000';
        }

        return $obj;
    }

    protected function colors()
    {
        return collect($this->gradient->colors)->map(function ($color) {
            return sprintf('%s %s%%', $color->color, $color->stop);
        })->join(', ');
    }

    protected function makeLinearGradient()
    {
        return sprintf(
            'linear-gradient(%sdeg, %s);',
            $this->gradient->angle,
            $this->colors()
        );
    }

    public function getCssValue()
    {
        return $this->makeLinearGradient();
    }
}
