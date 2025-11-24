<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

class SunShapeProcessor extends BaseShapeProcessor
{
    public static $shapeId = 'sun';

    public function symbolPath()
    {
        return 'M3.55 19.09L4.96 20.5L6.76 18.71L5.34 17.29zM12 6C8.69 6 6 8.69 6 12S8.69 18 12 18 18 15.31 18 12C18 8.68 15.31 6 12 6zM20 13H23V11H20zM17.24 18.71L19.04 20.5L20.45 19.09L18.66 17.29zM20.45 5L19.04 3.6L17.24 5.39L18.66 6.81zM13 1H11V4H13zM6.76 5.39L4.96 3.6L3.55 5L5.34 6.81L6.76 5.39zM1 13H4V11H1zM13 20H11V23H13Z';
    }

    protected function renderStyles()
    {
        return sprintf('.foreground-0 {
            transform: translate(0%%, 0%%);
        }
        .foreground-1 {
            mask: url(#%s);
        }', $this->maskId());
    }
}
