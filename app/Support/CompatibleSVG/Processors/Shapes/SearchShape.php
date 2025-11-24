<?php

namespace App\Support\CompatibleSVG\Processors\Shapes;

use App\Support\CompatibleSVG\Processors\Shapes\Traits\FillsFrameColor;

class SearchShape extends OutlinedShapeModifier
{
    use FillsFrameColor;

    protected function shouldProcess()
    {
        return $this->qrcode()->design->shape === 'search';
    }
}
