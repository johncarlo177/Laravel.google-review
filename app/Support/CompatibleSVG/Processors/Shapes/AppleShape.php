<?php

namespace App\Support\CompatibleSVG\Processors\Shapes;

use App\Support\CompatibleSVG\Processors\Shapes\Traits\FillsFrameColor;

class AppleShape extends OutlinedShapeModifier
{
    use FillsFrameColor;

    protected function shouldProcess()
    {
        return $this->qrcode()->design->shape === 'apple';
    }
}
