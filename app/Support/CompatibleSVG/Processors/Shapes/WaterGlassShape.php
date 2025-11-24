<?php

namespace App\Support\CompatibleSVG\Processors\Shapes;

use App\Support\CompatibleSVG\Processors\Shapes\Traits\FillsFrameColor;
use App\Support\CompatibleSVG\Processors\Shapes\Traits\GuessShapeName;

class WaterGlassShape extends OutlinedShapeModifier
{
    use FillsFrameColor;
    use GuessShapeName;

    protected function shouldProcess()
    {
        return $this->qrcode()->design->shape === $this->guessShapeName();
    }
}
