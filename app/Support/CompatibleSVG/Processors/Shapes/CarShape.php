<?php

namespace App\Support\CompatibleSVG\Processors\Shapes;

use App\Support\CompatibleSVG\Processors\Shapes\Interfaces\OverridesOutlinedShape;

class CarShape extends OutlinedShape implements OverridesOutlinedShape
{
    public static function slug(): string
    {
        return 'car';
    }

    protected function shouldProcess()
    {
        return $this->slug() === $this->qrcodeShapeSlug();
    }

    protected function qrcodeBackgroundMargin()
    {
        return 1.5;
    }
}
