<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class HomeShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'home';

    public function symbolPath()
    {
        return 'M19 20V12H22L12 3L2 12H5V20z';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(0%%, 10%%);
        }
        
        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId());
    }
}
