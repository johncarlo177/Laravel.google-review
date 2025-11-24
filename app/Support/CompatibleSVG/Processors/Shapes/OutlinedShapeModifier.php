<?php

namespace App\Support\CompatibleSVG\Processors\Shapes;

use App\Support\CompatibleSVG\Processors\BaseProcessor;

abstract class OutlinedShapeModifier extends BaseProcessor
{
    public const SORT_ORDER = 20;

    public function sortOrder()
    {
        return static::SORT_ORDER;
    }
}
