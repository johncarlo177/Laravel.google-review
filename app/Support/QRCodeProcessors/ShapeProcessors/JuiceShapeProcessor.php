<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;


class JuiceShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'juice';

    public function symbolPath()
    {
        return 'M 494.83501,168.50766 H 373.54635 l -0.005,-80.272473 60.25944,-44.398855 c 6.12905,-4.512893 7.44484,-13.149049 2.92183,-19.277843 -4.52801,-6.144036 -13.14905,-7.464833 -19.28809,-2.92184 l -65.8682,48.532108 c -3.5273,2.601631 -5.60865,6.719386 -5.60865,11.09728 v 87.231253 l -140.78934,0.0103 c -22.76043,0 -41.38249,18.48245 -41.38249,41.38249 v 44.13885 c 0,7.72497 6.2091,13.79459 13.79459,13.79459 h 344.83912 c 7.72496,0 13.79459,-6.06898 13.79459,-13.79459 v -44.13891 c 0,-22.90517 -18.48245,-41.38249 -41.37737,-41.38249 z M 217.16285,567.55171 H 482.82076 L 505.54021,295.41276 H 194.55356 Z m 6.21205,73.78888 c 1.79112,22.20456 20.82893,39.72638 43.1731,39.72638 h 167.03364 c 22.34416,0 41.24159,-17.5167 43.17309,-39.72638 l 3.72236,-46.20994 H 219.50703 Z';
    }

    public function symbolViewBox()
    {
        return '0 0 700 700';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(1%%, 12%%) scale(2);
        }
        
        #%3$s {
            fill: none;
            stroke: %4$s;
        }

        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId(), $this->symbolId(), $this->frameId(), $this->qrcode->design->frameColor);
    }

    protected function frameStrokeWidth()
    {
        return 15;
    }

    protected function modifiedViewBox()
    {
        return sprintf(
            '%1$s %1$s %2$s %2$s',
            $this->output->size * -2,
            $this->output->size * 6
        );
    }
}
