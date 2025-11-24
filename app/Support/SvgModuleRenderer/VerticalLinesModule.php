<?php

namespace App\Support\SvgModuleRenderer;

use App\Interfaces\ModuleRenderer;


class VerticalLinesModule extends BaseRenderer implements ModuleRenderer
{
    static $invertedLightCoords = [];

    protected function pathCommands()
    {
        $x = $this->x * $this->scale;
        $y = $this->y * $this->scale;

        $width = $this->scale - 0.2 * $this->scale;

        $height = $this->scale;

        $bits  = $this->checkNeighbours($this->x, $this->y);

        $check = fn (int $all, int $any): bool => ($bits & ($all | (~$any & 0xff))) === $all;

        $r = 0.5;

        $radius = $r * $width;

        $radiusInverse = (1 - $r) * $width;

        $radiusInverse2 = (1 - 2 * $r) * $width;

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 4 rounded corners
        if ($check(0b0000000, 0b11011101)) {
            return sprintf(
                ' M%1$s,%2$s m0,%4$s v%6$s q0,%4$s %4$s,%4$s h%6$s q%4$s,0 %4$s,-%4$s v-%6$s q0,-%4$s -%4$s,-%4$s h-%6$s q-%4$s,0 -%4$s,%4$sZ',
                $x,
                $y,
                $width,
                $radius,
                $radiusInverse,
                $radiusInverse2,
            );
        }


        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 2 rounded corners on (1, 3)
        if ($check(0b00000000, 0b11111101)) {
            return sprintf(
                ' M%1$s,%2$s m0,%7$s h%3$s v-%4$s q0,-%4$s -%4$s,-%4$s h-%6$s q-%4$s,0 -%4$s,%4$sZ',
                $x,
                $y,
                $width,
                $radius,
                $radiusInverse,
                $radiusInverse2,
                $height
            );
        }

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 2 rounded corners on (5, 7)
        if ($check(0b00000000, 0b11011111)) {
            return sprintf(
                ' M%1$s,%2$s v%5$s q0,%4$s %4$s,%4$s h%6$s q%4$s,0 %4$s,-%4$s v-%5$sZ',
                $x,
                $y,
                $width,
                $radius,
                $radiusInverse,
                $radiusInverse2
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
        return $this->qrcode->design->module === 'vertical-lines'
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
