<?php

namespace Database\Factories;

use App\Repositories\ColorPaletteGenerator;
use App\Models\QRCode;
use Illuminate\Database\Eloquent\Factories\Factory;



/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QRCode>
 */
class QRCodeFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = collect(QRCode::getTypes())->random();

        $type = 'url';

        $url = $this->faker->url();

        $palette = config('colors')[random_int(0, count(config('colors')) - 1)];

        $palette = array_map(
            fn ($color) => preg_match('/#/', $color) ? $color : '#' . $color,
            $palette
        );

        return [
            "type" => $type,
            "data" => [
                "url" => $url
            ],
            "design" => [
                "module" => "roundness",
                "fillType" => "gradient",
                "gradientType" => "RADIAL",
                "eyeExternalColor"      => $palette[0],
                "eyeInternalColor"      => $palette[1],
                "gradientStartColor"    => $palette[2],
                "gradientEndColor"      => $palette[3],
            ],
            "archived" => 0,
        ];
    }

    public static function palette($index)
    {
        $palettes = config('colors');

        $palette = $palettes[$index % count($palettes)];

        $palette = array_map(
            fn ($color) => preg_match('/#/', $color) ? $color : '#' . $color,
            $palette
        );

        return [
            "design" => [
                "module" => "roundness",
                "fillType" => "gradient",
                "gradientType" => "RADIAL",
                "eyeExternalColor"      => $palette[0],
                "eyeInternalColor"      => $palette[1],
                "gradientStartColor"    => $palette[2],
                "gradientEndColor"      => $palette[3],
            ]
        ];
    }
}
