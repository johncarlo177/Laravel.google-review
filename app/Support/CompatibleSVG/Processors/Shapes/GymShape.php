<?php

namespace App\Support\CompatibleSVG\Processors\Shapes;

use App\Support\CompatibleSVG\Processors\BaseProcessor;
use App\Support\CompatibleSVG\Processors\Shapes\Interfaces\OverridesOutlinedShape;
use App\Support\CompatibleSVG\Processors\Shapes\Traits\FillsFrameColor;

class GymShape extends OutlinedShape implements OverridesOutlinedShape
{
    public static function slug(): string
    {
        return 'gym';
    }

    protected function shouldProcess()
    {
        return $this->slug() === $this->qrcodeShapeSlug();
    }

    protected function qrcodeBackgroundMargin()
    {
        return 0;
    }
}
