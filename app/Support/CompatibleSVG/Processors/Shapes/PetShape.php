<?php

namespace App\Support\CompatibleSVG\Processors\Shapes;

use App\Support\CompatibleSVG\Processors\Shapes\Traits\FillsFrameColor;

class PetShape extends OutlinedShapeModifier
{
    use FillsFrameColor;

    protected function shouldProcess()
    {
        return $this->qrcode()->design->shape === 'pet';
    }
}
