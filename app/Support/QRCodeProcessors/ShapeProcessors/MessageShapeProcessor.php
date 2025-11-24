<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class MessageShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'message';

    public function symbolPath()
    {
        return 'M20,2H4A2,2 0 0,0 2,4V22L6,18H20A2,2 0 0,0 22,16V4C22,2.89 21.1,2 20,2Z';
    }

    protected function renderStyles()
    {
        return sprintf('
            .foreground-0 {
                transform: translate(0, -10%%);
            }

            .foreground-1 {
                mask: url(#%s);
            }
        ', $this->maskId());
    }

    protected function modifiedViewBox()
    {
        return sprintf(
            '%1$s %1$s %2$s %2$s',
            -$this->output->size / 2,
            $this->output->size * 2
        );
    }
}
