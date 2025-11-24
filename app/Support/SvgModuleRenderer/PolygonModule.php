<?php

namespace App\Support\SvgModuleRenderer;

use App\Interfaces\ModuleRenderer;

class PolygonModule extends BaseRenderer implements ModuleRenderer
{
    private int $points;

    const modules = ['triangle', 'rhombus', 'star-5', 'star-7'];

    protected function shouldRenderSingleModule()
    {
        $module = array_filter($this::modules, fn ($m) => $m === $this->qrcode->design->module);


        return (!empty($module)
            || preg_match('/polygon/', $this->qrcode->design->module)
        )
            && $this->matrix->checkTypeNotIn(
                $this->x,
                $this->y,
                $this->options->keepAsSquare
            );
    }

    protected function setUp()
    {
        $type = $this->qrcode->design->module;

        preg_match('/polygon-(\d+)/', $type, $matches);

        $points = intval(@$matches[1]);

        $this->points = $points;
    }

    protected function makePolygon(
        $translateX,
        $translateY,
        $innerRadius,
        $outerRadius,
        $numPoints
    ) {
        $center = max($innerRadius, $outerRadius);
        $angle = pi() / $numPoints;
        $points = [];

        for ($i = 0; $i < $numPoints * 2; $i++) {
            $radius = $i & 1 ? $innerRadius : $outerRadius;
            $x = $center + $radius * sin($i * $angle) + $translateX;
            $y = $center - $radius * cos($i * $angle) + $translateY;
            $points[] = [$x, $y];
        }

        return $points;
    }

    protected function pathCommands()
    {
        $points = func_get_arg(0);

        $lines = implode(
            ' ',
            array_map(
                fn ($point) => sprintf('L%s,%s', $point[0], $point[1]),
                $points
            )
        ) . 'Z';

        return sprintf(' M%s,%s %s', $points[0][0], $points[0][1], $lines);
    }

    protected function singleModuleCommands()
    {
        return $this->pathCommands(
            $this->makeShape()
        );
    }

    protected function makeTriangle()
    {
        return $this->makePolygon(
            $this->x * $this->scale,
            $this->y * $this->scale,
            $this->scale / 3,
            $this->scale / 1.5,
            3
        );
    }

    protected function makeRhombus()
    {
        return $this->makePolygon(
            $this->x * $this->scale,
            $this->y * $this->scale,
            $this->scale / 3,
            $this->scale / 2,
            4
        );
    }

    protected function makeStar5()
    {
        return $this->makePolygon(
            $this->x * $this->scale,
            $this->y * $this->scale,
            $this->scale / 3,
            $this->scale / 1.75,
            5
        );
    }

    protected function makeStar7()
    {
        return $this->makePolygon(
            $this->x * $this->scale,
            $this->y * $this->scale,
            $this->scale / 3,
            $this->scale / 1.7,
            7
        );
    }

    private function makeShape()
    {
        $compatibiltyMap = [
            'polygon-3' => 'triangle',
            'polygon-4' => 'rhombus',
            'polygon-5' => 'star-5',
            'polygon-7' => 'star-7'
        ];

        $module = $this->qrcode->design->module;

        if (preg_match('/polygon/', $module)) {
            $module = $compatibiltyMap[$module];
        }

        $module = ucfirst(preg_replace('/-/', '', $module));

        $func = "make$module";

        return call_user_func([$this, $func]);
    }
}
