<?php

namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;

class ContentManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'contentManager';
    }
}
