<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class CupShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'cup';

    public function symbolPath()
    {
        return 'M2,21H20V19H2ZM20,8H18V5H20ZM20,3H4V13A4,4 0 0,0 8,17H14A4,4 0 0,0 18,13V10H20A2,2 0 0,0 22,8V5C22,3.89 21.1,3 20,3Z';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(-7%%, -13%%) scale(1.3);
        }
        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId());
    }
}
