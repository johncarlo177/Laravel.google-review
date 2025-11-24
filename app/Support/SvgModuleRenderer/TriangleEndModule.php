<?php

namespace App\Support\SvgModuleRenderer;

use App\Interfaces\ModuleRenderer;

class TriangleEndModule extends BaseRenderer implements ModuleRenderer
{
    const modules = ['triangle-end'];

    protected function pathCommands()
    {
        $x = func_get_arg(0);
        $y = func_get_arg(1);
        $width = func_get_arg(2);
        $height = func_get_arg(3);

        $bits  = $this->checkNeighbours($this->x, $this->y);

        $check = fn (int $all, int $any): bool => ($bits & ($all | (~$any & 0xff))) === $all;

        // 1 2 3
        // 8 # 4
        // 7 6 5

        if ($check(0b00100000, 0b01010101)) {
            return $this->triangle(1);
        }

        if ($check(0b00000010, 0b01010101)) {
            return $this->triangle(3);
        }

        if ($check(0b10000000, 0b01010101)) {
            return $this->triangle(2);
        }

        if ($check(0b00001000, 0b01010101)) {
            return $this->triangle(4);
        }

        /** Loneleness module, make it rhombus */
        if ($check(0, 0b01010101)) {
            return $this->triangle(5);
        }

        // 1 2 3
        // 8 # 4
        // 7 6 5

        if ($check(0, 0b11010111)) {
            return $this->trapezoid(5);
        }

        if ($check(0, 0b01111101)) {
            return $this->trapezoid(1);
        }

        if ($check(0, 0b11110101)) {
            return $this->trapezoid(3);
        }

        if ($check(0, 0b01011111)) {
            return $this->trapezoid(7);
        }

        return sprintf(
            ' M%1$s %2$s h%3$s v%4$s h-%3$sZ',
            $x,
            $y,
            $width,
            $height
        );
    }

    /**
     * @param direction determines the direction of the triangle
     */
    // 0 1 0
    // 4 5 2
    // 0 3 0
    protected function triangle($direction)
    {
        $x = $this->x * $this->scale;

        $y = $this->y * $this->scale;

        $height = $this->scale / 2;

        $base = $this->scale;

        switch ($direction) {
            case 1:
                $y += $height;

                $points = [
                    [$x, $y],
                    [$x + $base / 2, $y - $height],
                    [$x + $base, $y],
                    [$x + $base, $y + $height],
                    [$x, $y + $height],
                ];
                break;

            case 3:
                $y += $height;
                $points = [
                    [$x, $y],
                    [$x + $base / 2, $y + $height],
                    [$x + $base, $y],
                    [$x + $base, $y - $height],
                    [$x, $y - $height],
                ];
                break;
            case 2:
                $x += $height;
                $points = [
                    [$x, $y],
                    [$x + $height, $y + $base / 2],
                    [$x, $y + $base],
                    [$x - $height, $y + $base],
                    [$x - $height, $y],
                ];
                break;
            case 4:
                $x += $height;
                $points = [
                    [$x, $y],
                    [$x - $height, $y + $base / 2],
                    [$x, $y + $base],
                    [$x + $height, $y + $base],
                    [$x + $height, $y],
                ];
                break;
            case 5:
                $y += $height;

                $points = [
                    [$x, $y],
                    [$x + $base / 2, $y - $height],
                    [$x + $base, $y],
                    [$x + $base / 2, $y + $height],
                ];

                break;
        }


        $lines = ' L' . implode(
            ',',
            array_map(
                fn ($point) => sprintf('%s,%s', $point[0], $point[1]),
                array_slice($points, 1)
            )
        ) . 'Z';

        return sprintf(' M%s,%s %s', $points[0][0], $points[0][1], $lines);
    }

    // 1 2 3
    // 8 # 4
    // 7 6 5
    private function trapezoid($direction)
    {
        $x = $this->x * $this->scale;

        $y = $this->y * $this->scale;

        $length = $this->scale;

        switch ($direction) {
            case 5:
                $points = [
                    [$x, $y],
                    [$x + $length, $y],
                    [$x + $length, $y + $length / 2],
                    [$x + $length / 2, $y + $length],
                    [$x, $y + $length]
                ];
                break;
            case 1:
                $points = [
                    [$x, $y + $length / 2],
                    [$x + $length / 2, $y],
                    [$x + $length, $y],
                    [$x + $length, $y + $length],
                    [$x, $y + $length]
                ];
                break;

            case 3:
                $points = [
                    [$x, $y],
                    [$x + $length / 2, $y],
                    [$x + $length, $y + $length / 2],
                    [$x + $length, $y + $length],
                    [$x, $y + $length]
                ];
                break;

            case 7:
                $points = [
                    [$x, $y + $length / 2],
                    [$x + $length / 2, $y + $length],
                    [$x + $length, $y + $length],
                    [$x + $length, $y],
                    [$x, $y]
                ];
                break;
        }

        $lines = ' L' . implode(
            ',',
            array_map(
                fn ($point) => sprintf('%s,%s', $point[0], $point[1]),
                $points
            )
        ) . 'Z';

        return sprintf(' M%s,%s %s', $points[0][0], $points[0][1], $lines);
    }

    protected function shouldRenderSingleModule()
    {
        $module = array_filter($this::modules, fn ($m) => $m === $this->qrcode->design->module);

        return !empty($module)
            && $this->matrix->checkTypeNotIn(
                $this->x,
                $this->y,
                $this->options->keepAsSquare
            );
    }

    protected function singleModuleCommands()
    {
        return $this->pathCommands(
            $this->x * $this->scale,
            $this->y * $this->scale,
            $this->scale,
            $this->scale
        );
    }
}
