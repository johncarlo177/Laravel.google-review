<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class BulbShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'bulb';

    public function symbolPath()
    {
        return 'M17.24,18.15L19.04,19.95L20.45,18.53L18.66,16.74zM20,12.5H23V10.5H20zM15,6.31V1.5H9V6.31C7.21,7.35 6,9.28 6,11.5A6,6 0 0,0 12,17.5A6,6 0 0,0 18,11.5C18,9.28 16.79,7.35 15,6.31zM4,10.5H1V12.5H4zM11,22.45C11.32,22.45 13,22.45 13,22.45V19.5H11zM3.55,18.53L4.96,19.95L6.76,18.15L5.34,16.74L3.55,18.53Z';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(0%%, -2%%);
        }
        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId());
    }
}
