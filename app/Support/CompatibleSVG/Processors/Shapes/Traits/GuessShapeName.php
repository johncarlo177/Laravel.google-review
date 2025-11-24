<?php

namespace App\Support\CompatibleSVG\Processors\Shapes\Traits;

use Illuminate\Support\Str;

trait GuessShapeName
{

    protected function guessShapeName()
    {
        $class = class_basename(static::class);

        $shape = str_replace('Shape', '', $class);

        return Str::kebab($shape);
    }
}
