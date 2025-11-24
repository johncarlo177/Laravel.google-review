<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class MobileShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'mobile';

    public function symbolPath()
    {
        return 'm 281.8747,443.90244 v -320.7078 h 184.79197 184.79196 v 320.7078 320.70779 H 466.66667 281.8747 Z';
    }

    protected function frameNode()
    {
        return $this->makeSinglePathUsedSymbol(
            'symbol-frame-' . $this::$shapeId,
            '0 0 700 700',
            'm 350,597.41186 c -18.71315,0 -33.89051,15.17736 -33.89051,33.89051 0,18.71315 15.17736,33.89051 33.89051,33.89051 18.71314,0 33.8905,-15.17736 33.8905,-33.89051 0,-18.71853 -15.17736,-33.89051 -33.8905,-33.89051 z m 0,54.22696 c -11.21632,0 -20.33645,-9.12013 -20.33645,-20.33645 0,-11.21633 9.12013,-20.33646 20.33645,-20.33646 11.21632,0 20.33646,9.12013 20.33646,20.33646 0,11.21632 -9.12014,20.33645 -20.33646,20.33645 z M 316.10814,44.973338 h 67.78102 V 58.527389 H 316.10814 Z M 197.48667,587.2443 h 305.02666 l -0.005,-508.377765 H 197.48129 Z M 211.04072,92.423279 H 488.95389 V 573.68756 H 211.04072 Z M 502.51333,11.082832 H 197.48667 c -18.71315,0 -33.89051,15.177363 -33.89051,33.890506 V 655.02666 c 0,18.71315 15.17736,33.89051 33.89051,33.89051 h 305.02666 c 18.71315,0 33.89051,-15.17736 33.89051,-33.89051 V 44.973338 c 0.005,-18.713143 -15.17199,-33.890506 -33.89051,-33.890506 z m 20.33646,643.945178 c 0,11.21631 -9.12014,20.33645 -20.33646,20.33645 H 197.48667 c -11.21632,0 -20.33646,-9.12014 -20.33646,-20.33645 V 44.974684 c 0,-11.216321 9.12014,-20.336457 20.33646,-20.336457 h 305.02666 c 11.21632,0 20.33646,9.120136 20.33646,20.336457 z',
            $this->frameId()
        );
    }

    public function symbolViewBox()
    {
        return '0 0 700 700';
    }

    protected function renderStyles()
    {
        return sprintf(
            '.foreground-0 {
            transform: translate(-2.5%%, -2.5%%) scale(2.3);
        }

        #%2$s path {
            transform: scale(0.75);
        }
        
        #%4$s path {
            fill: %5$s;
            
        }

        .foreground-1 {
            mask: url(#%1$s);
        }',
            $this->maskId(),
            $this->symbolId(),
            $this->frameId(),
            'symbol-frame-' . $this::$shapeId,
            $this->qrcode->design->frameColor
        );
    }

    protected function frameStrokeWidth()
    {
        return 1;
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
