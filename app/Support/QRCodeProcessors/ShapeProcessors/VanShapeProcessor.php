<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class VanShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'van';

    public function symbolPath()
    {
        return 'M3,7C1.89,7 1,7.89 1,9V17H3A3,3 0 0,0 6,20A3,3 0 0,0 9,17H15A3,3 0 0,0 18,20A3,3 0 0,0 21,17H23V13C23,11.89 22.11,11 21,11L18,7H3M3,8.5H7V11H3V8.5zM9,8.5H13V11H9V8.5zM15,8.5H17.5L19.46,11H15V8.5zM6,15.5A1.5,1.5 0 0,1 7.5,17A1.5,1.5 0 0,1 6,18.5A1.5,1.5 0 0,1 4.5,17A1.5,1.5 0 0,1 6,15.5M18,15.5A1.5,1.5 0 0,1 19.5,17A1.5,1.5 0 0,1 18,18.5A1.5,1.5 0 0,1 16.5,17A1.5,1.5 0 0,1 18,15.5Z';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(-4%%, 9%%) scale(1.8);
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
        return 0.5;
    }
}
