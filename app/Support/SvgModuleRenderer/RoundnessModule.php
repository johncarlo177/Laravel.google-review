<?php

namespace App\Support\SvgModuleRenderer;

use App\Interfaces\ModuleRenderer;


class RoundnessModule extends BaseRenderer implements ModuleRenderer
{
    static $invertedLightCoords = [];

    protected function pathCommands()
    {
        $x = func_get_arg(0);
        $y = func_get_arg(1);
        $length = func_get_arg(2);

        $bits  = $this->checkNeighbours($this->x, $this->y);

        $check = fn (int $all, int $any): bool => ($bits & ($all | (~$any & 0xff))) === $all;

        $r = 0.4;

        $radius = $r * $length;

        $radiusInverse = (1 - $r) * $length;

        $radiusInverse2 = (1 - 2 * $r) * $length;

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 1 rounded corner on (1)
        if ($check(0b00101000, 0b01010101))
            return sprintf(
                ' M%1$s,%2$s m0,%3$s h%3$s v-%3$s h-%5$s q-%4$s,0 -%4$s,%4$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
            );


        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 1 rounded corner on (3)
        if ($check(0b10100000, 0b01010101))
            return sprintf(
                ' M%1$s,%2$s h%5$s q%4$s,0 %4$s,%4$s v%5$s h-%3$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 1 rounded corner on (5)
        if ($check(0b10000010, 0b01010101))
            return sprintf(
                ' M%1$s,%2$s h%3$s v%5$s q0,%4$s -%4$s,%4$s h-%5$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 1 rounded corner on (7)
        if ($check(0b00001010, 0b01010101))
            return sprintf(
                ' M%1$s,%2$s v%5$s q0,%4$s %4$s,%4$s h%5$s v-%3$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );


        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 4 rounded corners
        if ($check(0b000000, 0b01010101))
            return sprintf(
                ' M%1$s,%2$s m0,%4$s v%6$s q0,%4$s %4$s,%4$s h%6$s q%4$s,0 %4$s,-%4$s v-%6$s q0,-%4$s -%4$s,-%4$s h-%6$s q-%4$s,0 -%4$s,%4$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );


        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 2 rounded corners on (1, 3)
        if ($check(0b00100000, 0b01010101)) {
            return sprintf(
                ' M%1$s,%2$s m0,%3$s h%3$s v-%4$s q0,-%4$s -%4$s,-%4$s h-%6$s q-%4$s,0 -%4$s,%4$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );
        }

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 2 rounded corners on (1, 7)
        if ($check(0b00001000, 0b01010101)) {
            return sprintf(
                ' M%1$s,%2$s m%3$s,%3$s v-%3$s h-%5$s q-%4$s,0 -%4$s,%4$s v%6$s q0,%4$s %4$s,%4$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );
        }

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 2 rounded corners on (3, 5)
        if ($check(0b10000000, 0b01010101)) {
            return sprintf(
                ' M%1$s,%2$s h%5$s q%4$s,0 %4$s,%4$s v%6$s q0,%4$s -%4$s,%4$s h-%5$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );
        }

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 2 rounded corners on (5, 7)
        if ($check(0b00000010, 0b01010101)) {
            return sprintf(
                ' M%1$s,%2$s v%5$s q0,%4$s %4$s,%4$s h%6$s q%4$s,0 %4$s,-%4$s v-%5$sZ',
                $x,
                $y,
                $length,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );
        }

        return sprintf(
            ' M%1$s %2$s h%3$s v%3$s h-%3$sZ',
            $x,
            $y,
            $length
        );
    }



    protected function shouldRenderSingleModule()
    {
        return $this->qrcode->design->module === 'roundness'
            && $this->matrix->checkTypeNotIn(
                $this->x,
                $this->y,
                $this->options->keepAsSquare
            );
    }

    protected function invertedModule()
    {
        $svg = '';

        foreach ($this::neighbours as $coords) {

            // point x and y
            $px = $this->x + $coords[0];

            $py = $this->y + $coords[1];

            $x = $px * $this->scale;

            $y = $py * $this->scale;

            if (empty($this->matrix->get($px, $py))) continue;

            // if this is dark coords skip

            if ($this->matrix->check($px, $py)) continue;

            // if this is already rendered skip

            if (
                array_filter(
                    $this::$invertedLightCoords,
                    fn ($coords) => $coords[0] === $px && $coords[1] === $py
                )
            ) {
                continue;
            }

            $this::$invertedLightCoords[] = [$px, $py];

            $length = $this->scale;

            $bits  = $this->checkNeighbours($px, $py);

            $check = fn (int $mask): bool => ($bits & $mask) === $mask;

            $r = 0.4;

            $radius = $r * $length;

            $radiusInverse = (1 - $r) * $length;

            $radiusInverse2 = (1 - 2 * $r) * $length;

            // 1 2 3
            // 8 # 4
            // 7 6 5

            // 1 rounded corner on (1)
            if ($check(0b10000011))
                $svg .= sprintf(
                    ' M%1$s,%2$s h%4$s q-%4$s,0 -%4$s,%4$sZ',
                    $x,
                    $y,
                    $length,
                    $radius,
                    $radiusInverse,
                );

            // 1 2 3
            // 8 # 4
            // 7 6 5

            // 1 rounded corner on (3)
            if ($check(0b00001110))
                $svg .= sprintf(
                    ' M%1$s,%2$s m%3$s,0 v%4$s q0,-%4$s -%4$s,-%4$sZ',
                    $x,
                    $y,
                    $length,
                    $radius,
                    $radiusInverse,
                );

            // 1 2 3
            // 8 # 4
            // 7 6 5

            // 1 rounded corner on (5)
            if ($check(0b00111000)) {
                $svg .= sprintf(
                    ' M%1$s,%2$s m%3$s,%3$s h-%4$s q%4$s,0 %4$s,-%4$sZ',
                    $x,
                    $y,
                    $length,
                    $radius,
                    $radiusInverse,
                );
            }

            // 1 2 3
            // 8 # 4
            // 7 6 5

            // 1 rounded corner on (7)
            if ($check(0b11100000)) {
                $svg .= sprintf(
                    ' M%1$s,%2$s m0,%3$s v-%4$s q0,%4$s %4$s,%4$sZ',
                    $x,
                    $y,
                    $length,
                    $radius,
                    $radiusInverse,
                );
            }
        }


        return $svg;
    }

    protected function singleModuleCommands()
    {
        return $this->pathCommands(
            $this->x * $this->scale,
            $this->y * $this->scale,
            $this->scale,
        ) . $this->invertedModule();
    }
}
