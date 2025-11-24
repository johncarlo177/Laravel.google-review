<?php

namespace App\Interfaces;

use App\Support\ModuleRenderOptions;
use Closure;

interface ModuleRenderer
{
    public function handle(ModuleRenderOptions $moduleRenderOptions, Closure $next);
}
