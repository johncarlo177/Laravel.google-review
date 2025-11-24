<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class UmbrellaShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'umbrella';

    public function symbolPath()
    {
        return 'M12,2A9,9 0 0,1 21,11H13V19A3,3 0 0,1 10,22A3,3 0 0,1 7,19V18H9V19A1,1 0 0,0 10,20A1,1 0 0,0 11,19V11H3A9,9 0 0,1 12,2Z';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(-3%%, -26%%) scale(2.6);
        }
        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId());
    }

    protected function modifiedViewBox()
    {
        return sprintf(
            '%1$s %1$s %2$s %2$s',
            -$this->output->size * 4,
            $this->output->size * 10
        );
    }

    protected function frameStrokeWidth()
    {
        return 0.4;
    }
}
