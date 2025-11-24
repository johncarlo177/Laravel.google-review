<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class GiftShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'gift';

    public function symbolPath()
    {
        return 'M9.06,1.93C7.17,1.92 5.33,3.74 6.17,6H3A2,2 0 0,0 1,8V10A1,1 0 0,0 2,11H22A1,1 0 0,0 23,10V8A2,2 0 0,0 21,6H17.83C19,2.73 14.6,0.42 12.57,3.24L12,4L11.43,3.22C10.8,2.33 9.93,1.94 9.06,1.93M9,4C9.89,4 10.34,5.08 9.71,5.71C9.08,6.34 8,5.89 8,5A1,1 0 0,1 9,4M15,4C15.89,4 16.34,5.08 15.71,5.71C15.08,6.34 14,5.89 14,5A1,1 0 0,1 15,4M2,12V20A2,2 0 0,0 4,22H20A2,2 0 0,0 22,20V11H2Z';
    }

    protected function renderStyles()
    {

        return sprintf('.foreground-0 {
            transform: translate(-7%%, -2%%) scale(1.5);
        }

        .foreground-1 {
            mask: url(#%s);
        }',  $this->maskId());
    }

    protected function modifiedViewBox()
    {
        return sprintf(
            '%1$s %1$s %2$s %2$s',
            -$this->output->size,
            $this->output->size * 3
        );
    }
}
