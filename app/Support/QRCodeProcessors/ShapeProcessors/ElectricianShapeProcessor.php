<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class ElectricianShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'electrician';

    public function symbolPath()
    {
        return 'm 628.02487,684.84007 c 0,-0.36959 20.70846,-39.96847 46.01881,-87.99753 25.31034,-48.02906 47.55434,-90.36736 49.4311,-94.08509 l 3.41231,-6.75954 -55.62518,0.5689 c -36.20681,0.37029 -55.44958,0.1084 -55.12216,-0.75022 0.27665,-0.7255 30.93042,-52.92536 68.11947,-115.99969 L 751.8757,265.1363 h 42.68618 c 33.95509,0 42.41815,0.34237 41.3758,1.67384 -2.06313,2.63541 -103.46518,173.46592 -103.46518,174.30624 0,0.41358 29.10403,0.90836 64.67562,1.09948 l 64.67564,0.34751 -105.18257,109.35837 C 626.2314,687.50888 628.02487,685.65542 628.02487,684.84007 Z M 28.120516,140.60257 H 550.35868 V 662.84073 H 28.120516 Z';
    }

    protected function frameNode()
    {
        return $this->makeSinglePathUsedSymbol(
            'symbol-frame-' . $this::$shapeId,
            '0 0 700 700',
            'm 562.96779,199.24472 -102.48,173.88 83.969,-0.66016 -77.355,146.78 181.16,-188.43 h -98.512 l 78.676,-132.23 h -64.793 z m -10.578,-18.512 108.43,-0.66016 -78.676,132.23 h 109.75 l -303.47,316.03 124.3,-236.7 -85.949,0.66016 124.96,-211.57 z',
            $this->frameId()
        );
    }

    public function symbolViewBox()
    {
        return '0 0 700 700';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(-30.5%%, -18.3%%) scale(3.4);
        }

        #%2$s path {
            transform: scale(0.75);
        }
        
        #%3$s {
            fill: %4$s;
        }

        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId(), $this->symbolId(), $this->frameId(), $this->qrcode->design->frameColor);
    }

    protected function frameStrokeWidth()
    {
        return 3;
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
