<?php

namespace App\Support\SvgModuleRenderer;

use App\Interfaces\ModuleRenderer;

class HorizontalLinesModule extends BaseRenderer implements ModuleRenderer
{
    static $invertedLightCoords = [];

    protected function pathCommands()
    {
        $x = $this->x * $this->scale;
        $y = $this->y * $this->scale;

        $height = $this->scale - 0.2 * $this->scale;

        $width = $this->scale;

        $bits  = $this->checkNeighbours($this->x, $this->y);

        $check = fn (int $all, int $any): bool => ($bits & ($all | (~$any & 0xff))) === $all;

        $r = 0.5;

        $radius = $r * $height;

        $radiusInverse = (1 - $r) * $height;

        $radiusInverse2 = (1 - 2 * $r) * $height;

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 4 rounded corners
        if ($check(0b0000000, 0b01110111)) {
            return sprintf(
                ' M%1$s,%2$s m0,%4$s v%6$s q0,%4$s %4$s,%4$s h%6$s q%4$s,0 %4$s,-%4$s v-%6$s q0,-%4$s -%4$s,-%4$s h-%6$s q-%4$s,0 -%4$s,%4$sZ',
                $x,
                $y,
                $height,
                $radius,
                $radiusInverse,
                $radiusInverse2,
            );
        }


        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 2 rounded corners on (3, 5)
        if ($check(0, 0b11110111)) {
            return sprintf(
                ' M%1$s,%2$s h%5$s q%4$s,0 %4$s,%4$s v%6$s q0,%4$s -%4$s,%4$s h-%5$sZ',
                $x,
                $y,
                $height,
                $radius,
                $radiusInverse,
                $radiusInverse2
            );
        }

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 2 rounded corners on (1, 7)
        if ($check(0b00001000, 0b01111111)) {
            return sprintf(
                ' M%1$s,%2$s m%7$s,%3$s v-%3$s h-%5$s q-%4$s,0 -%4$s,%4$s v%6$s q0,%4$s %4$s,%4$sZ',
                $x,
                $y,
                $height,
                $radius,
                $radiusInverse,
                $radiusInverse2,
                $width
            );
        }



        return sprintf(
            ' M%1$s,%2$s h%3$s v%4$s h-%3$sZ',
            $x,
            $y,
            $width,
            $height
        );
    }

    protected function shouldRenderSingleModule()
    {
        return $this->qrcode->design->module === 'horizontal-lines'
            && $this->matrix->checkTypeNotIn(
                $this->x,
                $this->y,
                $this->options->keepAsSquare
            );
    }

    protected function singleModuleCommands()
    {
        return $this->pathCommands(
            $this->x,
            $this->y
        );
    }
}
