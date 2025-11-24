<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class TicketShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'ticket';

    public function symbolPath()
    {
        return 'M 95.26,0.5 H 2 C 1.17,0.5 0.5,1.17 0.5,2 v 14.35 c 0,0.8 0.62,1.46 1.42,1.5 4.87,0.25 8.69,4.27 8.69,9.15 0,4.88 -3.82,8.89 -8.69,9.15 -0.8,0.04 -1.42,0.7 -1.42,1.5 V 52 c 0,0.83 0.67,1.5 1.5,1.5 h 93.26 c 0.83,0 1.5,-0.67 1.5,-1.5 V 2 c 0,-0.83 -0.67,-1.5 -1.5,-1.5 z M 28.07,18.67 c -0.83,0 -1.5,-0.67 -1.5,-1.5 v -4.68 c 0,-0.83 0.67,-1.5 1.5,-1.5 0.83,0 1.5,0.67 1.5,1.5 v 4.68 c 0,0.83 -0.67,1.5 -1.5,1.5 z m 1.5,5.99 v 4.68 c 0,0.83 -0.67,1.5 -1.5,1.5 -0.83,0 -1.5,-0.67 -1.5,-1.5 v -4.68 c 0,-0.83 0.67,-1.5 1.5,-1.5 0.83,0 1.5,0.67 1.5,1.5 z m -3,12.17 c 0,-0.83 0.67,-1.5 1.5,-1.5 0.83,0 1.5,0.67 1.5,1.5 v 4.68 c 0,0.83 -0.67,1.5 -1.5,1.5 -0.83,0 -1.5,-0.67 -1.5,-1.5 z M 28.07,1 c 0.65,0 1.2,0.42 1.41,1 0.06,0.16 0.09,0.32 0.09,0.5 V 5 c 0,0.83 -0.67,1.5 -1.5,1.5 -0.83,0 -1.5,-0.67 -1.5,-1.5 V 2.5 c 0,-0.18 0.04,-0.34 0.09,-0.5 0.21,-0.58 0.76,-1 1.41,-1 z m 0,52 c -0.65,0 -1.2,-0.42 -1.41,-1 -0.06,-0.16 -0.09,-0.32 -0.09,-0.5 V 49 c 0,-0.83 0.67,-1.5 1.5,-1.5 0.83,0 1.5,0.67 1.5,1.5 v 2.5 c 0,0.18 -0.04,0.34 -0.09,0.5 -0.21,0.58 -0.76,1 -1.41,1 z';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(7%%, -15%%) scale(5);
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
        return 1;
    }

    public function symbolViewBox()
    {
        return sprintf('0 0 %s %s', $this->svgWidth(), $this->svgHeight());
    }

    private function svgWidth()
    {
        return '97.260002 ';
    }

    private function svgHeight()
    {
        return '54';
    }
}
