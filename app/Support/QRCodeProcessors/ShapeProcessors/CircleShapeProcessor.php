<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

use App\Support\QRCodeProcessors\ShapeProcessors\BaseShapeProcessor;

class CircleShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'circle';

    protected function modifiedViewBox()
    {
        return sprintf(
            '%1$s %1$s %2$s %2$s',
            -$this->output->size / 2,
            $this->output->size * 2
        );
    }

    public function symbolViewBox()
    {
        return sprintf('%1$s %1$s %2$s %2$s', $this->getViewBoxStart(), $this->getSvgViewBoxSize());
    }

    public function symbolPath()
    {
        $pos = $this->output->size / 2;

        return sprintf(
            'M%1$s %2$s a%3$s %3$s 0 1 0 %4$s 0 a%3$s %3$s 0 1 0 -%4$s 0Z',
            ($pos + 0.5 - $this->circleRadius()),
            ($pos + 0.5),
            $this->circleRadius(),
            ($this->circleRadius() * 2)
        );
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(-8%%, -6.5%%) scale(1.3);
        }
        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId());
    }

    protected function frameStrokeWidth()
    {
        return $this->getSvgViewBoxSize() / 80;
    }

    protected function circleRadius()
    {
        return $this->output->size - 0.05 * $this->output->size;
    }
}
