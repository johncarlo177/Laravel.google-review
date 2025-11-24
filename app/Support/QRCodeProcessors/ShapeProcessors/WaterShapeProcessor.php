<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class WaterShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'water';

    public function symbolPath()
    {
        return 'M12,20A6,6 0 0,1 6,14C6,10 12,3.25 12,3.25C12,3.25 18,10 18,14A6,6 0 0,1 12,20Z';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(0%%, 7%%);
        }
        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId());
    }
}
