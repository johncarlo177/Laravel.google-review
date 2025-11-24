<?php

namespace App\Support\SvgModuleRenderer;

use App\Interfaces\ModuleRenderer;

class DynamicModuleRenderer extends BaseRenderer implements ModuleRenderer
{
    const modules = [
        'diamond',
        'fish',
        'tree',
        'twoTrianglesWithCircle',
        'fourTriangles'
    ];

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

    protected function pathCommands(
        $angle = 90,
        $t1 = 0.71,
        $t2 = 1.35,
        $t3 = 0.71,
        $length = 20,
        $originX = 50,
        $originY = 40,
    ) {
        $rotationDeg = $angle;

        $deg2rad = pi() / 180;

        $origin = ['x' => $originX, 'y' => $originY];

        $ts = [$t1, $t2, $t3];

        $points = array_map(function ($i) use ($origin, $length, $rotationDeg, $deg2rad) {
            return [
                'x' => $origin['x'] +
                    $length *
                    cos($rotationDeg * $deg2rad + ((2 * pi()) / 3) * $i),
                'y' =>
                $origin['y'] +
                    $length *
                    sin($rotationDeg * $deg2rad + ((2 * pi()) / 3) * $i)
            ];
        }, range(0, 2));

        $array = [];

        for ($i = 0; $i < count($points); $i++) {
            $ii = $i > 0 ? $i - 1 : count($points) - 1;

            $pp = $points[$ii];

            $p = $points[$i];

            $array[] = [
                'x' => $pp['x'] + ($p['x'] - $pp['x']) * $ts[$i],
                'y' => $pp['y'] + ($p['y'] - $pp['y']) * $ts[$i],
            ];
        }

        $array2 = [];

        for ($i = count($points) - 1; $i >= 0; $i--) {
            $ii = $i === count($points) - 1 ? 0 : $i + 1;
            $p = $points[$i];
            $pp = $points[$ii];

            $array2[] = [
                'x' => $pp['x'] + ($p['x'] - $pp['x']) * $ts[$ii],
                'y' => $pp['y'] + ($p['y'] - $pp['y']) * $ts[$ii],
            ];
        }

        $points2 = [];

        for ($i = 0; $i < count($array); $i++) {
            $points2[] = $array[$i];
            $points2[] = $array2[count($array2) - 1 - $i];
        }

        $pointsCommands = implode('', array_map(function ($p) {
            return sprintf('L%s,%s', $p['x'], $p['y']);
        }, $points2));

        $pointsCommands = sprintf('M%s,%s %sZ', $points2[0]['x'], $points2[0]['y'], $pointsCommands);

        return $pointsCommands;
    }

    protected function ___singleModuleCommands()
    {
        $l = $this->scale / 2;

        $zeroAngle = 30;

        $cx = 200;
        $cy = 200;
        $theta = 0;

        $x = $this->x * $this->scale + $l;
        $y = $this->y * $this->scale + $l / 2;

        $d = sqrt(pow($x - $cx, 2) + pow($y - $cy, 2));

        $angle_center = atan2($cy - $y, $cx - $x) * 180 / pi();

        $angle = $d * 1 + $zeroAngle + $angle_center + $theta;

        return $this->pathCommands(
            originX: $x,
            originY: $y,
            length: $l,
            angle: $angle,

        );
    }

    protected function _singleModuleCommands()
    {
        $l = $this->scale / 2;

        $cx = 200;
        $cy = 200;
        $theta = 0;

        $x = $this->x * $this->scale + $l;
        $y = $this->y * $this->scale + $l / 2;

        $angle_center = atan2($cy - $y, $cx - $x) * 180 / pi();

        $angle = $angle_center + $theta;

        return $this->pathCommands(
            originX: $x,
            originY: $y,
            length: $l,
            angle: $angle
        );
    }


    protected function diamondModuleCommands()
    {
        $l = $this->scale / 1.5;

        $x = $this->x * $this->scale + $l;
        $y = $this->y * $this->scale + $l / 2;

        $angle = rand(0, 180);

        return $this->pathCommands(
            t1: 0.8,
            t2: 0.8,
            t3: 0.8,
            originX: $x,
            originY: $y,
            length: $l,
            angle: $angle
        );
    }

    protected function fishModuleCommands()
    {
        $l = $this->scale / 1.5;

        $x = $this->x * $this->scale + $l;
        $y = $this->y * $this->scale + $l / 2;

        $theta = 120;

        $angle = rand(0, 100) & 1 ? $theta : $theta - 180;

        return $this->pathCommands(
            t1: 0.82,
            t2: 0.13,
            t3: 0.82,
            originX: $x,
            originY: $y,
            length: $l,
            angle: $angle
        );
    }

    protected function treeModuleCommands()
    {
        $l = $this->scale / 1.5;

        $x = $this->x * $this->scale + $l;
        $y = $this->y * $this->scale + $l / 2;

        $theta = 150;

        $angle = $theta;

        return $this->pathCommands(
            t1: 0.4,
            t2: 0.12,
            t3: 0.12,
            originX: $x,
            originY: $y,
            length: $l,
            angle: $angle
        );
    }

    protected function twoTrianglesWithCircleModuleCommands()
    {
        $l = $this->scale / 2;

        $x = $this->x * $this->scale + $l;
        $y = $this->y * $this->scale + $l / 2;

        $theta = 90;

        $t1 = 1.31;

        $t2 = $t1;

        $t3 = 0.79;

        $angle = $theta;


        $bits  = $this->checkNeighbours($this->x, $this->y);

        $check = fn (int $all, int $any): bool => ($bits & ($all | (~$any & 0xff))) === $all;

        // 1 2 3
        // 8 # 4
        // 7 6 5

        // 4 rounded corners
        if ($check(0b0000010, 0b11011101)) {

            $r = $l / 1.3;

            $cx = $x - $r / 2;
            $cy = $y - $r / 2;

            return sprintf(
                // ' M%1$s %2$s a%3$s %3$s 0 1 0 %4$s 0 a%3$s %3$s 0 1 0 -%4$s 0Z',
                ' M%1$s %2$s a%3$s %3$s 0 1 0 %4$s 0Z',
                $cx,
                $cy,
                $r,
                $r
            );
        }

        return $this->pathCommands(
            t1: $t1,
            t2: $t2,
            t3: $t3,
            originX: $x,
            originY: $y,
            length: $l,
            angle: $angle
        );
    }

    protected function fourTrianglesModuleCommands()
    {
        $l = $this->scale / 2.5;

        $x = $this->x * $this->scale + $l;

        $y = $this->y * $this->scale + $l / 2;

        $angle = 90;



        return $this->pathCommands(
            t1: 1.3,
            t2: 1.3,
            t3: 1.5,
            originX: $x,
            originY: $y,
            length: $l,
            angle: $angle
        );
    }

    protected function singleModuleCommands()
    {
        $func = $this->qrcode->design->module . 'ModuleCommands';

        return call_user_func([$this, $func]);
    }
}
